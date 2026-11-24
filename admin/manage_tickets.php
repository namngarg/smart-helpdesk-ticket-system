<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_id = (int) ($_POST['ticket_id'] ?? 0);
    $assigned_to = (int) ($_POST['assigned_to'] ?? 0) ?: null;
    $priority = $_POST['priority'] ?? 'medium';
    $deadline = $_POST['deadline'] ?: null;

    $stmt = $conn->prepare('UPDATE tickets SET assigned_to = ?, priority = ?, deadline = ? WHERE id = ?');
    $stmt->bind_param('issi', $assigned_to, $priority, $deadline, $ticket_id);
    $stmt->execute();

    header('Location: manage_tickets.php');
    exit;
}

$tickets = $conn->query(
    'SELECT t.*, creator.name AS creator_name, assignee.name AS assignee_name
     FROM tickets t
     JOIN users creator ON t.created_by = creator.id
     LEFT JOIN users assignee ON t.assigned_to = assignee.id
     ORDER BY t.created_at DESC'
);
$employees = $conn->query("SELECT id, name FROM users WHERE role = 'employee'")->fetch_all(MYSQLI_ASSOC);

$pageTitle = 'Manage Tickets';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>
<div class="container">
    <h3 class="mb-4">All Tickets</h3>
    <table class="table table-bordered table-striped align-middle">
        <thead>
        <tr>
            <th>Title</th>
            <th>Created By</th>
            <th>Status</th>
            <th>Assign To</th>
            <th>Priority</th>
            <th>Deadline</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php while ($t = $tickets->fetch_assoc()): ?>
            <tr>
                <td><?= e($t['title']) ?></td>
                <td><?= e($t['creator_name']) ?></td>
                <td><?= status_badge($t['status']) ?></td>
                <td>
                    <form method="post" class="d-flex gap-2">
                        <input type="hidden" name="ticket_id" value="<?= (int) $t['id'] ?>">
                        <select name="assigned_to" class="form-select form-select-sm">
                            <option value="">Unassigned</option>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?= (int) $emp['id'] ?>" <?= (int) $t['assigned_to'] === (int) $emp['id'] ? 'selected' : '' ?>>
                                    <?= e($emp['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                </td>
                <td>
                        <select name="priority" class="form-select form-select-sm">
                            <?php foreach (['low', 'medium', 'high'] as $p): ?>
                                <option value="<?= $p ?>" <?= $t['priority'] === $p ? 'selected' : '' ?>><?= e($p) ?></option>
                            <?php endforeach; ?>
                        </select>
                </td>
                <td>
                        <input type="date" name="deadline" value="<?= e($t['deadline'] ?? '') ?>" class="form-control form-control-sm">
                </td>
                <td>
                        <button type="submit" class="btn btn-sm btn-primary">Save</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
