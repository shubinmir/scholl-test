<?php
/** @var array|null $flash */
/** @var array<string,string> $errors */
?>
<?php if (!empty($flash)): ?>
    <div class="alert alert--<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
    <div class="alert alert--error" role="alert">
        <?php if (count($errors) === 1): ?>
            <?= e((string) reset($errors)) ?>
        <?php else: ?>
            Исправьте ошибки в форме:
            <ul class="alert__list">
                <?php foreach ($errors as $message): ?>
                    <li><?= e($message) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
<?php endif; ?>
