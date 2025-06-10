<?php
require 'db.php';
date_default_timezone_set('Asia/Almaty');

// Получаем токен из GET-параметра
$token = $_GET['token'] ?? '';
if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
    // Токен поддельный или испорченный
    die('Недопустимый токен');
}

// Проверка валидности токена (можно расширить логику)
$stmt = $pdo->prepare(
    "SELECT user_id FROM password_resets WHERE token = ? AND expires_at > NOW()"
);
$stmt->execute([$token]);
$valid = $stmt->fetch(PDO::FETCH_ASSOC);
$userId = (int)$valid['user_id'];

$stmt2 = $pdo->prepare("
  SELECT login 
  FROM users 
  WHERE id = ?
  LIMIT 1
");
$stmt2->execute([$userId]);
$user = $stmt2->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die('<div class="form_group">
    <p>Пользователь не найден.</p>
    </div>');
}

$userLogin = htmlspecialchars($user['login'], ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Установка нового пароля</title>
    <link rel="stylesheet" href="./index.css">
</head>

<body>
    <div class="root">
        <div class="wrapper">
            <div class="form_block">
                <div class="form_group">
                    <img src="./img/visiology-logo.png" alt="Visiology">
                </div>

                <?php if (!$valid): ?>
                    <div id="response" class="form_group">
                        <p>Ссылка недействительна или истекла.</p>
                        <a href="./login_page.php">Вернутсья обратно</a>
                    </div>
                <?php else: ?>
                    <div id="response" class="form_group"></div>
                    <form action="reset_password.php" method="post" class="form">
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                        <div class="form_group">
                            <label for="password">Новый пароль:</label>
                        </div>
                        <div class="form_group">
                            <input type="password" id="password" name="password" placeholder="Новый пароль" class="form-control" required>
                        </div>

                        <div class="form_group">
                            <label for="password_confirm">Повторите пароль:</label>
                        </div>
                        <div class="form_group">
                            <input type="password" id="password_confirm" name="password_confirm" placeholder="Повторите пароль" class="form-control" required>
                        </div>
                        <input type="hidden" id="userLogin" value="<?= $userLogin ?>">

                        <div class="form_group">
                            <button type="submit" class="btn">Установить пароль</button>
                        </div>
                    </form>

                    <div class="form_group">
                        <a href="./login_page.php">Войти</a>
                    </div>

                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const responseBlock = document.getElementById('response');
                    const pwd = form.querySelector('#password').value.trim();
                    const conf = form.querySelector('#password_confirm').value.trim();

                    // 1) Длина пароля
                    if (pwd.length < 8) {
                        responseBlock.textContent = 'Пароль должен содержать не менее 8 символов.';
                        return;
                    }


                    // 2) Верхний, нижний регистр, цифры, спецсимволы
                    const hasUpper = /[A-ZА-ЯЁ]/.test(pwd);
                    const hasLower = /[a-zа-яё]/.test(pwd);
                    const hasDigit = /\d/.test(pwd);
                    const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(pwd);
                    if (!hasUpper || !hasLower || !hasDigit || !hasSpecial) {
                        responseBlock.textContent = 'Пароль должен содержать буквы верхнего и нижнего регистра, цифры и специальные символы.';
                        return;
                    }

                    // 3) Совпадение паролей
                    if (pwd !== conf) {
                        responseBlock.textContent = 'Пароли не совпадают.';
                        return;
                    }

                    // 4) Запрет тривиальных паролей
                    const forbidden = [
                        '<?= addslashes($userLogin) ?>',
                    ];
                    if (forbidden.includes(pwd)) {
                        responseBlock.textContent = 'Пароль не должен совпадать с логином или датой рождения.';
                        return;
                    }

                    // 5) Запрет подряд идущих цифр (4 и более)
                    if (/\d{4,}/.test(pwd)) {
                        responseBlock.textContent = 'Пароль не должен содержать подряд более трёх цифр (например даты).';
                        return;
                    }

                    // 6) Если все ок
                    responseBlock.textContent = 'Отправка...';
                    const formData = new FormData(form);
                    fetch(form.action, {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.text())
                        .then(text => {
                            responseBlock.innerHTML = text;
                        })
                        .catch(() => {
                            responseBlock.textContent = 'Ошибка соединения. Попробуйте позже.';
                        });
                });
            }
        });
    </script>
    <?php
    require_once('./scripts.php');
    ?>
</body>

</html>