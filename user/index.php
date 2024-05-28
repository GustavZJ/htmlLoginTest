<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user' || $_SESSION['role'] !== 'admin') {
    header("Location: /index.html");
    exit();
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User page</title>
</head>
<body>
    <p>User</p>
</body>
</html>