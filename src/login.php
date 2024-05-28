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

// Function to verify the password using apr1-md5 hashing
function verify_apr1_md5_password($password, $hashed_password) {
    $passParts = explode('$', $hashed_password);
    $salt = $passParts[2];
    $hashed = crypt_apr1_md5($password, $salt);
    echo 'Passparts: '.print_r($passParts).'<br>';
    echo 'Password: '.$password.'<br>';
    echo 'Salt: '.$salt.'<br>';
    echo 'Hash: '.$hashed.'<br>';
    echo 'Stored hash: '.$hashed_password.'<br>';
    return $hashed === $hashed_password;
}

// Your custom function to generate apr1-md5 hash
function crypt_apr1_md5($password, $salt) {
    $magic = '$apr1$';
    $max = strlen($password);
    $mix = '';
    $mLen = strlen($magic);
    $sLen = strlen($salt);

    while ($max) {
        if ($max > $sLen) {
            $mix .= $salt;
            $max -= $sLen;
        } else {
            $mix .= substr($salt, 0, $max);
            break;
        }
    }

    $max = strlen($password);

    while ($max) {
        if ($max & 1) {
            $mix .= chr(0);
        } else {
            $mix .= $password[0];
        }
        $max >>= 1;
    }

    $ctx = $password . $magic . $salt . $password;
    $final = md5($password . $salt . $password, true);

    for ($i = $max; $i > 0; $i >>= 1) {
        if ($i & 1) {
            $ctx .= chr(0);
        } else {
            $ctx .= $password[0];
        }
    }

    $ctx = md5($ctx, true);

    $bit = '';

    for ($i = 0; $i < 1000; $i++) {
        $ctx1 = ($i & 1) ? $password : $ctx;
        if ($i % 3) {
            $ctx1 .= $salt;
        }
        if ($i % 7) {
            $ctx1 .= $password;
        }
        $ctx1 .= ($i & 1) ? $ctx : $password;
        $ctx = md5($ctx1, true);
    }

    $len = strlen($ctx);

    for ($i = 0; $i < $len; $i++) {
        $j = ord($ctx[$i]);
        $j += ord($mix[$i % $mLen]);
        $bit .= chr($j & 0xFF);
    }

    $final = md5($final . $bit, true);

    $len = strlen($final);

    $finalPw = '';

    for ($i = 0; $i < $max; $i++) {
        $finalPw .= $final[$i % $len];
    }

    $finalPw = substr_replace($finalPw, $magic, 0, $mLen);
    $finalPw = substr_replace($finalPw, $salt, 12, $sLen);

    return $finalPw;
}

// Path to the .htpasswd file
$htpasswd_file = '/etc/apache2/.htpasswd';

try {
    $password = $_POST['password'];
    $credentials = get_htpasswd_credentials($htpasswd_file);

    $user_password_hash = isset($credentials['uploader']) ? $credentials['uploader'] : null;
    $admin_password_hash = isset($credentials['admin']) ? $credentials['admin'] : null;

    if ($user_password_hash && verify_apr1_md5_password($password, $user_password_hash)) {
        $_SESSION['role'] = 'uploader';
        header('Location: /main/index.html');
        exit;
    } elseif ($admin_password_hash && verify_apr1_md5_password($password, $admin_password_hash)) {
        $_SESSION['role'] = 'admin';
        header('Location: /main/index.html');
        exit;
    } else {
        echo "Invalid password.";
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
