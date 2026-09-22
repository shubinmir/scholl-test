<?php
/** @var array $user */
/** @var string $csrf */
/** @var array|null $flash */
$errors = [];
$roles = ['user' => 'Пользователь', 'admin' => 'Администратор'];
?>
<section class="card card--wide">
    <h1 class="card__title">Личный кабинет</h1>
    <p class="card__subtitle">Данные вашей учётной записи</p>

    <?php require __DIR__ . '/partials/_alerts.php'; ?>

    <p class="profile__greeting">Добро пожаловать, <?= e($user['username']) ?>!</p>

    <div class="profile__list">
        <div class="profile__row">
            <span class="profile__key">Имя пользователя</span>
            <span class="profile__value"><?= e($user['username']) ?></span>
        </div>
        <div class="profile__row">
            <span class="profile__key">Email</span>
            <span class="profile__value"><?= e($user['email']) ?></span>
        </div>
        <div class="profile__row">
            <span class="profile__key">Дата регистрации</span>
            <span class="profile__value"><?= e(format_datetime($user['created_at'])) ?></span>
        </div>
        <div class="profile__row">
            <span class="profile__key">Роль</span>
            <span class="profile__value"><?= e($roles[$user['role']] ?? 'Пользователь') ?></span>
        </div>
    </div>

    <form class="profile__actions" method="post" action="/logout">
        <input type="hidden" name="csrf_token" value="<?= e($csrf) ?>">
        <button class="btn btn--ghost btn--block" type="submit">Выйти</button>
    </form>
</section>
