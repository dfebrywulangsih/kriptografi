<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $key = $_POST['key'];
    $file = $_FILES['file'];

    if (strlen($key) < 16) {
        echo "Key must be at least 16 characters.";
        exit;
    }

    // Hash key menjadi 256 bit untuk AES-256
    $key = substr(hash('sha256', $key), 0, 32);
    $iv = substr(hash('sha256', 'random_iv'), 0, 16);  // Membuat IV dari hash

    // Membaca file yang akan dienkripsi
    $uploadDir = 'uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filePath = $file['tmp_name'];
    $data = file_get_contents($filePath);

    // Enkripsi data menggunakan AES-256 CBC manual
    $encryptedData = aes256_cbc_encrypt($data, $key, $iv);

    // Menyimpan hasil enkripsi
    $encryptedFilePath = $uploadDir . 'encrypted_' . $file['name'];
    file_put_contents($encryptedFilePath, $encryptedData);

    // Mengirimkan file terenkripsi
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($encryptedFilePath) . '"');
    header('Content-Length: ' . filesize($encryptedFilePath));
    readfile($encryptedFilePath);
    exit;
}

// Fungsi untuk enkripsi AES-256 CBC manual
function aes256_cbc_encrypt($data, $key, $iv) {
    // Persiapkan data dalam blok 16-byte
    $data = pkcs7_padding($data);
    $dataBlocks = str_split($data, 16);
    $output = '';

    // Proses setiap blok
    foreach ($dataBlocks as $block) {
        $block = xor_data($block, $iv); // XOR dengan IV atau blok sebelumnya
        $block = aes256_encrypt_block($block, $key); // Enkripsi blok
        $output .= $block;
        $iv = $block; // Blok enkripsi menjadi IV untuk ronde berikutnya
    }

    return $output;
}

// Fungsi untuk enkripsi satu blok dengan AES-256
function aes256_encrypt_block($block, $key) {
    $state = array_values(unpack('C16', $block)); // Menyusun blok sebagai array 16 byte

    // 1. AddRoundKey (XOR blok dengan key)
    $state = add_round_key($state, $key);

    // Proses ronde 1-13
    for ($round = 1; $round < 14; $round++) {
        $state = sub_bytes($state);  // SubBytes
        $state = shift_rows($state);  // ShiftRows
        if ($round < 13) {
            $state = mix_columns($state);  // MixColumns pada ronde 1-12
        }
        $state = add_round_key($state, $key);  // AddRoundKey
    }

    // Ronde terakhir (Tanpa MixColumns)
    $state = sub_bytes($state);  // SubBytes
    $state = shift_rows($state);  // ShiftRows
    $state = add_round_key($state, $key);  // AddRoundKey

    // Mengembalikan blok yang telah dienkripsi
    return pack('C16', ...$state);
}

