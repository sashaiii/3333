<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

function sessionStart(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_start();
}

function csrfToken(): string
{
    sessionStart();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrfToken()) . '">';
}

function csrfCheck(): void
{
    sessionStart();
    $token = $_POST['csrf'] ?? '';
    if (!$token || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(419);
        die('Ошибка CSRF-токена. Обновите страницу.');
    }
}

function currentUser(): ?array
{
    sessionStart();
    return $_SESSION['user'] ?? null;
}

function isAdmin(): bool
{
    return (currentUser()['role'] ?? '') === 'admin';
}

function requireAdmin(): void
{
    if (!isAdmin()) redirect('login.php');
}

function loginUser(string $login, string $password): bool
{
    $stmt = db()->prepare('SELECT id, login, password_hash, role FROM users WHERE login = ? LIMIT 1');
    $stmt->execute([$login]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($password, $user['password_hash'])) return false;

    sessionStart();
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id'    => (int)$user['id'],
        'login' => $user['login'],
        'role'  => $user['role'],
    ];
    return true;
}

function logoutUser(): void
{
    sessionStart();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
function loginUser(string $login, string $password): bool
{
    // Жёстко заданные данные для задания
    if ($login === 'hotel123' && $password === 'adminHotel') {
        sessionStart();
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => 0, 'login' => 'hotel123', 'role' => 'admin'];
        return true;
    }
    
    // Иначе — обычная проверка через БД
    // ... (код из Задания 3)
}
