<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $key = $_POST['key'];
    $file = $_FILES['file'];

    if (strlen($key) < 8) {
        echo "Key must be at least 8 characters.";
        exit;
    }

    // Hash key menjadi 256 bit untuk AES-256
    $key = substr(hash('sha256', $key), 0, 32);
    $iv = substr(hash('sha256', 'random_iv'), 0, 16);  // Membuat IV dari hash

    // Membaca file yang akan didekripsi
    $filePath = $file['tmp_name'];
    $encryptedData = file_get_contents($filePath);

    // Dekripsi data menggunakan AES-256 CBC manual
    $decryptedData = aes256_cbc_decrypt($encryptedData, $key, $iv);

    // Menyimpan hasil dekripsi
    $decryptedFilePath = 'uploads/decrypted_' . $file['name'];
    file_put_contents($decryptedFilePath, $decryptedData);

    // Mengirimkan file yang telah didekripsi
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($decryptedFilePath) . '"');
    header('Content-Length: ' . filesize($decryptedFilePath));
    readfile($decryptedFilePath);
    exit;
}

// Fungsi untuk AddRoundKey (XOR blok dengan key)
function add_round_key($state, $key) {
    $keyBytes = array_values(unpack('C16', substr($key, 0, 16)));  // Ambil 16 byte pertama dari key
    $result = [];

    // XOR setiap byte state dengan byte dari key
    for ($i = 0; $i < 16; $i++) {
        $result[] = $state[$i] ^ $keyBytes[$i];
    }

    return $result;
}

// Fungsi untuk dekripsi AES-256 CBC manual
function aes256_cbc_decrypt($data, $key, $iv) {
    // Membagi data menjadi blok-blok 16-byte
    $dataBlocks = str_split($data, 16);
    $output = '';

    // Proses setiap blok
    foreach ($dataBlocks as $block) {
        $decryptedBlock = aes256_decrypt_block($block, $key); // Dekripsi blok
        $decryptedBlock = xor_data($decryptedBlock, $iv); // XOR dengan IV atau blok sebelumnya
        $output .= $decryptedBlock;
        $iv = $block; // Blok terenkripsi menjadi IV untuk ronde berikutnya
    }

    return pkcs7_unpadding($output); // Unpadding dengan benar
}

// Fungsi untuk dekripsi satu blok dengan AES-256
function aes256_decrypt_block($block, $key) {
    $state = array_values(unpack('C16', $block)); // Menyusun blok sebagai array 16 byte

    // 1. AddRoundKey (XOR blok dengan key)
    $state = add_round_key($state, $key);

    // Proses ronde 13-1
    for ($round = 13; $round > 0; $round--) {
        $state = inv_shift_rows($state);  // Inverse ShiftRows
        $state = inv_sub_bytes($state);  // Inverse SubBytes
        $state = add_round_key($state, $key);  // AddRoundKey
        if ($round > 1) {
            $state = inv_mix_columns($state);  // Inverse MixColumns pada ronde 2-13
        }
    }

    // Ronde pertama (Tanpa MixColumns)
    $state = inv_shift_rows($state);  // Inverse ShiftRows
    $state = inv_sub_bytes($state);  // Inverse SubBytes
    $state = add_round_key($state, $key);  // AddRoundKey

    // Mengembalikan blok yang telah didekripsi
    return pack('C16', ...$state);
}

// Fungsi untuk menghapus padding PKCS7
function pkcs7_unpadding($data) {
    $padding = ord($data[strlen($data) - 1]);
    return substr($data, 0, -$padding);
}

// Fungsi Inverse ShiftRows
function inv_shift_rows($state) {
    $tmp = array();

    // Baris pertama (tidak ada perubahan)
    $tmp[0] = $state[0];
    $tmp[1] = $state[1];
    $tmp[2] = $state[2];
    $tmp[3] = $state[3];

    // Baris kedua (shift 1 ke kanan)
    $tmp[4] = $state[7];
    $tmp[5] = $state[4];
    $tmp[6] = $state[5];
    $tmp[7] = $state[6];

    // Baris ketiga (shift 2 ke kanan)
    $tmp[8] = $state[10];
    $tmp[9] = $state[11];
    $tmp[10] = $state[8];
    $tmp[11] = $state[9];

    // Baris keempat (shift 3 ke kanan)
    $tmp[12] = $state[13];
    $tmp[13] = $state[14];
    $tmp[14] = $state[15];
    $tmp[15] = $state[12];

    return $tmp;
}

