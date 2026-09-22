# Сервис аутентификации с защитой от ботов

Регистрация, вход с капчей и личный кабинет.

## Запуск

```bash
docker compose up --build
```

Открыть <http://localhost> — произойдёт редирект на `/login`.

Остановить: `docker compose down`. Удалить вместе с базой: `docker compose down -v`.

Внешний порт задаётся в `.env`:

```
APP_PORT=80
```