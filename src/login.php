<?php
// Check if password is set and not empty
if(isset($_POST['password']) && !empty($_POST['password'])) {
    $password = $_POST['password'];
    
    // Hash the password using apr1_MD5
    $hashed_password = crypt($password, '$apr1$');

    // Check if the hashed password matches the one stored in .htpasswd
    if($hashed_password === file_get_contents('/etc/apache2/.htpasswd')) {
        // Password is correct, redirect based on user role
        if ($password === 'admin_password') {
            header('Location: /main/');
        } else {
            header('Location: /user');
        }
        exit;
    } else {
        // Incorrect password, redirect back to login page
        header('Location: index.html');
        exit;
    }
} else {
    // No password provided, redirect back to login page
    header('Location: index.html');
    exit;
}
?>
