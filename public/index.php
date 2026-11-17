<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/models/Room.php';

sessionStart();

$filters = [
    'category'  => $_GET['category']  ?? '',
    'price_min' => $_GET['price_min'] ?? '',
    'price_max' => $_GET['price_max'] ?? '',
    'capacity'  => $_GET['capacity']  ?? '',
];
$rooms = Room::findWithFilter($filters, 6);
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/index.css">
    <title>Каталог — Сладкие сны</title>
</head>
<body class="container">

<header class="d-flex flex-wrap justify-content-center py-3">
    <a href="index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none">
        <span class="fs-4 mx-2 fw-medium">Сладкие сны</span>
    </a>
    <ul class="nav align-items-center">
        <li class="nav-item"><a href="#" class="nav-link">Приезжайте как гости, уезжайте как друзья!</a></li>
        <?php if (isAdmin()): ?>
            <li class="nav-item"><a href="admin.php" class="nav-link fw-semibold">Админка</a></li>
            <li class="nav-item"><a href="logout.php" class="nav-link">Выйти</a></li>
        <?php else: ?>
            <li class="nav-item"><a href="login.php" class="nav-link">Вход</a></li>
        <?php endif; ?>
    </ul>
</header>

<?= renderFlash() ?>

<main>
    <div class="d-flex justify-content-between flex-wrap align-items-center mb-3">
        <h1>Каталог номеров</h1>
        <form method="get" action="index.php" class="d-flex flex-wrap gap-2 align-items-end">
            <div>
                <label class="form-label mb-0 small">Категория</label>
                <select name="category" class="form-select form-select-sm">
                    <option value="">Любая</option>
                    <?php foreach (['standard'=>'Стандарт','studio'=>'Студия','lux'=>'Люкс'] as $k=>$v): ?>
                        <option value="<?= $k ?>" <?= $filters['category']===$k?'selected':'' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label mb-0 small">Цена от</label>
                <input type="number" name="price_min" min="0" class="form-control form-control-sm"
                       value="<?= e((string)$filters['price_min']) ?>">
            </div>
            <div>
                <label class="form-label mb-0 small">до</label>
                <input type="number" name="price_max" min="0" class="form-control form-control-sm"
                       value="<?= e((string)$filters['price_max']) ?>">
            </div>
            <div>
                <label class="form-label mb-0 small">Мест</label>
                <select name="capacity" class="form-select form-select-sm">
                    <option value="">Любое</option>
                    <?php foreach (['1','2','3'] as $c): ?>
                        <option value="<?= $c ?>" <?= $filters['capacity']===$c?'selected':'' ?>>
                            <?= $c === '3' ? '3+' : $c ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="btn btn-primary btn-sm">Применить</button>
            <a href="index.php" class="btn btn-danger btn-sm">Сбросить</a>
        </form>
    </div>

    <?php if (!$rooms): ?>
        <div class="alert alert-warning">По выбранным условиям номера не найдены.</div>
    <?php else: ?>
        <div class="row g-3">
        <?php foreach ($rooms as $r): ?>
            <div class="col-md-4">
                <div class="card h-100">
                    <img src="<?= e($r['image_path']) ?>" class="card-img-top" alt="<?= e($r['title']) ?>">
                    <div class="card-body">
                        <h3 class="h5"><?= e($r['title']) ?> №<?= e($r['room_number']) ?></h3>
                        <h5><?= number_format((float)$r['price_per_day'], 0, '.', ' ') ?> ₽ / сутки</h5>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">Мест: <?= (int)$r['capacity'] ?></li>
                            <li class="list-group-item">Площадь: <?= e($r['area']) ?> м²</li>
                            <?php if ($r['has_ac']): ?><li class="list-group-item">Кондиционер</li><?php endif; ?>
                            <?php if ($r['has_tv']): ?><li class="list-group-item">Телевизор</li><?php endif; ?>
                            <?php if ($r['has_fridge']): ?><li class="list-group-item">Холодильник</li><?php endif; ?>
                            <?php if ($r['has_wifi']): ?><li class="list-group-item">Wi-Fi</li><?php endif; ?>
                        </ul>
                    </div>
                    <div class="d-grid gap-2 p-3">
                        <a href="order.php?room_id=<?= (int)$r['room_id'] ?>" class="btn btn-success">Забронировать</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
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
