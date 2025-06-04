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
                        <input type="text" class="form-control" placeholder="Код" name="code">
                    </div>
                    <div class="form_group">
                        <button class="btn" name="button" value="login">Подтвердить</button>
                    </div>
                </form>

                <div class="form_group">
                    <a href="./login_page.php">Логин</a>
                </div>

                <div id="response"></div>
            </div>
        </div>
    </div>

    <script>
        document.querySelector('.form').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            // TODO менять путь
            fetch('./verify_2fa.php', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.text())
                .then(data => {
                    const [status, message] = data.split('|');
                    console.log(status)
                    if (status == 'done') {
                        window.location.href = "./dashboard.php"; // Редирект на страницу верификации
                    } else {
                        document.getElementById('response').innerHTML = message; // Показываем сообщение об ошибке
                    }
                })
                .catch(err => {
                    document.getElementById('response').innerHTML = err;
                });
            let inputs = document.querySelectorAll('.form-control')
            inputs.forEach((input) => {
                input.value = ""
            })
        });
    </script>
    <?php
    require_once('./scripts.php');
    ?>
</body>

</html>

<?php

?>