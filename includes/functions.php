<?php

session_start();
require_once __DIR__ . '/../config/db.php';

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function current_role() {
    return $_SESSION['role'] ?? null;
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: ../auth/login.php');
        exit;
    }
}

function require_role($roles) {
    if (!is_logged_in() || !in_array(current_role(), (array) $roles, true)) {
        header('Location: ../auth/login.php');
        exit;
    }
}

function status_badge($status) {
    $map = [
        'open' => 'secondary',
        'in_progress' => 'warning',
        'resolved' => 'success',
        'closed' => 'dark',
    ];
    $color = $map[$status] ?? 'secondary';
    return '<span class="badge bg-' . $color . '">' . e(str_replace('_', ' ', $status)) . '</span>';
}

function priority_badge($priority) {
    $map = [
        'low' => 'success',
        'medium' => 'warning',
        'high' => 'danger',
    ];
    $color = $map[$priority] ?? 'secondary';
    return '<span class="badge bg-' . $color . '">' . e($priority) . '</span>';
}

function dot_color($name) {
    $colors = ['#4f46e5', '#0ea5e9', '#f59e0b', '#10b981', '#ec4899', '#8b5cf6'];
    $name = (string) $name;
    return $colors[array_sum(array_map('ord', str_split($name))) % count($colors)];
}

function avatar($name) {
    $name = trim((string) $name);
    $initial = $name === '' ? '?' : mb_strtoupper(mb_substr($name, 0, 1));
    return '<span class="avatar-initial" style="background:' . dot_color($name) . '">' . e($initial) . '</span>' . e($name);
}

function time_ago($datetime) {
    $diff = time() - strtotime($datetime);

    if ($diff < 60) {
        return 'just now';
    }
    if ($diff < 3600) {
        $mins = (int) floor($diff / 60);
        return $mins . ' minute' . ($mins === 1 ? '' : 's') . ' ago';
    }
    if ($diff < 86400) {
        $hours = (int) floor($diff / 3600);
        return $hours . ' hour' . ($hours === 1 ? '' : 's') . ' ago';
    }
    $days = (int) floor($diff / 86400);
    return $days . ' day' . ($days === 1 ? '' : 's') . ' ago';
}

function role_badge($role) {
    $map = [
        'admin' => 'dark',
        'employee' => 'info',
        'user' => 'secondary',
    ];
    $color = $map[$role] ?? 'secondary';
    return '<span class="badge bg-' . $color . '">' . e($role) . '</span>';
}

function is_overdue($deadline, $status) {
    return $deadline && $deadline < date('Y-m-d') && !in_array($status, ['resolved', 'closed'], true);
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf() {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        die('Invalid or missing CSRF token.');
    }
}
