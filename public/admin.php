<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/models/Booking.php';

sessionStart();
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $id     = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $ok = false;
    if ($action === 'approve') $ok = Booking::setStatus($id, 'approved');
    if ($action === 'reject')  $ok = Booking::setStatus($id, 'rejected');
    if ($action === 'delete')  $ok = Booking::delete($id);
    flashSet($ok ? 'success' : 'error',
             $ok ? 'Действие выполнено.' : 'Не удалось выполнить действие.');
    redirect('admin.php');
}

$bookings = Booking::all();
$labels = ['new'=>'Новая','approved'=>'Одобрена','rejected'=>'Отклонена'];
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/index.css">
    <title>Админка — Сладкие сны</title>
</head>
<body class="container">

<header class="d-flex flex-wrap justify-content-center py-3">
    <a href="index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none">
        <span class="fs-4 mx-2 fw-medium">Сладкие сны</span>
    </a>
    <ul class="nav align-items-center">
        <li class="nav-item"><a href="index.php" class="nav-link">Каталог</a></li>
        <li class="nav-item"><a href="logout.php" class="nav-link">Выйти</a></li>
    </ul>
</header>

<?= renderFlash() ?>

<main>
    <h1 class="mb-3">Панель администратора</h1>
    <?php if (!$bookings): ?>
        <div class="alert alert-info">Заявок пока нет.</div>
    <?php else: ?>
        <div class="row g-3">
        <?php foreach ($bookings as $b): ?>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5>Фамилия: <?= e($b['last_name']) ?></h5>
                        <h5>Имя: <?= e($b['first_name']) ?></h5>
                        <h5>Телефон: <?= e($b['phone']) ?></h5>
                        <h5>E-mail: <?= e($b['email']) ?></h5>
                        <p class="mb-1">Номер: <?= e($b['room_title']) ?> №<?= e($b['room_number']) ?></p>
                        <ul class="list-group">
                            <li class="list-group-item">Заезд: <?= date('d.m.Y', strtotime($b['check_in'])) ?></li>
                            <li class="list-group-item">Выезд: <?= date('d.m.Y', strtotime($b['check_out'])) ?></li>
                            <li class="list-group-item">Статус: <strong><?= $labels[$b['status']] ?></strong></li>
                        </ul>
                    </div>
                    <div class="d-grid gap-2 p-3">
                        <form method="post" class="d-grid gap-2">
                            <?= csrfField() ?>
                            <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
                            <?php if ($b['status'] === 'new'): ?>
                                <button name="action" value="approve" class="btn btn-success">Одобрить</button>
                                <button name="action" value="reject"  class="btn btn-warning">Отклонить</button>
                            <?php endif; ?>
                            <button name="action" value="delete" class="btn btn-danger"
                                    onclick="return confirm('Удалить заявку?')">Удалить</button>
                        </form>
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
