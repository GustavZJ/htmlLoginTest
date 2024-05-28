<?php
session_start();

function verifyPassword($password) {
    $htpasswd_path = '/etc/apache2/.htpasswd';
    
    if (!file_exists($htpasswd_path)) {
        return false;
    }
    
    $lines = file($htpasswd_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        list($user, $hash) = explode(':', $line, 2);
        
        if (password_verify($password, $hash)) {
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
            header("Location: /main/index.html");
        } else {
            header("Location: /main/index.html");
        }
        exit();
    } else {
        echo "<p>Invalid password</p>";
    }
}