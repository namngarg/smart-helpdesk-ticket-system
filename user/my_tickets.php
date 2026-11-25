<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('user');

$stmt = $conn->prepare(
    'SELECT t.*, u.name AS assigned_name FROM tickets t
     LEFT JOIN users u ON t.assigned_to = u.id
     WHERE t.created_by = ? ORDER BY t.created_at DESC'
);
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$tickets = $stmt->get_result();

$pageTitle = 'My Tickets';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>
<div class="container-fluid">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="page-icon"><i class="bi bi-ticket-detailed"></i></div>
            <div>
                <h3 class="mb-0">My Tickets</h3>
                <p class="mb-0">Track all your submitted support requests</p>
            </div>
        </div>
        <a href="create_ticket.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>New Ticket</a>
    </div>
    <?php if ($tickets->num_rows === 0): ?>
        <div class="card">
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                You haven't raised any tickets yet.<br>
                <a href="create_ticket.php" class="btn btn-primary mt-3"><i class="bi bi-plus-lg me-1"></i>Create your first ticket</a>
            </div>
        </div>
    <?php else: ?>
    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>Title</th>
            <th>Priority</th>
            <th>Status</th>
            <th>Assigned To</th>
            <th>Deadline</th>
            <th>Created</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($t = $tickets->fetch_assoc()): $overdue = is_overdue($t['deadline'], $t['status']); ?>
            <tr class="<?= $overdue ? 'table-danger' : '' ?>">
                <td><a href="../ticket/view.php?id=<?= (int) $t['id'] ?>"><?= e($t['title']) ?></a></td>
                <td><?= priority_badge($t['priority']) ?></td>
                <td><?= status_badge($t['status']) ?></td>
                <td><?= $t['assigned_name'] ? avatar($t['assigned_name']) : '<span class="text-muted">Unassigned</span>' ?></td>
                <td><?= e($t['deadline'] ?? '-') ?> <?= $overdue ? '<span class="badge bg-danger">Overdue</span>' : '' ?></td>
                <td><?= e($t['created_at']) ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
