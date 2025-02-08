<?php
require_once 'config.php';

function decryptData($data) {
    $key = ENCRYPTION_KEY;
    $iv = substr(hash('sha256', $key), 0, 16);
    return openssl_decrypt(base64_decode(urldecode($data)), 'AES-256-CBC', $key, 0, $iv);
}

function encryptData($data) {
    $key = ENCRYPTION_KEY;
    $iv = substr(hash('sha256', $key), 0, 16);
    return urlencode(base64_encode(openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv)));
}
?>