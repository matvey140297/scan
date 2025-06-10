<?php
// change_password.php
require 'db.php';

// 1) Проверяем, что пользователь залогинен
if (!isset($_SESSION['pending_2fa_user_id'])) {
  header("Location: login_page.php");
  exit;
}
$userId = (int)$_SESSION['pending_2fa_user_id'];

$msg = '';

// 2) Если пришёл POST — обрабатываем форму
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $old  = trim($_POST['old_password'] ?? '');
  $new  = trim($_POST['new_password'] ?? '');
  $conf = trim($_POST['new_password_confirm'] ?? '');

  // 2.1) Получаем текущий хеш
  $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ? LIMIT 1");
  $stmt->execute([$userId]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$row || !password_verify($old, $row['password_hash'])) {
    $msg = 'Старый пароль указан неверно.';
  } elseif ($new !== $conf) {
    $msg = 'Новый пароль и подтверждение не совпадают.';
  } elseif (strlen($new) < 8) {
    $msg = 'Новый пароль должен быть не менее 8 символов.';
  } else {
    // всё ок — обновляем пароль и время
    $newHash = password_hash($new, PASSWORD_DEFAULT);
    $upd = $pdo->prepare("
            UPDATE users
               SET password_hash = ?,
                   password_updated_at = NOW()
             WHERE id = ?
        ");
    $upd->execute([$newHash, $userId]);

    // чистим возможные токены сброса
    $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?")
      ->execute([$userId]);

    $msg = 'Пароль успешно изменён.';
    header("Location: ./dashboard.php");
    exit();
  }
}
?>

<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <link rel="stylesheet" href="./index.css">
  <title>Сменить пароль</title>
</head>

<body>
  <!-- Шапка -->
  <header class="site-header">
    <div class="header-left">
      <a href="./dashboard.php" class="logo">Visiology</a>
    </div>
    <div class="header-right">
      <a href="./logout.php" class="header-link">Выйти</a>
    </div>
  </header>

  <!-- Форма смены пароля -->
  <div class="root">
    <div class="wrapper">
      <div class="form_block">

        <div class="form_group">
          <img src="./img/visiology-logo.png" alt="Visiology">
        </div>

        <div id="response" class="form_group">
          <?= htmlspecialchars($msg) ?>
        </div>

        <form action="" method="post" class="form">
          <input type="hidden" name="change_password" value="1">

          <div class="form_group">
            <input
              type="password"
              id="old_password"
              name="old_password"
              placeholder="Старый пароль"
              class="form-control"
              required>
          </div>

          <div class="form_group">
            <input
              type="password"
              id="new_password"
              name="new_password"
              placeholder="Новый пароль"
              class="form-control"
              required>
          </div>

          <div class="form_group">
            <input
              type="password"
              id="new_password_confirm"
              name="new_password_confirm"
              placeholder="Повторите новый пароль"
              class="form-control"
              required>
          </div>

          <div class="form_group">
            <button type="submit" class="btn">Изменить пароль</button>
          </div>
        </form>

      </div>
    </div>
  </div>

  <?php require_once('./scripts.php'); ?>
</body>

</html>