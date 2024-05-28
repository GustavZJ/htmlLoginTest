<?php
session_start();

// Function to read the .htpasswd file and return an associative array of username => hashed_password
function get_htpasswd_credentials($file_path) {
    if (!file_exists($file_path)) {
        throw new Exception("The file $file_path does not exist.");
    }
    
    $lines = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
        throw new Exception("Failed to open the file $file_path.");
    }

    $credentials = [];
    foreach ($lines as $line) {
        if (strpos($line, ':') !== false) {
            list($username, $hash) = explode(':', $line, 2);
            $credentials[$username] = $hash;
        }
    }

    return $credentials;
}

// Function to verify password using apr_md5
function verify_apr_md5_password($password, $hash) {
    $hashParts = explode('$', $hash);
    if (count($hashParts) != 4 || $hashParts[1] != 'apr1') {
        return false;
    }
    
    $salt = $hashParts[2];
    $expectedHash = $hashParts[3];
    $testHash = md5($password . $salt);

    return $testHash === $expectedHash;
}

// Path to the .htpasswd file
$htpasswd_file = '/etc/apache2/.htpasswd';

try {
    $password = $_POST['password'];
    $credentials = get_htpasswd_credentials($htpasswd_file);

    $user_password_hash = isset($credentials['uploader']) ? $credentials['uploader'] : null;
    $admin_password_hash = isset($credentials['admin']) ? $credentials['admin'] : null;

    if ($user_password_hash && verify_apr_md5_password($password, $user_password_hash)) {
        $_SESSION['role'] = 'uploader';
        header('Location: /main/index.html');
        exit;
    } elseif ($admin_password_hash && verify_apr_md5_password($password, $admin_password_hash)) {
        $_SESSION['role'] = 'admin';
        header('Location: /main/index.html');
        exit;
    } else {
        echo "Invalid password.";
    }
} catch (Exception $e) {
    echo $e->getMessage();
}