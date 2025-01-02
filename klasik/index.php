<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kriptografi Klasik Caesar Cipher pada File</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function toggleSection(section) {
            const encryptSection = document.getElementById('encrypt');
            const decryptSection = document.getElementById('decrypt');
            const encryptLink = document.getElementById('encrypt-link');
            const decryptLink = document.getElementById('decrypt-link');
            
            if (section === 'encrypt') {
                encryptSection.style.display = 'block';
                decryptSection.style.display = 'none';
                encryptLink.classList.add('active');
                decryptLink.classList.remove('active');
            } else {
                encryptSection.style.display = 'none';
                decryptSection.style.display = 'block';
                encryptLink.classList.remove('active');
                decryptLink.classList.add('active');
            }
        }
    </script>
</head>
<body>
    <header class="header">
        <h1>Kriptografi Klasik Caesar Cipher</h1>
    </header>
    <div class="dashboard">
        <aside class="sidebar">
            <nav>
                <ul>
                    <li><a id="encrypt-link" href="javascript:void(0);" class="active" onclick="toggleSection('encrypt')">Encrypt</a></li>
                    <li><a id="decrypt-link" href="javascript:void(0);" onclick="toggleSection('decrypt')">Decrypt</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <section id="encrypt" class="content-section" style="display: block;">
                <h2>Encrypt File</h2>
                <form action="caesar_file_processor.php" method="POST" enctype="multipart/form-data">
                    <label for="file-encrypt">Upload File:</label>
                    <input type="file" name="file" id="file-encrypt" required>

                    <label for="shift-encrypt">Shift Value:</label>
                    <input type="number" name="shift" id="shift-encrypt" required>

                    <input type="hidden" name="action" value="encrypt">
                    <button type="submit">Encrypt File</button>
                </form>
            </section>
            <section id="decrypt" class="content-section" style="display: none;">
                <h2>Decrypt File</h2>
                <form action="caesar_file_processor.php" method="POST" enctype="multipart/form-data">
                    <label for="file-decrypt">Upload File:</label>
                    <input type="file" name="file" id="file-decrypt" required>

                    <label for="shift-decrypt">Shift Value:</label>
                    <input type="number" name="shift" id="shift-decrypt" required>

                    <input type="hidden" name="action" value="decrypt">
                    <button type="submit">Decrypt File</button>
                </form>
            </section>

            <?php if (isset($_GET['encrypted_file'])): ?>
                <section>
                    <h2>File Encrypted</h2>
                    <p>Your file has been encrypted successfully. You can download it <a href="encrypted_files/<?php echo $_GET['encrypted_file']; ?>" download>here</a>.</p>
                </section>
            <?php elseif (isset($_GET['decrypted_file'])): ?>
                <section>
                    <h2>File Decrypted</h2>
                    <p>Your file has been decrypted successfully. You can download it <a href="decrypted_files/<?php echo $_GET['decrypted_file']; ?>" download>here</a>.</p>
                </section>
            <?php elseif (isset($error_message)): ?>
                <section>
                    <h2>Error</h2>
                    <p><?php echo $error_message; ?></p>
                </section>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
