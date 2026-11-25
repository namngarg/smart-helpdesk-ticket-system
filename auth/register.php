<?php
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    header('Location: ../index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($name === '' || $email === '' || $password === '') {
        $error = 'All fields are required.';
    } else {
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();

        if ($stmt->get_result()->fetch_assoc()) {
            $error = 'Email is already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, "user")');
            $stmt->bind_param('sss', $name, $email, $hash);
            $stmt->execute();

            header('Location: login.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Smart Helpdesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-icon"><i class="bi bi-person-plus"></i></div>
        <h3 class="mb-4 text-center">Create Account</h3>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Register</button>
        </form>
        <p class="text-center mt-3 mb-0">Already have an account? <a href="login.php">Login</a></p>
    </div>
</div>
</body>
</html>
