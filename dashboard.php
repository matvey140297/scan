<?php
require 'db.php';

if (!isset($_SESSION['user_token'])) {
    header("Location: login_page.php");
    exit;
}

// Проверка возраста пароля
$stmt = $pdo->prepare("
SELECT password_updated_at, role FROM users WHERE id = ? LIMIT 1;
");
$stmt->execute([(int)$_SESSION['pending_2fa_user_id']]);
$user = $stmt->fetchAll();
$changedAt = $user[0]['password_updated_at'];
$role = $user[0]['role'];
if ($changedAt !== false) {
    $expires    = (new DateTime($changedAt))->modify('+1 month');
    $now        = new DateTime();
    if ($now > $expires) {
        // 1) создаём новый токен для сброса
        $token     = bin2hex(random_bytes(32));
        $expReset  = (new DateTime('+1 day'))->format('Y-m-d H:i:s');

        // 2) очищаем старые и записываем новый
        $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?")
            ->execute([(int)$_SESSION['pending_2fa_user_id']]);
        $pdo->prepare("
            INSERT INTO password_resets (user_id, token, expires_at)
                 VALUES (?,     ?,     ?)
        ")->execute([(int)$_SESSION['pending_2fa_user_id'], $token, $expReset]);

        // 3) редирект на форму сброса с токеном
        header("Location: reset_password_page.php?token=$token");
        exit;
    }
}


// Проверка возраста токена
$token = $_SESSION['user_token'] ?? '';
if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
    // Токен поддельный или испорченный
    die('Недопустимый токен');
}
$stmt = $pdo->prepare("SELECT * FROM user_tokens WHERE token = ? AND expires_at > NOW()");
$stmt->execute([$token]);
$token = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="./index.css">
</head>

<body>
    <?php
    if (!$token) {
        echo "Сессия истекла. <a href='login_page.php'>Войти снова</a>";
        exit;
    }
    ?>
    <header class="site-header">
        <div class="header-left">
            <a href="./dashboard.php" class="logo">Visiology</a>
        </div>
        <div class="header-right">
            <a href="./logout.php" class="header-link">Выйти</a>
            <?php if (isset($_SESSION['pending_2fa_user_id'])): ?>
                <a href="./change_password_page.php" class="header-link">Сменить пароль</a>
                <a href="./login_history.php" class="header-link">История входов</a>
            <?php endif; ?>

            <?php if ($role === 'root'): ?>
                <a href="./register_page.php" class="header-link">Регистрация</a>
            <?php endif; ?>
        </div>
    </header>
    <iframe src="https://ias.ertisdata.kz/" frameborder="0"></iframe>
    <?php
    require_once('./scripts.php');
    ?>
</body>

</html>