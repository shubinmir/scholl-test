<?php
declare(strict_types=1);

/**
 * Сессии
 */
function session_boot(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'path'     => '/',
        // secure не включаем: сервис поднимается по http://localhost
    ]);
    session_start();
}

function login_user(array $user): void
{
    // Защита от фиксации сессии: после входа выдаём новый идентификатор.
    session_regenerate_id(true);
    $_SESSION['user_id']  = (int) $user['id'];
    $_SESSION['username'] = $user['username'];
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();

    // Иначе следующий session_start() подхватит тот же id из $_COOKIE —
    // и новая сессия (с прощальным сообщением) уедет в уже удалённую куку.
    unset($_COOKIE[session_name()]);
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $user = find_user_by_id((int) $_SESSION['user_id']);
    if ($user === null) {
        // Пользователя удалили — сессия больше не действительна.
        logout_user();
        session_boot();
    }

    return $user;
}

/** Одноразовое сообщение между редиректами (flash). */
function flash_set(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_take(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_check(?string $token): bool
{
    return is_string($token)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function redirect(string $path): never
{
    header('Location: ' . $path, true, 302);
    exit;
}
