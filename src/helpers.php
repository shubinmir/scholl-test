<?php
declare(strict_types=1);

/** Экранирование для вывода в HTML. */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Рендер вида внутри общего layout.
 *
 * @param array<string,mixed> $data
 */
function view(string $name, array $data = []): void
{
    $viewFile = dirname(__DIR__) . '/views/' . $name . '.php';
    if (!is_file($viewFile)) {
        throw new RuntimeException("Вид не найден: {$name}");
    }

    extract($data, EXTR_SKIP);
    ob_start();
    require $viewFile;
    $content = ob_get_clean();

    $title = $data['title'] ?? 'Сервис аутентификации';
    require dirname(__DIR__) . '/views/layout.php';
}

/** Дата регистрации в человекочитаемом виде. */
function format_datetime(?string $value): string
{
    if (!$value) {
        return '—';
    }
    try {
        // SQLite хранит CURRENT_TIMESTAMP в UTC.
        $dt = new DateTimeImmutable($value, new DateTimeZone('UTC'));
    } catch (Exception) {
        return $value;
    }

    return $dt->setTimezone(new DateTimeZone(date_default_timezone_get()))->format('d.m.Y H:i');
}
