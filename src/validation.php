<?php
declare(strict_types=1);
require_once __DIR__ . '/helpers.php';

function validateBooking(array $post): array
{
    $err = [];
    $fn = trim($post['first_name'] ?? '');
    $ln = trim($post['last_name']  ?? '');
    $ph = trim($post['phone']      ?? '');
    $em = trim($post['email']      ?? '');
    $ci = trim($post['check_in']   ?? '');
    $co = trim($post['check_out']  ?? '');

    if ($fn === '') $err['first_name'] = 'Укажите имя.';
    elseif (!preg_match('/^[А-Яа-яЁё\s.\-]{2,100}$/u', $fn))
        $err['first_name'] = 'Имя: кириллица, пробелы, точки и тире (2–100).';

    if ($ln === '') $err['last_name'] = 'Укажите фамилию.';
    elseif (!preg_match('/^[А-Яа-яЁё\s.\-]{2,100}$/u', $ln))
        $err['last_name'] = 'Фамилия: кириллица, пробелы, точки и тире (2–100).';

    if ($ph === '') $err['phone'] = 'Укажите телефон.';
    elseif (!preg_match('/^\+7\(\d{3}\)\d{3}-\d{2}-\d{2}$/', $ph))
        $err['phone'] = 'Формат телефона: +7(ХХХ)ХХХ-ХХ-ХХ.';

    if ($em === '') $err['email'] = 'Укажите e-mail.';
    elseif (!filter_var($em, FILTER_VALIDATE_EMAIL))
        $err['email'] = 'Некорректный e-mail.';

    $in  = DateTime::createFromFormat('d.m.Y', $ci);
    $out = DateTime::createFromFormat('d.m.Y', $co);
    if (!$in)  $err['check_in']  = 'Дата в формате ДД.ММ.ГГГГ.';
    if (!$out) $err['check_out'] = 'Дата в формате ДД.ММ.ГГГГ.';
    if ($in && $out && $out <= $in)
        $err['check_out'] = 'Дата выезда должна быть позже даты заезда.';
    if ($in && $in < new DateTime('today'))
        $err['check_in'] = 'Дата заезда не может быть в прошлом.';

    return [
        'errors' => $err,
        'data'   => [
            'first_name' => $fn,
            'last_name'  => $ln,
            'phone'      => $ph,
            'email'      => $em,
            'check_in'   => $in  ? $in->format('Y-m-d')  : '',
            'check_out'  => $out ? $out->format('Y-m-d') : '',
        ],
    ];
}

function isInvalid(array $errors, string $key): string
{
    return !empty($errors[$key]) ? 'is-invalid' : '';
}

function fieldError(array $errors, string $key): string
{
    return !empty($errors[$key])
        ? '<div class="invalid-feedback d-block">' . e($errors[$key]) . '</div>'
        : '';
}
