<?php
/** @var array<string,string> $errors */
/** @var array<string,string> $old */
/** @var string $captchaQuestion */
/** @var string $csrf */
/** @var array|null $flash */
$err = static fn(string $field): string => $errors[$field] ?? '';
$inv = static fn(string $field): string => isset($errors[$field]) ? ' field--invalid' : '';
?>
<section class="card">
    <h1 class="card__title">Регистрация</h1>
    <p class="card__subtitle">Создайте аккаунт, чтобы войти в личный кабинет</p>

    <?php require __DIR__ . '/partials/_alerts.php'; ?>

    <form class="form" method="post" action="/register" novalidate data-validate="register">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">

        <div class="field<?= $inv('username') ?>" data-field="username">
            <label class="field__label" for="username">Имя пользователя</label>
            <input class="field__input" type="text" id="username" name="username"
                   value="<?= e($old['username'] ?? '') ?>"
                   autocomplete="username" maxlength="20" spellcheck="false" required>
            <span class="field__hint">Латиница и цифры, 3–20 символов</span>
            <span class="field__error" data-error-for="username"><?= e($err('username')) ?></span>
        </div>

        <div class="field<?= $inv('email') ?>" data-field="email">
            <label class="field__label" for="email">Email</label>
            <input class="field__input" type="email" id="email" name="email"
                   value="<?= e($old['email'] ?? '') ?>"
                   autocomplete="email" maxlength="100" spellcheck="false" required>
            <span class="field__error" data-error-for="email"><?= e($err('email')) ?></span>
        </div>

        <div class="field<?= $inv('password') ?>" data-field="password">
            <label class="field__label" for="password">Пароль</label>
            <input class="field__input" type="password" id="password" name="password"
                   autocomplete="new-password" required>
            <span class="field__hint">Минимум 6 символов, буквы и цифры</span>
            <span class="field__error" data-error-for="password"><?= e($err('password')) ?></span>
        </div>

        <div class="field<?= $inv('password_confirm') ?>" data-field="password_confirm">
            <label class="field__label" for="password_confirm">Подтверждение пароля</label>
            <input class="field__input" type="password" id="password_confirm" name="password_confirm"
                   autocomplete="new-password" required>
            <span class="field__error" data-error-for="password_confirm"><?= e($err('password_confirm')) ?></span>
        </div>

        <div class="field<?= $inv('captcha') ?>" data-field="captcha">
            <label class="field__label" for="captcha">Проверка: вы не робот</label>
            <p class="captcha__question"><?= e($captchaQuestion) ?></p>
            <input class="field__input" type="text" id="captcha" name="captcha"
                   inputmode="numeric" autocomplete="off" placeholder="Ответ" required>
            <span class="field__error" data-error-for="captcha"><?= e($err('captcha')) ?></span>
        </div>

        <button class="btn btn--primary btn--block" type="submit">Зарегистрироваться</button>
    </form>

    <p class="form__footer">Уже есть аккаунт? <a class="link" href="/login">Войти</a></p>
</section>
