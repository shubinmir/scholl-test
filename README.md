# Сервис аутентификации с защитой от ботов

Регистрация, вход с капчей и личный кабинет.

## Запуск

```bash
docker compose up --build
```

Открыть <http://localhost:8099> — произойдёт редирект на `/login`.

Остановить: `docker compose down`. Удалить вместе с базой: `docker compose down -v`.

Порт меняется в `docker-compose.yml` (`8099:80`).