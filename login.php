<?php
session_start();

function verifyPassword($password) {
    // Hardcoded test user and hash
    $test_user = 'admin';
    $test_hash = '$apr1$Sfnd1SG.$THZiL/eydHJLFDiwfsWQL/';

    // Extract salt from the existing hash
    $salt = substr($test_hash, 0, strrpos($test_hash, '$') + 1);

    // Verify the password using the extracted salt
    if (crypt($password, $salt) == $test_hash) {
        return $test_user;
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