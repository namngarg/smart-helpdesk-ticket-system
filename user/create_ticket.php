<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('user');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'medium';
    $deadline = $_POST['deadline'] ?: null;

    if ($title === '' || $description === '') {
        $error = 'Title and description are required.';
    } else {
        $stmt = $conn->prepare('INSERT INTO tickets (title, description, priority, deadline, created_by) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('ssssi', $title, $description, $priority, $deadline, $_SESSION['user_id']);
        $stmt->execute();

        header('Location: my_tickets.php');
        exit;
    }
}

$pageTitle = 'New Ticket';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>
<div class="container-fluid">
    <div class="page-header d-flex align-items-center gap-3 mb-4">
        <div class="page-icon"><i class="bi bi-plus-circle"></i></div>
        <div>
            <h3 class="mb-0">Create New Ticket</h3>
            <p class="mb-0">Describe your issue and we'll get on it</p>
        </div>
    </div>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="post" class="card p-4" style="max-width: 600px;">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4" required></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Priority</label>
            <select name="priority" class="form-select">
                <option value="low">Low</option>
                <option value="medium" selected>Medium</option>
                <option value="high">High</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Deadline (optional)</label>
            <input type="date" name="deadline" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary"><i class="bi bi-send me-1"></i>Submit Ticket</button>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
