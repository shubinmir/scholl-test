<?php
declare(strict_types=1);

/**
 * Единая точка входа. Маршруты: /register, /login, /profile, /logout.
 */

date_default_timezone_set('Europe/Moscow');

require dirname(__DIR__) . '/src/helpers.php';
require dirname(__DIR__) . '/src/db.php';
require dirname(__DIR__) . '/src/session.php';
require dirname(__DIR__) . '/src/captcha.php';
require dirname(__DIR__) . '/src/validation.php';

session_boot();

$path   = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/', '/') ?: '/';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    match (true) {
        $path === '/'         => redirect(current_user() ? '/profile' : '/login'),
        $path === '/register' => $method === 'POST' ? register_post() : register_get(),
        $path === '/login'    => $method === 'POST' ? login_post() : login_get(),
        $path === '/profile'  => profile_get(),
        $path === '/logout'   => $method === 'POST' ? logout_action() : redirect('/profile'),
        default               => not_found(),
    };
} catch (Throwable $e) {
    error_log('[auth-service] ' . $e);
    http_response_code(500);
    echo 'Внутренняя ошибка сервера';
}

// --- Регистрация ------------------------------------------------------------

function register_get(): void
{
    if (current_user()) {
        redirect('/profile');
    }

    view('register', [
        'title'           => 'Регистрация',
        'errors'          => take_errors(),
        'old'             => take_old(),
        'flash'           => flash_take(),
        'captchaQuestion' => captcha_question(),
        'csrf'            => csrf_token(),
    ]);
}

function register_post(): void
{
    if (!csrf_check($_POST['csrf_token'] ?? null)) {
        captcha_new();
        flash_set('error', 'Сессия истекла, попробуйте ещё раз');
        redirect('/register');
    }

    $errors = [];

    // Капчу проверяем первой: она одноразовая и стирается при любом исходе.
    if (!captcha_verify($_POST['captcha'] ?? null)) {
        $errors['captcha'] = 'Неверный ответ капчи';
    }

    $errors += validate_registration($_POST);

    if ($errors !== []) {
        captcha_new(); // после каждой проверки капча обновляется
        keep_errors($errors);
        keep_old(['username' => trim((string) ($_POST['username'] ?? '')), 'email' => trim((string) ($_POST['email'] ?? ''))]);
        redirect('/register');
    }

    try {
        create_user(
            trim((string) $_POST['username']),
            trim((string) $_POST['email']),
            (string) $_POST['password'],
        );
    } catch (PDOException $e) {
        // Гонка: между проверкой уникальности и вставкой кто-то занял логин/email.
        captcha_new();
        keep_errors(['username' => 'Пользователь с таким именем или email уже существует']);
        redirect('/register');
    }

    captcha_new();
    flash_set('success', 'Регистрация успешна! Войдите в систему.');
    redirect('/login');
}

// --- Вход -------------------------------------------------------------------

function login_get(): void
{
    if (current_user()) {
        redirect('/profile');
    }

    view('login', [
        'title'           => 'Вход',
        'errors'          => take_errors(),
        'old'             => take_old(),
        'flash'           => flash_take(),
        'captchaQuestion' => captcha_question(),
        'csrf'            => csrf_token(),
    ]);
}

function login_post(): void
{
    if (!csrf_check($_POST['csrf_token'] ?? null)) {
        captcha_new();
        flash_set('error', 'Сессия истекла, попробуйте ещё раз');
        redirect('/login');
    }

    $login    = trim((string) ($_POST['login'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    $captchaOk = captcha_verify($_POST['captcha'] ?? null);
    captcha_new(); // новая задача при каждой попытке — и при ошибке, и при успехе

    if (!$captchaOk) {
        keep_errors(['captcha' => 'Неверный ответ капчи']);
        keep_old(['login' => $login]);
        redirect('/login');
    }

    $user = $login !== '' ? find_user_by_login($login) : null;

    // Одинаковое сообщение и без пользователя, и с неверным паролем —
    // чтобы нельзя было перебором узнать существующие логины.
    if ($user === null || !password_verify($password, $user['password_hash'])) {
        keep_errors(['login' => 'Неверный логин или пароль']);
        keep_old(['login' => $login]);
        redirect('/login');
    }

    login_user($user);
    redirect('/profile');
}

// --- Профиль и выход --------------------------------------------------------

function profile_get(): void
{
    $user = current_user();
    if ($user === null) {
        flash_set('info', 'Войдите, чтобы открыть личный кабинет');
        redirect('/login');
    }

    view('profile', [
        'title' => 'Личный кабинет',
        'user'  => $user,
        'flash' => flash_take(),
        'csrf'  => csrf_token(),
    ]);
}

function logout_action(): void
{
    // Без проверки токена чужой сайт мог бы разлогинить пользователя.
    if (!csrf_check($_POST['csrf_token'] ?? null)) {
        redirect('/profile');
    }

    logout_user();
    session_boot();
    flash_set('info', 'Вы вышли из системы');
    redirect('/login');
}

function not_found(): void
{
    http_response_code(404);
    view('not_found', ['title' => 'Страница не найдена']);
}

// --- Перенос ошибок и введённых значений через редирект (PRG) ---------------

function keep_errors(array $errors): void
{
    $_SESSION['form_errors'] = $errors;
}

function take_errors(): array
{
    $errors = $_SESSION['form_errors'] ?? [];
    unset($_SESSION['form_errors']);

    return $errors;
}

function keep_old(array $old): void
{
    $_SESSION['form_old'] = $old;
}

function take_old(): array
{
    $old = $_SESSION['form_old'] ?? [];
    unset($_SESSION['form_old']);

    return $old;
}