// Fungsi Inverse SubBytes (Inverse S-Box)
function inv_sub_bytes($state) {
    // Definisi Inverse S-Box
    $invSBox = [
        0x52, 0x09, 0x6A, 0xD5, 0x30, 0x36, 0xA5, 0x38,
        0xBF, 0x40, 0xA3, 0x9E, 0x81, 0xF3, 0xD7, 0xFB,
        0x7C, 0xE3, 0x39, 0x82, 0x9B, 0x2F, 0xFF, 0x87,
        0x34, 0x8E, 0x43, 0x44, 0xC4, 0xDE, 0xE9, 0xCB,
        0x54, 0x7B, 0x94, 0x32, 0xA6, 0xC2, 0x23, 0x3D,
        0xEE, 0x4C, 0x95, 0x0B, 0x42, 0xFA, 0xC3, 0x4E,
        0x08, 0x2E, 0xA1, 0x66, 0x28, 0xD9, 0x24, 0xB2,
        0x76, 0x5B, 0xA2, 0x49, 0x6D, 0x8B, 0x8A, 0x70,
        0x3E, 0xB5, 0x66, 0x48, 0x03, 0xF6, 0x0E, 0x61,
        0x35, 0x57, 0xB9, 0x86, 0xB6, 0xC5, 0xDA, 0x24,
        0x5A, 0xB3, 0xCD, 0x9F, 0x68, 0x33, 0x79, 0x69
        // Lanjutkan array sesuai dengan standar AES Inverse S-box
    ];

    return $state;
}

// Fungsi Inverse MixColumns
function inv_mix_columns($state) {
    $stateCopy = $state;
    for ($i = 0; $i < 4; $i++) {
        $a = array($stateCopy[$i], $stateCopy[$i+4], $stateCopy[$i+8], $stateCopy[$i+12]);

        $state[$i] = gmul(0x0E, $a[0]) ^ gmul(0x0B, $a[1]) ^ gmul(0x0D, $a[2]) ^ gmul(0x09, $a[3]);
        $state[$i+4] = gmul(0x09, $a[0]) ^ gmul(0x0E, $a[1]) ^ gmul(0x0B, $a[2]) ^ gmul(0x0D, $a[3]);
        $state[$i+8] = gmul(0x0D, $a[0]) ^ gmul(0x09, $a[1]) ^ gmul(0x0E, $a[2]) ^ gmul(0x0B, $a[3]);
        $state[$i+12] = gmul(0x0B, $a[0]) ^ gmul(0x0D, $a[1]) ^ gmul(0x09, $a[2]) ^ gmul(0x0E, $a[3]);
    }

    return $state;
}

// Fungsi untuk XOR data
function xor_data($data, $key) {
    // Memastikan panjang key dan data adalah 16 byte
    $keyBytes = array_values(unpack('C16', substr($key, 0, 16)));  // Pastikan panjang kunci adalah 16 byte
    $dataBytes = array_values(unpack('C16', substr($data, 0, 16)));  // Pastikan panjang data adalah 16 byte

    $result = [];
    for ($i = 0; $i < 16; $i++) {
        $result[] = $dataBytes[$i] ^ $keyBytes[$i];
    }
    return pack('C16', ...$result);
}

// Fungsi Perkalian di GF(2^8) untuk MixColumns
function gmul($a, $b) {
    $p = 0;
    $hiBitSet = 0x80; // 10000000
    for ($counter = 0; $counter < 8; $counter++) {
        if ($b & 0x01) {
            $p ^= $a;
        }
        $hiBitSet &= $a & 0x80;
        $a <<= 1;
        if ($hiBitSet) {
            $a ^= 0x11b; // Polynomial untuk GF(2^8) - x^8 + x^4 + x^3 + x + 1
        }
        $b >>= 1;
    }
    return $p;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dekripsi File</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard">
        <header>
            <h1>Dekripsi File</h1>
        </header>
        <nav>
            <ul>
                <li><a href="index.php">Beranda</a></li>
                <li><a href="encrypt.php">Enkripsi File</a></li>
                <li><a href="decrypt.php">Dekripsi File</a></li>
            </ul>
        </nav>
        <main>
            <section class="form-section">
                <h2>Mendekripsi File dengan AES</h2>
                <form action="decrypt.php" method="post" enctype="multipart/form-data">
                    <label for="key">Kunci Dekripsi (min. 8 karakter):</label>
                    <input type="text" id="key" name="key" required maxlength="32">
                    
                    <label for="file">Pilih File yang Terenkripsi:</label>
                    <input type="file" id="file" name="file" required>
                    
                    <button type="submit">Dekripsi</button>
                </form>
            </section>
        </main>
        <footer>
            <p>© 2025 Cryptography Inc. Semua hak dilindungi.</p>
        </footer>
    </div>
</body>
</html>

