<?php
require 'db.php';
$pdo->prepare("DELETE FROM user_tokens WHERE token = ?")->execute([$_SESSION['user_token']]);
unset($_SESSION['user_token']);
session_destroy();
header("Location: login_page.php");
?>