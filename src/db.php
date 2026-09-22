<?php
declare(strict_types=1);

/**
 * Подключение к SQLite и создание схемы при первом запуске.
 */
function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $path = getenv('APP_DB_PATH') ?: dirname(__DIR__) . '/data/app.sqlite';
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $pdo = new PDO('sqlite:' . $path, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    $pdo->exec('PRAGMA journal_mode = WAL');
    $pdo->exec('PRAGMA foreign_keys = ON');

    migrate($pdo);

    return $pdo;
}

function migrate(PDO $pdo): void
{
    $pdo->exec(<<<SQL
        CREATE TABLE IF NOT EXISTS users (
            id            INTEGER PRIMARY KEY AUTOINCREMENT,
            username      VARCHAR(50)  NOT NULL UNIQUE,
            email         VARCHAR(100) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            role          VARCHAR(20)  NOT NULL DEFAULT 'user',
            created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
        )
    SQL);

    // Регистр не должен создавать «двойников»: Ivan и ivan — один и тот же логин.
    $pdo->exec('CREATE UNIQUE INDEX IF NOT EXISTS users_username_nocase ON users (username COLLATE NOCASE)');
    $pdo->exec('CREATE UNIQUE INDEX IF NOT EXISTS users_email_nocase    ON users (email    COLLATE NOCASE)');
}

function find_user_by_username(string $username): ?array
{
    $stmt = db()->prepare('SELECT * FROM users WHERE username = ? COLLATE NOCASE LIMIT 1');
    $stmt->execute([$username]);

    return $stmt->fetch() ?: null;
}

function find_user_by_email(string $email): ?array
{
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ? COLLATE NOCASE LIMIT 1');
    $stmt->execute([$email]);

    return $stmt->fetch() ?: null;
}

/** Вход принимает и логин, и email — ищем по обоим полям. */
function find_user_by_login(string $login): ?array
{
    $stmt = db()->prepare(
        'SELECT * FROM users WHERE username = :login COLLATE NOCASE OR email = :login COLLATE NOCASE LIMIT 1'
    );
    $stmt->execute(['login' => $login]);

    return $stmt->fetch() ?: null;
}

function find_user_by_id(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);

    return $stmt->fetch() ?: null;
}

function create_user(string $username, string $email, string $password): int
{
    $stmt = db()->prepare(
        'INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)'
    );
    $stmt->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT)]);

    return (int) db()->lastInsertId();
}
