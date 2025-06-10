<?php
require 'db.php';
$token = $_SESSION['user_token'] ?? '';
if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
    // Токен поддельный или испорченный
    die('Недопустимый токен');
}
$pdo->prepare("DELETE FROM user_tokens WHERE token = ?")->execute([$token]);
unset($_SESSION['user_token']);
session_destroy();
header("Location: login_page.php");
