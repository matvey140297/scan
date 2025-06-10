<?php
require 'db.php';
date_default_timezone_set('Asia/Almaty');

$token = $_POST['token'] ?? '';
if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
  // Токен поддельный или испорченный
  die('Недопустимый токен');
}
$pass = $_POST['password'] ?? '';
$pass2 = $_POST['password_confirm'] ?? '';

if (!$pass || $pass !== $pass2) {
  die('Пароли не совпадают или пустые.');
}

// Проверяем токен ещё раз
$stmt = $pdo->prepare("
  SELECT pr.user_id 
  FROM password_resets pr
  WHERE pr.token = ? AND pr.expires_at > NOW()
");
$stmt->execute([$token]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$row) {
  die('Ссылка недействительна или истекла.');
}

// Хешируем и обновляем пароль
$newHash = password_hash($pass, PASSWORD_DEFAULT);
$userId = (int)$row['user_id'];
$upd = $pdo->prepare("UPDATE users SET password_hash = ?, password_updated_at = NOW() WHERE id = ?");
$upd->execute([$newHash, $userId]);

// Удаляем все старые токены этого юзера
$del = $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?");
$del->execute([$userId]);

echo 'Пароль изменён. Можете войти с новым паролем.';
