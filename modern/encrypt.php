<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $key = $_POST['key'];
    $file = $_FILES['file'];

    if (strlen($key) < 8) {
        echo "Key must be at least 8 characters.";
        exit;
    }

    $key = substr(hash('sha256', $key), 0, 32);
    $iv = substr(hash('sha256', 'random_iv'), 0, 16);
    
    $uploadDir = 'uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $filePath = $file['tmp_name'];
    $data = file_get_contents($filePath);

    $encryptedData = openssl_encrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
    $encryptedFilePath = $uploadDir . 'encrypted_' . $file['name'];

    file_put_contents($encryptedFilePath, $encryptedData);

    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($encryptedFilePath) . '"');
    header('Content-Length: ' . filesize($encryptedFilePath));
    readfile($encryptedFilePath);
    exit;
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
