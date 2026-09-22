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
    <h1 class="card__title">Вход</h1>
    <p class="card__subtitle">Войдите под своим логином или email</p>

    <?php require __DIR__ . '/partials/_alerts.php'; ?>

    <form class="form" method="post" action="/login" novalidate data-validate="login">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">

        <div class="field<?= $inv('login') ?>" data-field="login">
            <label class="field__label" for="login">Логин или Email</label>
            <input class="field__input" type="text" id="login" name="login"
                   value="<?= e($old['login'] ?? '') ?>"
                   autocomplete="username" maxlength="100" spellcheck="false" required>
            <span class="field__error" data-error-for="login"><?= e($err('login')) ?></span>
        </div>

        <div class="field<?= $inv('password') ?>" data-field="password">
            <label class="field__label" for="password">Пароль</label>
            <input class="field__input" type="password" id="password" name="password"
                   autocomplete="current-password" required>
            <span class="field__error" data-error-for="password"><?= e($err('password')) ?></span>
        </div>

        <div class="field<?= $inv('captcha') ?>" data-field="captcha">
            <label class="field__label" for="captcha">Проверка: вы не робот</label>
            <p class="captcha__question"><?= e($captchaQuestion) ?></p>
            <input class="field__input" type="text" id="captcha" name="captcha"
                   inputmode="numeric" autocomplete="off" placeholder="Ответ" required>
            <span class="field__error" data-error-for="captcha"><?= e($err('captcha')) ?></span>
        </div>

        <button class="btn btn--primary btn--block" type="submit">Войти</button>
    </form>

    <p class="form__footer">Нет аккаунта? <a class="link" href="/register">Зарегистрироваться</a></p>
</section>
