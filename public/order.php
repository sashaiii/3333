<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/helpers.php';
require_once __DIR__ . '/../src/validation.php';
require_once __DIR__ . '/../src/models/Room.php';
require_once __DIR__ . '/../src/models/Booking.php';

sessionStart();

$roomId = (int)($_GET['room_id'] ?? $_POST['room_id'] ?? 0);
$room   = $roomId > 0 ? Room::findRoom($roomId) : null;
if (!$room) {
    flashSet('error', 'Номер не найден. Выберите номер в каталоге.');
    redirect('index.php');
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrfCheck();
    $result = validateBooking($_POST);
    $errors = $result['errors'];

    if (!$errors) {
        Booking::create($roomId, $result['data']);
        clearOld();
        flashSet('success', 'Заявка успешно отправлена! Мы свяжемся с вами.');
        redirect('order.php?room_id=' . $roomId);
    }
    rememberOld($_POST);
    flashSet('error', 'Исправьте ошибки в форме.');
}
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/index.css">
    <title>Бронирование — Сладкие сны</title>
</head>
<body class="container">

<header class="d-flex flex-wrap justify-content-center py-3">
    <a href="index.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto link-body-emphasis text-decoration-none">
        <span class="fs-4 mx-2 fw-medium">Сладкие сны</span>
    </a>
    <ul class="nav">
        <li class="nav-item"><a href="index.php" class="nav-link">Каталог</a></li>
    </ul>
</header>

<?= renderFlash() ?>

<main>
    <h1 class="mb-3">Бронирование номера</h1>
    <div class="alert alert-info">
        Вы бронируете: <strong><?= e($room['title']) ?></strong> №<?= e($room['room_number']) ?> —
        <?= number_format((float)$room['price_per_day'], 0, '.', ' ') ?> ₽ / сутки
    </div>

    <form method="post" action="order.php" class="row g-3 my-2" novalidate>
        <?= csrfField() ?>
        <input type="hidden" name="room_id" value="<?= (int)$room['room_id'] ?>">

        <div class="col-md-4">
            <label class="form-label">Имя *</label>
            <input type="text" name="first_name"
                   class="form-control <?= isInvalid($errors, 'first_name') ?>"
                   value="<?= old('first_name') ?>" required>
            <?= fieldError($errors, 'first_name') ?>
        </div>
        <div class="col-md-4">
            <label class="form-label">Фамилия *</label>
            <input type="text" name="last_name"
                   class="form-control <?= isInvalid($errors, 'last_name') ?>"
                   value="<?= old('last_name') ?>" required>
            <?= fieldError($errors, 'last_name') ?>
        </div>
        <div class="col-md-4">
            <label class="form-label">Телефон *</label>
            <input type="text" name="phone"
                   class="form-control <?= isInvalid($errors, 'phone') ?>"
                   value="<?= old('phone') ?>" placeholder="+7(999)123-45-67" required>
            <?= fieldError($errors, 'phone') ?>
        </div>
        <div class="col-md-6">
            <label class="form-label">Почта *</label>
            <input type="email" name="email"
                   class="form-control <?= isInvalid($errors, 'email') ?>"
                   value="<?= old('email') ?>" required>
            <?= fieldError($errors, 'email') ?>
        </div>
        <div class="col-md-3">
            <label class="form-label">Дата заезда *</label>
            <input type="text" name="check_in"
                   class="form-control <?= isInvalid($errors, 'check_in') ?>"
                   value="<?= old('check_in') ?>" placeholder="ДД.ММ.ГГГГ" required>
            <?= fieldError($errors, 'check_in') ?>
        </div>
        <div class="col-md-3">
            <label class="form-label">Дата выезда *</label>
            <input type="text" name="check_out"
                   class="form-control <?= isInvalid($errors, 'check_out') ?>"
                   value="<?= old('check_out') ?>" placeholder="ДД.ММ.ГГГГ" required>
            <?= fieldError($errors, 'check_out') ?>
        </div>
        <div class="d-grid gap-2">
            <button class="btn btn-primary">Отправить заявку</button>
        </div>
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
<script src="js/jquery-3.7.1.slim.min.js"></script>
<script src="js/jquery.inputmask.min.js"></script>
<script>
    // Только UX. Вся валидация — на сервере.
    $(function () {
        $('#phone').inputmask('+7(999)999-99-99');
        $('[name=check_in],[name=check_out]').inputmask('99.99.9999');
    });
</script>
</body>
</html>

// После validateBooking, но перед Booking::create
if (!$errors) {
    if (Booking::hasOverlap($roomId, $result['data']['check_in'], $result['data']['check_out'])) {
        $errors['check_in'] = 'На выбранные даты этот номер уже забронирован.';
        $errors['check_out'] = 'Выберите другие даты.';
    }
}

if (!$errors) {
    Booking::create($roomId, $result['data']);
    // ...
}
