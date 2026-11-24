<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

$ticket_id = (int) ($_GET['id'] ?? 0);

$stmt = $conn->prepare(
    'SELECT t.*, creator.name AS creator_name, assignee.name AS assignee_name
     FROM tickets t
     JOIN users creator ON t.created_by = creator.id
     LEFT JOIN users assignee ON t.assigned_to = assignee.id
     WHERE t.id = ?'
);
$stmt->bind_param('i', $ticket_id);
$stmt->execute();
$ticket = $stmt->get_result()->fetch_assoc();

if (!$ticket) {
    http_response_code(404);
    die('Ticket not found.');
}

$role = current_role();
$uid = (int) $_SESSION['user_id'];
$can_view = $role === 'admin'
    || ($role === 'user' && (int) $ticket['created_by'] === $uid)
    || ($role === 'employee' && (int) $ticket['assigned_to'] === $uid);

if (!$can_view) {
    http_response_code(403);
    die('You do not have access to this ticket.');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $comment = trim($_POST['comment'] ?? '');

    if ($comment === '') {
        $error = 'Comment cannot be empty.';
    } else {
        $stmt = $conn->prepare('INSERT INTO ticket_comments (ticket_id, user_id, comment) VALUES (?, ?, ?)');
        $stmt->bind_param('iis', $ticket_id, $uid, $comment);
        $stmt->execute();

        header('Location: view.php?id=' . $ticket_id);
        exit;
    }
}

$stmt = $conn->prepare(
    'SELECT c.*, u.name AS author_name, u.role AS author_role
     FROM ticket_comments c
     JOIN users u ON c.user_id = u.id
     WHERE c.ticket_id = ?
     ORDER BY c.created_at ASC'
);
$stmt->bind_param('i', $ticket_id);
$stmt->execute();
$comments = $stmt->get_result();

$overdue = is_overdue($ticket['deadline'], $ticket['status']);
$back_links = [
    'admin' => '../admin/manage_tickets.php',
    'employee' => '../employee/my_assigned.php',
    'user' => '../user/my_tickets.php',
];

$pageTitle = 'Ticket #' . $ticket_id;
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>
<div class="container" style="max-width: 800px;">
    <a href="<?= e($back_links[$role]) ?>" class="d-inline-block mb-3">&larr; Back</a>

    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <h4><?= e($ticket['title']) ?></h4>
                <div>
                    <?= priority_badge($ticket['priority']) ?>
                    <?= status_badge($ticket['status']) ?>
                    <?= $overdue ? '<span class="badge bg-danger">Overdue</span>' : '' ?>
                </div>
            </div>
            <p class="mt-3"><?= nl2br(e($ticket['description'])) ?></p>
            <hr>
            <div class="row small text-muted">
                <div class="col-md-3">Created by<br><strong class="text-dark"><?= e($ticket['creator_name']) ?></strong></div>
                <div class="col-md-3">Assigned to<br><strong class="text-dark"><?= e($ticket['assignee_name'] ?? 'Unassigned') ?></strong></div>
                <div class="col-md-3">Deadline<br><strong class="text-dark"><?= e($ticket['deadline'] ?? '-') ?></strong></div>
                <div class="col-md-3">Last updated<br><strong class="text-dark"><?= e($ticket['updated_at']) ?></strong></div>
            </div>
        </div>
    </div>

    <h5 class="mb-3">Activity</h5>
    <?php if ($comments->num_rows === 0): ?>
        <p class="text-muted">No comments yet.</p>
    <?php endif; ?>
    <?php while ($c = $comments->fetch_assoc()): ?>
        <div class="card mb-2">
            <div class="card-body py-2">
                <div class="d-flex justify-content-between">
                    <strong><?= e($c['author_name']) ?> <span class="text-muted small">(<?= e($c['author_role']) ?>)</span></strong>
                    <span class="text-muted small"><?= e($c['created_at']) ?></span>
                </div>
                <p class="mb-0"><?= nl2br(e($c['comment'])) ?></p>
            </div>
        </div>
    <?php endwhile; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger mt-3"><?= e($error) ?></div>
    <?php endif; ?>
    <form method="post" class="mt-3">
        <?= csrf_field() ?>
        <div class="mb-2">
            <textarea name="comment" class="form-control" rows="3" placeholder="Add a comment..." required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Post Comment</button>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
