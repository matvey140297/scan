<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255) {
        echo 'error|Некорректный email';
        exit;
    }

    $username = trim($_POST['username'] ?? '');
    if (empty($username) || strlen($username) > 100 || !preg_match('/^[a-zA-Z0-9_ ]{1,100}$/', $username)) {
        echo 'error|Некорректное имя пользователя';
        exit;
    }

    $password = $_POST['password'] ?? '';
    if (empty($password) || strlen($password) < 8) {
        echo 'error|Пароль должен быть не менее 8 символов';
        exit;
    }
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $isRoot = isset($_POST['is_root']) && $_POST['is_root'] === 'on' ? 'root' : 'user';
    if (!in_array($isRoot, ['user', 'root'])) {
        echo 'error|Недопустимая роль пользователя';
        exit;
    }

    // Проверка уникальности email
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE login = ?");
    $checkStmt->execute([$email]);
    if ($checkStmt->fetchColumn() > 0) {
        echo 'error|Пользователь с таким email уже существует';
        exit;
    }

    $createdAt = time();

    $stmt = $pdo->prepare("INSERT INTO users (login, password_hash, name, role) VALUES (?, ?, ?, ?)");
    try {
        $stmt->execute([$email, $passwordHash, $username, $isRoot]);
        echo 'Регистрация успешна!';
    } catch (PDOException $e) {
        echo 'Ошибка регистрации. Пожалуйста, попробуйте снова.';
    }
}
