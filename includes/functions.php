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
