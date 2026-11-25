<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('employee');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $ticket_id = (int) ($_POST['ticket_id'] ?? 0);
    $status = $_POST['status'] ?? 'open';
    $allowed_statuses = ['open', 'in_progress', 'resolved', 'closed'];

    if (in_array($status, $allowed_statuses, true)) {
        $stmt = $conn->prepare('UPDATE tickets SET status = ? WHERE id = ? AND assigned_to = ?');
        $stmt->bind_param('sii', $status, $ticket_id, $_SESSION['user_id']);
        $stmt->execute();
    }

    header('Location: my_assigned.php');
    exit;
}

$stmt = $conn->prepare(
    'SELECT t.*, u.name AS creator_name FROM tickets t
     JOIN users u ON t.created_by = u.id
     WHERE t.assigned_to = ? ORDER BY t.deadline IS NULL, t.deadline ASC'
);
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$tickets = $stmt->get_result();

$pageTitle = 'My Assigned Tickets';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>
<div class="container-fluid">
    <div class="page-header d-flex align-items-center gap-3 mb-4">
        <div class="page-icon"><i class="bi bi-list-check"></i></div>
        <div>
            <h3 class="mb-0">My Assigned Tickets</h3>
            <p class="mb-0">Tickets routed to you for resolution</p>
        </div>
    </div>
    <?php if ($tickets->num_rows === 0): ?>
        <div class="card">
            <div class="empty-state">
                <i class="bi bi-check2-circle"></i>
                No tickets are assigned to you right now.
            </div>
        </div>
    <?php else: ?>
    <table class="table table-bordered table-striped align-middle">
        <thead>
        <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Priority</th>
            <th>Deadline</th>
            <th>Created By</th>
            <th>Status</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($t = $tickets->fetch_assoc()): $overdue = is_overdue($t['deadline'], $t['status']); ?>
            <tr class="<?= $overdue ? 'table-danger' : '' ?>">
                <td><a href="../ticket/view.php?id=<?= (int) $t['id'] ?>"><?= e($t['title']) ?></a></td>
                <td><?= e($t['description']) ?></td>
                <td><?= priority_badge($t['priority']) ?></td>
                <td><?= e($t['deadline'] ?? '-') ?> <?= $overdue ? '<span class="badge bg-danger">Overdue</span>' : '' ?></td>
                <td><?= avatar($t['creator_name']) ?></td>
                <td>
                    <form method="post" class="d-flex gap-2" onsubmit="return confirm('Are you sure you want to update this ticket\'s status?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="ticket_id" value="<?= (int) $t['id'] ?>">
                        <select name="status" class="form-select form-select-sm">
                            <?php foreach (['open', 'in_progress', 'resolved', 'closed'] as $s): ?>
                                <option value="<?= $s ?>" <?= $t['status'] === $s ? 'selected' : '' ?>>
                                    <?= e(str_replace('_', ' ', $s)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-check-lg"></i></button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
