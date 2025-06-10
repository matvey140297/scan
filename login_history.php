<?php
require 'db.php';

// 1) Проверка, что пользователь залогинен
if (!isset($_SESSION['pending_2fa_user_id'])) {
    header('Location: login_page.php');
    exit;
}

$stmt = $pdo->prepare(
    "SELECT id, login
           FROM users 
          WHERE id = ? 
          LIMIT 1"
);
$stmt->execute([(int)$_SESSION['pending_2fa_user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$currentLogin = $user['login'];

// 2) Получаем до 100 последних попыток по этому логину
$stmt = $pdo->prepare("
  SELECT attempted_at, login, success, ip_address, user_agent
    FROM login_attempts
   WHERE login = ?
   ORDER BY attempted_at DESC
   LIMIT 100
");
$stmt->execute([$currentLogin]);
$attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <link rel="stylesheet" href="./index.css">
    <title>История входов</title>
</head>

<body>
    <header class="site-header">
        <div class="header-left">
            <a href="./dashboard.php" class="logo">Visiology</a>
        </div>
        <div class="header-right">
            <a href="./logout.php" class="header-link">Выйти</a>
        </div>
    </header>

    <div class="root">
        <div class="wrapper">
            <div class="login-history-container">
                <h2>История попыток входа (<?= htmlspecialchars($currentLogin) ?>)</h2>
                <table class="login-history">
                    <thead>
                        <tr>
                            <th>Время</th>
                            <th>Логин</th>
                            <th>Результат</th>
                            <th>IP</th>
                            <th>User-Agent</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($attempts as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['attempted_at']) ?></td>
                                <td><?= htmlspecialchars($row['login']) ?></td>
                                <td><?= $row['success'] ? '✔️' : '❌' ?></td>
                                <td><?= htmlspecialchars($row['ip_address']) ?></td>
                                <td><?= htmlspecialchars($row['user_agent']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php require_once('./scripts.php'); ?>
</body>

</html>