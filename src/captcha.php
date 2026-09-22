<?php
declare(strict_types=1);

/**
 * Простая капча.
 */
function captcha_new(): array
{
    $op = ['+', '-', 'x'][random_int(0, 2)];

    switch ($op) {
        case '+':
            $a = random_int(1, 20);
            $b = random_int(1, 20);
            $answer = $a + $b;
            break;
        case '-':
            // Первое число всегда больше — ответ не уходит в минус.
            $a = random_int(5, 25);
            $b = random_int(1, $a - 1);
            $answer = $a - $b;
            break;
        default:
            $a = random_int(2, 9);
            $b = random_int(2, 9);
            $answer = $a * $b;
    }

    $_SESSION['captcha'] = [
        'question' => sprintf('Сколько будет %d %s %d?', $a, $op, $b),
        'answer'   => $answer,
    ];

    return $_SESSION['captcha'];
}

function captcha_question(): string
{
    if (empty($_SESSION['captcha']['question'])) {
        captcha_new();
    }

    return $_SESSION['captcha']['question'];
}

/**
 * Проверка ответа. Ответ стирается после проверки.
 */
function captcha_verify(?string $input): bool
{
    $expected = $_SESSION['captcha']['answer'] ?? null;
    unset($_SESSION['captcha']);

    if ($expected === null || $input === null || trim($input) === '') {
        return false;
    }

    $input = trim($input);
    if (!preg_match('/^-?\d+$/', $input)) {
        return false;
    }

    return (int) $input === (int) $expected;
}
