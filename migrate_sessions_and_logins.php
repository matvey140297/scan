<?php
// migrate_sessions_and_login_attempts.php
require 'db.php';

try {
    // 1) Запрет параллельных сессий: уникальный user_id в user_tokens
    $pdo->exec("
        ALTER TABLE user_tokens
        ADD UNIQUE INDEX unq_user_token_user (user_id);
    ");

    // 2) Таблица для логов попыток входа (по login)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS login_attempts (
          id INT AUTO_INCREMENT PRIMARY KEY,
          login VARCHAR(255)    NOT NULL,
          success TINYINT(1)    NOT NULL,
          ip_address VARCHAR(45) NOT NULL,
          user_agent VARCHAR(255),
          attempted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB
          DEFAULT CHARSET=utf8mb4;
    ");

    echo "Миграции успешно применены.";
} catch (PDOException $e) {
    echo "Ошибка миграции: " . $e->getMessage();
}
