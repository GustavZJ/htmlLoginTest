<?php
session_start();

function validate_apr1_md5($password, $hash) {
    $salt = substr($hash, 6, 8);  // Extract the salt from the hash
    return $hash === apr1_md5($password, $salt);
}

function apr1_md5($password, $salt) {
    $len = strlen($password);
    $text = $password . '$apr1$' . $salt;
    $bin = pack("H32", md5($password . $salt . $password));

    for ($i = $len; $i > 0; $i -= 16) {
        $text .= substr($bin, 0, min(16, $i));
    }

    for ($i = $len; $i > 0; $i >>= 1) {
        $text .= ($i & 1) ? chr(0) : $password[0];
    }

    $bin = pack("H32", md5($text));

    for ($i = 0; $i < 1000; $i++) {
        $new = ($i & 1) ? $password : $bin;
        if ($i % 3) {
            $new .= $salt;
        }
        if ($i % 7) {
            $new .= $password;
        }
        $new .= ($i & 1) ? $bin : $password;
        $bin = pack("H32", md5($new));
    }

    $tmp = '';
    for ($i = 0; $i < 5; $i++) {
        $k = ($i + 6) % 8;
        $j = ($i + 12) % 8;
        $tmp = $bin[$k] . $bin[$j] . $tmp;
    }
    $tmp = chr(0) . chr(0) . $bin[11] . $tmp;

    $result = '$apr1$' . $salt . '$';
    $tmp = base64_encode($tmp);
    $tmp = str_replace(['+', '/', '='], ['.', '', ''], $tmp);

    return $result . substr($tmp, 0, 22);
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

if ($user_password_hash && validate_apr1_md5($password, $user_password_hash)) {
    $_SESSION['role'] = 'user';
    header('Location: /main/index.html');
    exit;
} elseif ($admin_password_hash && validate_apr1_md5($password, $admin_password_hash)) {
    $_SESSION['role'] = 'admin';
    header('Location: /main/index.html');
    exit;
} else {
    echo "Invalid password.";
}