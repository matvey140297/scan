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
                <form id="registerForm" action="" class="form">
                    <div class="form_group">
                        <input type="email" class="form-control" placeholder="E-mail" name="email">
                    </div>
                    <div class="form_group">
                        <input type="text" class="form-control" placeholder="Логин" name="username">
                    </div>
                    <div class="form_group">
                        <input type="password" class="form-control" placeholder="Пароль" name="password">
                    </div>
                    <div class="form_group">
                        <label class="checkbox-container">
                            <input type="checkbox" name="is_root" value="1">
                            <span class="checkmark"></span>
                            Зарегистрировать как root
                        </label>
                    </div>
                    <div class="form_group">
                        <button class="btn" name="button" value="login">Зарегестрироваться</button>
                    </div>
                </form>

                <div class="form_group">
                    <a href="./dashboard.php">Главная</a>
                </div>

                <div id="response" class="form_group"></div>
            </div>
        </div>
    </div>

    <?php
    require_once('./scripts.php');
    ?>
    <script>
        const form = document.getElementById('registerForm');
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const responseBlock = document.getElementById('response');
            responseBlock.textContent = '';

            const email = form.querySelector('[name="email"]').value.trim();
            const username = form.querySelector('[name="username"]').value.trim();
            const pwd = form.querySelector('[name="password"]').value.trim();

            // 1) Минимальная длина
            if (pwd.length < 8) {
                responseBlock.textContent = 'Пароль должен содержать не менее 8 символов.';
                return;
            }

            // 2) Регистр, цифры, спецсимволы
            const hasUpper = /[A-ZА-ЯЁ]/.test(pwd);
            const hasLower = /[a-zа-яё]/.test(pwd);
            const hasDigit = /\d/.test(pwd);
            const hasSpecial = /[!@#\$%\^&\*\(\),\.\?":\{\}\|<>]/.test(pwd);
            if (!hasUpper || !hasLower || !hasDigit || !hasSpecial) {
                responseBlock.textContent =
                    'Пароль должен содержать буквы верхнего и нижнего регистра, цифры и специальные символы.';
                return;
            }

            // 3) Запрет подряд >=4 цифр (например даты)
            if (/\d{4,}/.test(pwd)) {
                responseBlock.textContent =
                    'Пароль не должен содержать подряд более трёх цифр (например даты).';
                return;
            }

            // 4) Запрет совпадения с логином или email (до @)
            const emailLocal = email.split('@')[0];
            const forbidden = [username.toLowerCase(), emailLocal.toLowerCase()];
            if (forbidden.includes(pwd.toLowerCase())) {
                responseBlock.textContent =
                    'Пароль не должен совпадать с вашим логином или частью вашего e-mail.';
                return;
            }

            // 5) Все ок
            // TODO менять путь
            fetch('./register.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.text())
                .then(data => {
                    responseBlock.innerHTML = data;
                })
                .catch(err => {
                    responseBlock.innerHTML = 'Ошибка соединения';
                    console.error(err);
                });
            let inputs = document.querySelectorAll('.form-control')
            inputs.forEach((input) => {
                input.value = ""
            })
        });
    </script>
</body>

</html>

<?php

?>