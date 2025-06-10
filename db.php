<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=ertis", "root", "Azaza09za");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    session_start([
        'cookie_httponly' => true,
        'cookie_secure' => true, // Только для HTTPS
        'use_strict_mode' => true
    ]);
} catch (Exception $e) {
    print_r($e);
}
