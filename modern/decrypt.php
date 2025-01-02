<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $key = $_POST['key'];
    $file = $_FILES['file'];

    if (strlen($key) < 8) {
        echo "Key must be at least 16 characters.";
        exit;
    }

    // Kunci harus sepanjang 32 byte (256-bit) untuk AES-256
    $key = substr(hash('sha256', $key), 0, 32);  // Menggunakan SHA-256 untuk memastikan panjang 256 bit
    $iv = substr(hash('sha256', 'random_iv'), 0, 16);  // IV sepanjang 16 byte (128-bit)
    
    $uploadDir = 'uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filePath = $file['tmp_name'];
    $data = file_get_contents($filePath);

    // Dekripsi menggunakan AES-256-CBC
    $decryptedData = openssl_decrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

    // Cek apakah dekripsi berhasil
    if ($decryptedData === false) {
        echo "Decryption failed. The key or data might be incorrect.";
        exit;
    }

    $decryptedFilePath = $uploadDir . 'decrypted_' . $file['name'];

    // Menyimpan hasil dekripsi ke file
    file_put_contents($decryptedFilePath, $decryptedData);

    // Menyiapkan file untuk diunduh oleh pengguna
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($decryptedFilePath) . '"');
    header('Content-Length: ' . filesize($decryptedFilePath));
    readfile($decryptedFilePath);
    exit;
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
                    <label for="key">Kunci Dekripsi (min. 16 karakter):</label>
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
