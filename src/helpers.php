<?php
declare(strict_types=1);

function e(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function flashSet(string $type, string $message): void
{
    sessionStart();
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function renderFlash(): string
{
    sessionStart();
    $html = '';
    foreach ($_SESSION['flash'] ?? [] as $f) {
        $cls  = $f['type'] === 'error' ? 'alert-danger' : 'alert-success';
        $html .= '<div class="alert ' . $cls . ' text-center" role="alert">'
               . e($f['message']) . '</div>';
    }
    unset($_SESSION['flash']);
    return $html;
}

function old(string $key, string $default = ''): string
{
    sessionStart();
    return e($_SESSION['old'][$key] ?? $default);
}

function rememberOld(array $data): void
{
    sessionStart();
    $_SESSION['old'] = $data;
}

function clearOld(): void
{
    sessionStart();
    unset($_SESSION['old']);
}
