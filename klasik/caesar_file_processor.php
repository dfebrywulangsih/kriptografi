<?php
$encrypted_dir = 'encrypted_files/';
$decrypted_dir = 'decrypted_files/';

// Cek jika folder enkripsi dan dekripsi ada
if (!is_dir($encrypted_dir)) {
    mkdir($encrypted_dir, 0777, true);
}

if (!is_dir($decrypted_dir)) {
    mkdir($decrypted_dir, 0777, true);
}

// Fungsi untuk enkripsi dan dekripsi
function caesarCipherEncrypt($text, $shift) {
    $encrypted_text = '';
    $shift = $shift % 26;

    for ($i = 0; $i < strlen($text); $i++) {
        $char = $text[$i];

        if (ctype_alpha($char)) {
            $offset = ctype_lower($char) ? ord('a') : ord('A');
            $encrypted_text .= chr((ord($char) - $offset + $shift) % 26 + $offset);
        } else {
            $encrypted_text .= $char;
        }
    }

    return $encrypted_text;
}

function caesarCipherDecrypt($text, $shift) {
    $decrypted_text = '';
    $shift = $shift % 26;

    for ($i = 0; $i < strlen($text); $i++) {
        $char = $text[$i];

        if (ctype_alpha($char)) {
            $offset = ctype_lower($char) ? ord('a') : ord('A');
            $decrypted_text .= chr((ord($char) - $offset - $shift + 26) % 26 + $offset);
        } else {
            $decrypted_text .= $char;
        }
    }

    return $decrypted_text;
}

function processEncrypt($file, $shift) {
    global $encrypted_dir;
    
    $file_content = file_get_contents($file['tmp_name']);
    $encrypted_content = caesarCipherEncrypt($file_content, $shift);
    
    $path_info = pathinfo($file['name']);
    $encrypted_filename = $encrypted_dir . $path_info['filename'] . '_encrypted.' . $path_info['extension'];
    
    $result = file_put_contents($encrypted_filename, $encrypted_content);
    return $result ? $encrypted_filename : false;
}

function processDecrypt($file, $shift) {
    global $decrypted_dir;
    
    $file_content = file_get_contents($file['tmp_name']);
    $decrypted_content = caesarCipherDecrypt($file_content, $shift);
    
    $path_info = pathinfo($file['name']);
    $decrypted_filename = $decrypted_dir . $path_info['filename'] . '_decrypted.' . $path_info['extension'];
    
    $result = file_put_contents($decrypted_filename, $decrypted_content);
    return $result ? $decrypted_filename : false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shift = $_POST['shift'];
    $file = $_FILES['file'];
    $processed_file = false;

    if ($_POST['action'] === 'encrypt' && isset($file)) {
        $processed_file = processEncrypt($file, $shift);
    } elseif ($_POST['action'] === 'decrypt' && isset($file)) {
        $processed_file = processDecrypt($file, $shift);
    }

    if ($processed_file) {
        $file_type = ($_POST['action'] === 'encrypt') ? 'encrypted' : 'decrypted';
        header('Location: index.php?' . $file_type . '_file=' . basename($processed_file));
        exit;
    } else {
        $error_message = "File processing failed. Please try again.";
    }
}
?>