<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Cryptography Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard">
        <header>
            <h1>Kriptografi Modern dengan AES</h1>
        </header>
        <nav>
            <ul>
                <li><a href="index.php">Beranda</a></li>
                <li><a href="encrypt.php">Enkripsi File</a></li>
                <li><a href="decrypt.php">Dekripsi File</a></li>
            </ul>
        </nav>
        <main>
            <section class="hero-section">
                <h2>Selamat datang di Dashboard Kriptografi AES</h2>
                <p>Gunakan dashboard ini untuk mengenkripsi dan mendekripsi file secara aman menggunakan Advanced Encryption Standard (AES).</p>
                <a href="encrypt.php" class="cta-button">Mulai Enkripsi</a>
                <a href="decrypt.php" class="cta-button">Mulai Dekripsi</a>
            </section>

            <section class="info-section">
                <h3>Apa itu Kriptografi AES?</h3>
                <p>Advanced Encryption Standard (AES) adalah algoritma enkripsi yang sangat aman dan banyak digunakan. AES termasuk algoritma enkripsi simetris, yang berarti kunci yang sama digunakan untuk proses enkripsi dan dekripsi. AES bekerja pada blok data (biasanya 128 bit) dan mendukung panjang kunci yang berbeda, yaitu 128, 192, dan 256 bit. AES-256 dianggap sebagai opsi yang paling kuat.</p>

                <h3>Kenapa Menggunakan AES?</h3>
                <ul>
                    <li><strong>Keamanan Tinggi:</strong> AES-256 dianggap sangat aman dan digunakan oleh pemerintah, lembaga keuangan, serta sektor lain yang membutuhkan tingkat keamanan tinggi.</li>
                    <li><strong>Efisiensi:</strong> AES sangat efisien dalam penggunaan sumber daya komputasi, sehingga cocok untuk implementasi baik di perangkat keras maupun perangkat lunak.</li>
                    <li><strong>Versatilitas:</strong> AES dapat digunakan untuk mengenkripsi berbagai tipe data, mulai dari file hingga saluran komunikasi.</li>
                </ul>

                <h3>Bagaimana AES Bekerja?</h3>
                <p>AES menggunakan serangkaian putaran (rounds) untuk mengubah data plaintext menjadi ciphertext, dengan setiap putaran melibatkan beberapa operasi seperti substitusi, permutasi, dan pencampuran. Kekuatan AES terletak pada kemampuannya untuk menahan berbagai jenis serangan kriptografi, termasuk brute-force dan analisis diferensial.</p>

                <h3>Aplikasi AES:</h3>
                <ul>
                    <li>Enkripsi data sensitif seperti informasi pribadi, catatan keuangan, dan dokumen yang dilindungi.</li>
                    <li>Melindungi komunikasi dalam VPN, jaringan Wi-Fi, dan sistem pesan terenkripsi.</li>
                    <li>Penyimpanan file yang aman dan transmisi data melalui internet.</li>
                </ul>
            </section>
        </main>
        <footer>
            <p>© 2024 Cryptography Inc. Semua hak dilindungi.</p>
        </footer>
    </div>
</body>
</html>
