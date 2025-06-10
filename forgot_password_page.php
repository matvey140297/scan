<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Восстановление пароля</title>
    <link rel="stylesheet" href="./index.css">
</head>

<body>
    <div class="root">
        <div class="wrapper">
            <div class="form_block">
                <div class="form_group">
                    <img src="./img/visiology-logo.png" alt="">
                </div>

                <form action="forgot_password.php" method="post" class="form">
                    <div class="form_group">
                        <label>Ваш e-mail:</label>
                    </div>
                    <div class="form_group">
                        <input type="email" name="email" placeholder="E-mail" class="form-control" required>
                    </div>
                    <div class="form_group">
                        <button type="submit" class="btn">Сбросить пароль</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
    require_once('./scripts.php');
    ?>
</body>

</html>