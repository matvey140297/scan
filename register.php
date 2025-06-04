<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $createdAt = time();
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $isRoot = isset($_POST['is_root']) ? 'root' : 'user';

    $stmt = $pdo->prepare("INSERT INTO users (login, password_hash, name, role ) VALUES (?, ?, ?, ?)");
    try {
        $stmt->execute([$email, $password, $username, $isRoot]);
        echo "Регистрация успешна!";
    } catch (PDOException $e) {
        echo $e;
        echo "Ошибка: пользователь уже существует.";
    }
}
?>