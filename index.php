<?php
require_once __DIR__ . '/includes/functions.php';

if (!is_logged_in()) {
    header('Location: auth/login.php');
    exit;
}

switch (current_role()) {
    case 'admin':
        header('Location: admin/dashboard.php');
        break;
    case 'employee':
        header('Location: employee/my_assigned.php');
        break;
    default:
        header('Location: user/my_tickets.php');
}
exit;
