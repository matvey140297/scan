<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'db.php';
require 'vendor/autoload.php';  // Подключение PHPMailer
date_default_timezone_set('Asia/Almaty');

$email = trim($_POST['email'] ?? '');
if (!$email) {
  die('Введите email');
}

// 1) Ищем пользователя
$stmt = $pdo->prepare(query: "SELECT id FROM users WHERE login = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
  echo "Если ваш email есть в базе, вы получите письмо.";
  exit;
}

// 2) Генерируем токен
$token = bin2hex(random_bytes(32));
$expires = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');

// 3) Сохраняем в password_resets
$insert = $pdo->prepare("
  INSERT INTO password_resets (user_id, token, expires_at)
  VALUES (?, ?, ?)
");
$insert->execute([$user['id'], $token, $expires]);

// 4) #TODO меняем ссылку когда выгружаем на сайт
$resetLink = "https://ertisdata.kz/reset_password_page.php?token=$token";
$mail = new PHPMailer(true);
try{
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'n2005.08.z@gmail.com';  //TODO изменить email и на других страницах
    $mail->Password = 'rocx hpdt staf igaa';  
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587; #TODO изменить порт в этом и других (login)

    $mail->setFrom('no-reply@your-domain.com', 'Сброс пароля');
    $mail->addAddress(address: $email);
    $mail->Subject = 'Ссылка для сброса пароля';
    $mail->Body    = "Перейдите по ссылке, чтобы задать новый пароль:\n\n$resetLink\n\nСсылка действует 1 час.";
    $mail->send();

    echo "Если ваш email есть в базе, вы получите письмо.";
}catch(Exception $e){
    echo "Что-то пошло не так";
}

