<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/helpers.php';

sessionStart();
if (isAdmin()) redirect('admin.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    if (loginUser(trim($_POST['login'] ?? ''), $_POST['password'] ?? '')) {
        redirect('admin.php');
    }
    $error = 'Неверный логин или пароль.';
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/index.css">
    <title>Вход — Сладкие сны</title>
</head>
<body class="container">

<header class="d-flex flex-wrap justify-content-center py-3">
    <a href="index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none">
        <span class="fs-4 mx-2 fw-medium">Сладкие сны</span>
    </a>
</header>

<main>
    <h1 class="mb-3">Вход</h1>
    <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

    <form method="post" class="my-2">
        <?= csrfField() ?>
        <div class="my-2">
            <label for="login" class="form-label">Логин</label>
            <input type="text" class="form-control" id="login" name="login" required>
        </div>
        <div class="my-2">
            <label for="password" class="form-label">Пароль</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="d-grid gap-2"><button class="btn btn-primary">Войти</button></div>
    </form>
</main>

<footer class="py-2 my-2">
    <ul class="nav justify-content-between align-items-center">
        <li class="nav-item"><a href="#" class="nav-link text-body-secondary">г. Москва, ул. Ивовая, 48</a></li>
        <li class="nav-item"><a href="#" class="nav-link text-body-secondary">Пн-Пт, 8:00–17:00</a></li>
        <li class="nav-item"><a href="tel:88005553535" class="nav-link text-body-secondary">8 (800) 555-35-35</a></li>
        <li class="nav-item"><a href="mailto:info@sladkie-sny.ru" class="nav-link text-body-secondary">info@sladkie-sny.ru</a></li>
    </ul>
</footer>
<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
