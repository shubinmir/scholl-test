<?php
declare(strict_types=1);

/**
 * Серверная валидация регистрации.
 *
 * @return array<string,string> ошибки по именам полей
 */
function validate_registration(array $input): array
{
    $errors = [];

    $username = trim((string) ($input['username'] ?? ''));
    $email    = trim((string) ($input['email'] ?? ''));
    $password = (string) ($input['password'] ?? '');
    $confirm  = (string) ($input['password_confirm'] ?? '');

    // Имя пользователя: только латиница и цифры, 3–20 символов, уникальное
    if ($username === '') {
        $errors['username'] = 'Укажите имя пользователя';
    } elseif (!preg_match('/^[A-Za-z0-9]{3,20}$/', $username)) {
        $errors['username'] = 'Только латиница и цифры, от 3 до 20 символов';
    } elseif (find_user_by_username($username) !== null) {
        $errors['username'] = 'Пользователь с таким именем уже существует';
    }

    // Email: валидный адрес с доменом, уникальный
    if ($email === '') {
        $errors['email'] = 'Укажите email';
    } elseif (!is_valid_email($email)) {
        $errors['email'] = 'Введите корректный email (например, name@example.com)';
    } elseif (mb_strlen($email) > 100) {
        $errors['email'] = 'Email не длиннее 100 символов';
    } elseif (find_user_by_email($email) !== null) {
        $errors['email'] = 'Пользователь с таким email уже зарегистрирован';
    }

    // Пароль: минимум 6 символов, буквы + цифры
    if ($password === '') {
        $errors['password'] = 'Укажите пароль';
    } elseif (mb_strlen($password) < 6) {
        $errors['password'] = 'Минимум 6 символов';
    } elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
        $errors['password'] = 'Пароль должен содержать и буквы, и цифры';
    } elseif (mb_strlen($password) > 72) {
        // password_hash (bcrypt) учитывает только первые 72 байта.
        $errors['password'] = 'Пароль не длиннее 72 символов';
    }

    if ($confirm === '') {
        $errors['password_confirm'] = 'Повторите пароль';
    } elseif ($password !== $confirm) {
        $errors['password_confirm'] = 'Пароли не совпадают';
    }

    return $errors;
}

/** Валидный email: формат + домен с точкой. */
function is_valid_email(string $email): bool
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    $domain = substr(strrchr($email, '@') ?: '', 1);

    return (bool) preg_match('/^[A-Za-z0-9-]+(\.[A-Za-z0-9-]+)+$/', $domain);
}
