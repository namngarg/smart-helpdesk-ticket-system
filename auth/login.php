<?php
require_once __DIR__ . '/../includes/functions.php';

if (is_logged_in()) {
    header('Location: ../index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare('SELECT id, name, password, role FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        switch ($user['role']) {
            case 'admin':
                header('Location: ../admin/dashboard.php');
                break;
            case 'employee':
                header('Location: ../employee/my_assigned.php');
                break;
            default:
                header('Location: ../user/my_tickets.php');
        }
        exit;
    }

    $error = 'Invalid email or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Smart Helpdesk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-shell">
        <div class="auth-illustration">
            <img src="../assets/images/support-illustration.svg" alt="Support illustration">
            <h4>We're here to help</h4>
            <p>Track, assign, and resolve support tickets in one place.</p>
        </div>
        <div class="auth-card">
            <h3 class="mb-4 text-center">Smart Helpdesk</h3>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
            <?php endif; ?>
            <form method="post">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
            <p class="text-center mt-3 mb-0">No account? <a href="register.php">Register User</a></p>

            <button type="button" class="demo-toggle-btn" onclick="toggleDemo()">
                <i class="bi bi-lightning-charge me-1"></i>
                <span id="demoToggleLabel">Quick Access</span>
                <i class="bi bi-chevron-down ms-1" id="demoToggleChevron"></i>
            </button>

            <div class="demo-panel d-none" id="demoPanel">
                <p class="text-muted small mb-2">Explore the app instantly as:</p>
                <div class="demo-account-card" onclick="fillDemo('admin@helpdesk.com','admin123')">
                    <span class="demo-role-icon bg-dark"><i class="bi bi-shield-lock"></i></span>
                    <div>
                        <div class="fw-semibold">Administrator</div>
                        <div class="text-muted small">Full access &mdash; manage tickets &amp; users</div>
                    </div>
                    <i class="bi bi-box-arrow-in-right ms-auto text-muted"></i>
                </div>
                <div class="demo-account-card" onclick="fillDemo('employee1@helpdesk.com','employee123')">
                    <span class="demo-role-icon bg-info"><i class="bi bi-headset"></i></span>
                    <div>
                        <div class="fw-semibold">Support Agent</div>
                        <div class="text-muted small">Resolve assigned tickets</div>
                    </div>
                    <i class="bi bi-box-arrow-in-right ms-auto text-muted"></i>
                </div>
                <p class="text-muted small text-center mb-0 mt-2">New here? <a href="register.php">Create your own account</a> to try it as a customer.</p>
            </div>
        </div>
    </div>
</div>
<script>
function toggleDemo() {
    const panel = document.getElementById('demoPanel');
    const label = document.getElementById('demoToggleLabel');
    const chevron = document.getElementById('demoToggleChevron');
    const hidden = panel.classList.toggle('d-none');
    label.textContent = hidden ? 'Quick Access' : 'Hide Quick Access';
    chevron.classList.toggle('bi-chevron-down', hidden);
    chevron.classList.toggle('bi-chevron-up', !hidden);
}

function fillDemo(email, password) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = password;
}
</script>
</body>
</html>
