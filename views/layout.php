<?php /** @var string $content */ /** @var string $title */ ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark">
    <title><?= e($title) ?></title>
    <link rel="icon" href="data:,">
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
<main class="page">
<?= $content ?>
</main>
<script src="/assets/app.js" defer></script>
</body>
</html>
