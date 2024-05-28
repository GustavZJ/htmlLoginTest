<?php
session_start();

function verifyPassword($password) {
    $htpasswd_path = '/var/www/html/.htpasswd';
    
    if (!file_exists($htpasswd_path)) {
        return false;
    }
    
    $lines = file($htpasswd_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        list($user, $hash) = explode(':', $line, 2);
        
        // Extract salt from the existing hash
        $salt = substr($hash, 0, strrpos($hash, '$') + 1);

        // Verify the password
        if (crypt($password, $salt) == $hash) {
            return $user;
        }
    }
    
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    
    $user = verifyPassword($password);
    
    if ($user) {
        $_SESSION['authenticated'] = true;
        $_SESSION['user'] = $user;
        
        if ($user === 'admin') {
            header("Location: /Admin/index.php");
        } else {
            header("Location: /User/index.php");
        }
        exit();
    } else {
        echo "<p>Invalid password</p>";
    }
}