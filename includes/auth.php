<?php

require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user']);
}

function require_login(?string $role = null): void
{
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }

    if ($role !== null && ($_SESSION['user']['role'] ?? '') !== $role) {
        http_response_code(403);
        echo 'Access denied';
        exit;
    }
}

function login_user(string $email, string $password): ?array
{
    $stmt = get_db()->prepare('SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        return null;
    }

    unset($user['password']);
    $_SESSION['user'] = $user;

    return $user;
}

function login_user_with_identifier(string $identifier, string $password, string $role): ?array
{
    $identifier = trim($identifier);
    if ($identifier === '' || $password === '') {
        return null;
    }

    if (!in_array($role, ['teacher', 'student'], true)) {
        return null;
    }

    $db = get_db();
    if ($role === 'teacher') {
        $stmt = $db->prepare('SELECT id, name, email, password, role, teacher_unique_id FROM users WHERE role = ? AND (email = ? OR teacher_unique_id = ?) LIMIT 1');
    } else {
        $stmt = $db->prepare('SELECT id, name, email, password, role, student_unique_id FROM users WHERE role = ? AND (email = ? OR student_unique_id = ?) LIMIT 1');
    }

    $stmt->execute([$role, $identifier, $identifier]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        return null;
    }

    unset($user['password']);
    $_SESSION['user'] = $user;

    return $user;
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
