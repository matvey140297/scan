<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'db.php';
require 'vendor/autoload.php';  // Подключение PHPMailer
date_default_timezone_set('Asia/Almaty');

// Функция записи попытки
function logAttempt(PDO $pdo, string $login, bool $success)
{
    if (empty($login) || strlen($login) > 255 || !filter_var($login, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Invalid login format');
    }

    $stmt = $pdo->prepare("
      INSERT INTO login_attempts
        (login, success, ip_address, user_agent)
      VALUES
        (?, ?, ?, ?)
    ");
    $stmt->execute([
        $login,
        $success ? 1 : 0,
        substr($_SERVER['REMOTE_ADDR'], 0, 45),
        substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_SESSION['user_token'])) {
        $token = $_SESSION['user_token'] ?? '';
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            // Токен поддельный или испорченный
            die('Недопустимый токен');
        }
        $pdo->prepare("DELETE FROM user_tokens WHERE token = ?")->execute([$token]);
        unset($_SESSION['user_token']);
    }

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        echo 'error|Введите логин и пароль';
        exit;
    }

    $email = trim($_POST['email'] ?? '');
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) {
        echo 'error|Некорректный email';
        exit;
    }

    // 1) Забираем пользователя вместе с полями failed_attempts и blocked_until
    $stmt = $pdo->prepare(
        "SELECT id, password_hash, failed_attempts, blocked_until 
           FROM users 
          WHERE login = ? 
          LIMIT 1"
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo 'error|Неверный логин или пароль';
        logAttempt($pdo, $email, false);
        exit;
    }

    // 2) Проверяем, не заблокирован ли аккаунт
    if (!empty($user['blocked_until'])) {
        $now          = new DateTime();
        $blockedUntil = new DateTime($user['blocked_until']);
        if ($now < $blockedUntil) {
            $diff    = $now->diff($blockedUntil);
            $minutes = $diff->days * 24 * 60 + $diff->h * 60 + $diff->i;
            echo "error|Аккаунт заблокирован. Попробуйте через {$minutes} минут.";
            exit;
        }
    }

    // 3) Проверяем пароль
    if (!password_verify($password, $user['password_hash'])) {
        $attempts      = $user['failed_attempts'] + 1;
        $blocked_until = null;

        if ($attempts >= 3) {
            // Блокируем на 15 минут
            $blocked_until = (new DateTime('+15 minutes'))->format('Y-m-d H:i:s');
            $message       = 'error|Слишком много неудачных попыток. Аккаунт заблокирован на 15 минут.';
        } else {
            $left    = 3 - $attempts;
            logAttempt($pdo, $email, false);
            $message = "error|Неверный пароль. Осталось попыток: {$left}.";
        }

        // Сохраняем новые значения в БД
        $upd = $pdo->prepare(
            "UPDATE users 
                SET failed_attempts = ?, blocked_until = ? 
              WHERE id = ?"
        );
        $upd->execute([$attempts, $blocked_until, (int)$user['id']]);

        echo $message;
        exit;
    }

    // 4) Успешный пароль — сбрасываем счётчики
    $upd = $pdo->prepare(
        "UPDATE users 
            SET failed_attempts = 0, blocked_until = NULL 
          WHERE id = ?"
    );
    $upd->execute([(int)$user['id']]);

    $userId = (int)$user['id'];
    $stmt2 = $pdo->prepare("
      SELECT 1
        FROM user_tokens
       WHERE user_id = ? AND expires_at > NOW()
    ");
    $stmt2->execute([$userId]);
    if ($stmt2->fetch()) {
        die('У вас уже есть активная сессия. Сначала выйдите из неё.');
    }

    // 5) Генерируем и сохраняем 2FA-код
    $code    = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    $pdo->prepare(
        "INSERT INTO user_2fa_codes (user_id, code, expires_at) 
         VALUES (?, ?, ?)"
    )->execute([(int)$user['id'], $code, $expires]);

    // 6) Отправляем код по e-mail
    $mail = new PHPMailer(true);
    $mail->CharSet   = 'UTF-8';
    $mail->Encoding  = 'base64';
    $mail->setLanguage('ru');

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'n2005.08.z@gmail.com';
        $mail->Password   = 'rocx hpdt staf igaa';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        #TODO Изменить почту отправления
        $mail->setFrom('n2005.08.z@gmail.com', 'Smart Ertis');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Ваш код 2FA';
        $mail->Body    = '<p>Код: ' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '</p>';
        $mail->AltBody = 'Код: ' . $code;

        $mail->send();

        $_SESSION['pending_2fa_user_id'] = (int)$user['id'];
        logAttempt($pdo, $email, true);
        echo '2fa_required|./verify_2fa_page.php';
        exit;
    } catch (Exception $e) {
        echo "error|Ошибка при отправке письма: {$mail->ErrorInfo}";
        exit;
    }
}
