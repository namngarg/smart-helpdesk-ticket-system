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
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>My Tickets</h3>
        <a href="create_ticket.php" class="btn btn-primary">+ New Ticket</a>
    </div>
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
                <td><?= e($t['assigned_name'] ?? 'Unassigned') ?></td>
                <td><?= e($t['deadline'] ?? '-') ?> <?= $overdue ? '<span class="badge bg-danger">Overdue</span>' : '' ?></td>
                <td><?= e($t['created_at']) ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
