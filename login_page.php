<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация</title>
    <link rel="stylesheet" href="./index.css">
</head>

<body>
    <div class="root">
        <div class="wrapper">
            <div class="form_block">
                <div class="form_group">
                    <img src="./img/visiology-logo.png" alt="">
                </div>
                <form action="" class="form">
                    <div class="form_group">
                        <input type="email" class="form-control" placeholder="E-mail" name="email">
                    </div>
                    <div class="form_group">
                        <input type="password" class="form-control" placeholder="Пароль" name="password">
                    </div>
                    <div class="form_group">
                        <button class="btn" name="button" value="login">Вход</button>
                    </div>
                </form>
                <div id="response" class="form_group"></div>
                <div class="form_group">
                    <a href="./forgot_password_page.php">Забыли пароль?</a>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>Продолжая вы несете административную и уголовную ответственность за неправомерные действия в работе ОИ согласно Законодательств</p>
    </footer>
    <script>
        document.querySelector('.form').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            // TODO менять путь
            fetch('./login.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.text())
                .then(data => {
                    const [status, message] = data.split('|');
                    if (status == '2fa_required') {
                        window.location.href = message; // Редирект на страницу верификации
                    } else if (status === 'error') {
                        document.getElementById('response').innerHTML = message; // Показываем сообщение об ошибке
                    }
                })
                .catch(err => {
                    document.getElementById('response').innerHTML = 'Ошибка соединения';
                    console.error(err);
                });
            let inputs = document.querySelectorAll('.form-control')
        });
    </script>
    <?php
    require_once('./scripts.php');
    ?>
</body>

</html>

<?php

?>