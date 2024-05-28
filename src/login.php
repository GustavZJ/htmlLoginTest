<?php
session_start();

// Function to read the .htpasswd file and return an associative array of username => hashed_password
function get_htpasswd_credentials($file_path) {
    if (!file_exists($file_path)) {
        // Log an error message for debugging purposes
        error_log("The file $file_path does not exist.");
        return [];
    }

    $credentials = [];
    $lines = @file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
        // Log an error message for debugging purposes
        error_log("Failed to read the file $file_path.");
        return [];
    }

    foreach ($lines as $line) {
        if (strpos($line, ':') !== false) {
            list($username, $hash) = explode(':', $line, 2);
            $credentials[$username] = $hash;
        }
    }

    return $credentials;
}

// Path to the .htpasswd file
$htpasswd_file = '/etc/apache2/.htpasswd';  // Ensure this is the correct path

$password = $_POST['password'] ?? '';
$credentials = get_htpasswd_credentials($htpasswd_file);

$user_password_hash = $credentials['user'] ?? null;
$admin_password_hash = $credentials['admin'] ?? null;

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
