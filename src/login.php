<?php
session_start();

// Function to read the .htpasswd file and return an associative array of username => hashed_password
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
$htpasswd_file = '/etc/apache2/.htpasswd';

$password = $_POST['password'];
$credentials = get_htpasswd_credentials($htpasswd_file);

$user_password_hash = isset($credentials['user']) ? $credentials['user'] : null;
$admin_password_hash = isset($credentials['admin']) ? $credentials['admin'] : null;

if ($user_password_hash && password_verify($password, $user_password_hash)) {
    $_SESSION['role'] = 'user';
    header('Location: /main/index.html');
    exit;
} elseif ($admin_password_hash && password_verify($password, $admin_password_hash)) {
    $_SESSION['role'] = 'admin';
    header('Location: /main/index.html');
    exit;
} else {
    echo "Invalid password.";
}
?>