// Fungsi untuk melakukan padding PKCS7
function pkcs7_padding($data) {
    $blockSize = 16;
    $padding = $blockSize - (strlen($data) % $blockSize);
    if ($padding === 0) {
        $padding = $blockSize;
    }
    return $data . str_repeat(chr($padding), $padding);
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


// Fungsi AddRoundKey
function add_round_key($state, $key) {
    $keyBytes = array_values(unpack('C16', $key));
    for ($i = 0; $i < 16; $i++) {
        $state[$i] ^= $keyBytes[$i];
    }
    return $state;
}

// Fungsi SubBytes (S-Box)
function sub_bytes($state) {
    $sBox = [
        0x63, 0x7C, 0x77, 0x7B, 0xF2, 0x6B, 0x6F, 0xC5, 0x30, 0x01, 0x67, 0x2B, 0xFE, 0xD7, 0xAB, 0x76, 0xCA,
        0x82, 0xC9, 0x7D, 0xFA, 0x59, 0x47, 0xF0, 0xAD, 0xD4, 0xA2, 0xAF, 0x9C, 0xA8, 0x51, 0xA3, 0x40, 0x8F,
        0x92, 0x9D, 0x38, 0xF5, 0xBC, 0xB6, 0xDA, 0x21, 0x10, 0xFF, 0xF3, 0xD2, 0xCD, 0x0C, 0x13, 0xEC, 0x5F,
        0x97, 0x44, 0x17, 0xC4, 0xA7, 0x7E, 0x3D, 0x64, 0x5D, 0x19, 0x73, 0x60, 0x81, 0x4F, 0xDC, 0x22, 0x2A,
        0x90, 0x88, 0x46, 0xEE, 0xB8, 0x14, 0xDE, 0x5E, 0x0B, 0xDB, 0xE0, 0x32, 0x3A, 0x0A, 0x49, 0x06, 0x24,
        0x5C, 0xC2, 0xD3, 0xAC, 0x62, 0x91, 0x95, 0x0D, 0x35, 0x85, 0xE1, 0xE5, 0xF1, 0x76, 0x59, 0x47, 0xB6,
        0xF7, 0x7C, 0x6A, 0x8E, 0x6D, 0xB1, 0x92, 0xB7, 0xD1, 0x70, 0x5A, 0x32, 0x29, 0x98, 0x73, 0x9B, 0x2B
    ];

    return $state;
}

// Fungsi ShiftRows
// Fungsi ShiftRows
function shift_rows($state) {
    $tmp = array();

    // Baris pertama (tidak ada perubahan)
    $tmp[0] = $state[0];
    $tmp[1] = $state[1];
    $tmp[2] = $state[2];
    $tmp[3] = $state[3];

    // Baris kedua (shift 1 ke kiri)
    $tmp[4] = $state[5];
    $tmp[5] = $state[6];
    $tmp[6] = $state[7];
    $tmp[7] = $state[4];

    // Baris ketiga (shift 2 ke kiri)
    $tmp[8] = $state[10];
    $tmp[9] = $state[11];
    $tmp[10] = $state[8];
    $tmp[11] = $state[9];

    // Baris keempat (shift 3 ke kiri)
    $tmp[12] = $state[15];
    $tmp[13] = $state[12];
    $tmp[14] = $state[13];
    $tmp[15] = $state[14];

    return $tmp;
}


// Fungsi MixColumns
// Fungsi MixColumns
function mix_columns($state) {
    $stateCopy = $state;
    for ($i = 0; $i < 4; $i++) {
        // Mengambil kolom ke-i (kolom yang berisi 4 byte)
        $a = array($stateCopy[$i], $stateCopy[$i+4], $stateCopy[$i+8], $stateCopy[$i+12]);

        // Operasi MixColumns
        $state[$i] = gmul(0x02, $a[0]) ^ gmul(0x03, $a[1]) ^ gmul(0x01, $a[2]) ^ gmul(0x01, $a[3]);
        $state[$i+4] = gmul(0x01, $a[0]) ^ gmul(0x02, $a[1]) ^ gmul(0x03, $a[2]) ^ gmul(0x01, $a[3]);
        $state[$i+8] = gmul(0x01, $a[0]) ^ gmul(0x01, $a[1]) ^ gmul(0x02, $a[2]) ^ gmul(0x03, $a[3]);
        $state[$i+12] = gmul(0x03, $a[0]) ^ gmul(0x01, $a[1]) ^ gmul(0x01, $a[2]) ^ gmul(0x02, $a[3]);
    }

    return $state;
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
    <title>Enkripsi File</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard">
        <header>
            <h1>Enkripsi File</h1>
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
                <h2>Mengenkripsi File dengan AES</h2>
                <form action="encrypt.php" method="post" enctype="multipart/form-data">
                    <label for="key">Kunci Enkripsi (min. 16 karakter):</label>
                    <input type="text" id="key" name="key" required maxlength="32">
                    
                    <label for="file">Pilih File:</label>
                    <input type="file" id="file" name="file" required>
                    
                    <button type="submit">Enkripsi</button>
                </form>
            </section>
        </main>
        <footer>
            <p>© 2025 Cryptography Inc. Semua hak dilindungi.</p>
        </footer>
    </div>
</body>
</html>
