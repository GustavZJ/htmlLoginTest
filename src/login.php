<?php
session_start();

function validate_password($password, $hash) {
    // Check if the provided password matches the hashed password
    return crypt($password, $hash) === $hash;
}

function get_htpasswd_credentials($file_path) {
    $credentials = [];
    $lines = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        if (strpos($line, ':') !== false) {
            list($username, $hash) = explode(':', $line, 2);
            $credentials[$username] = $hash;
        }
    }

    return $credentials;
}

// Path to the .htpasswd file
$htpasswd_file = '/etc/apache2/.temppasswd';

$password = $_POST['password'];
$credentials = get_htpasswd_credentials($htpasswd_file);

$user_password_hash = isset($credentials['user']) ? $credentials['user'] : null;
$admin_password_hash = isset($credentials['admin']) ? $credentials['admin'] : null;

if ($user_password_hash && validate_password($password, $user_password_hash)) {
    $_SESSION['role'] = 'user';
    header('Location: /main/index.html');
    exit;
} elseif ($admin_password_hash && validate_password($password, $admin_password_hash)) {
    $_SESSION['role'] = 'admin';
    header('Location: /main/index.html');
    exit;
} else {
    echo "Invalid password.";
}
