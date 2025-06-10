<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'];
    $user_id = (int)$_SESSION['pending_2fa_user_id'];

    $stmt = $pdo->prepare("SELECT * FROM user_2fa_codes WHERE user_id = ? AND code = ? AND expires_at > NOW()");
    $stmt->execute([$user_id, $code]);
    $match = $stmt->fetch();

    if ($match) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+30 days'));

        $pdo->prepare("INSERT INTO user_tokens (user_id, token, expires_at) VALUES (?, ?, ?)")
            ->execute([$user_id, $token, $expires]);

        $_SESSION['user_token'] = $token;

        $pdo->prepare("DELETE FROM user_2fa_codes WHERE user_id = ?")->execute([$user_id]);

        echo 'done|Ошибок нет';
        exit;
    } else {
        echo 'error|Неверный код';
        exit;
    }
}
?>
