<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

$role = current_role();
$uid = (int) $_SESSION['user_id'];

$employee_filter = $role === 'admin' ? trim($_GET['employee'] ?? '') : '';

$monthParam = $_GET['month'] ?? date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $monthParam)) {
    $monthParam = date('Y-m');
}
$monthStart = DateTime::createFromFormat('Y-m-d', $monthParam . '-01');
if (!$monthStart) {
    $monthStart = new DateTime('first day of this month');
}
$monthEnd = (clone $monthStart)->modify('last day of this month');
$prevMonth = (clone $monthStart)->modify('-1 month')->format('Y-m');
$nextMonth = (clone $monthStart)->modify('+1 month')->format('Y-m');

$conditions = [];
$baseParams = [];
$baseTypes = '';

if ($role === 'user') {
    $conditions[] = 't.created_by = ?';
    $baseTypes .= 'i';
    $baseParams[] = $uid;
} elseif ($role === 'employee') {
    $conditions[] = 't.assigned_to = ?';
    $baseTypes .= 'i';
    $baseParams[] = $uid;
} elseif ($role === 'admin' && $employee_filter !== '') {
    if ($employee_filter === 'unassigned') {
        $conditions[] = 't.assigned_to IS NULL';
    } else {
        $conditions[] = 't.assigned_to = ?';
        $baseTypes .= 'i';
        $baseParams[] = (int) $employee_filter;
    }
}

$baseWhere = $conditions ? implode(' AND ', $conditions) . ' AND ' : '';

function fetch_tickets($conn, $sql, $types, $params) {
    $stmt = $conn->prepare($sql);
    if ($types !== '') {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result();
}

$monthTickets = fetch_tickets(
    $conn,
    "SELECT t.id, t.title, t.priority, t.status, t.deadline, creator.name AS creator_name, assignee.name AS assignee_name
     FROM tickets t
     JOIN users creator ON t.created_by = creator.id
     LEFT JOIN users assignee ON t.assigned_to = assignee.id
     WHERE {$baseWhere}t.deadline BETWEEN ? AND ?
     ORDER BY t.deadline ASC",
    $baseTypes . 'ss',
    [...$baseParams, $monthStart->format('Y-m-d'), $monthEnd->format('Y-m-d')]
);

$byDay = array_fill(1, (int) $monthStart->format('t'), []);
while ($row = $monthTickets->fetch_assoc()) {
    $day = (int) date('j', strtotime($row['deadline']));
    $byDay[$day][] = $row;
}

$today = date('Y-m-d');
$weekAhead = date('Y-m-d', strtotime('+7 days'));

$upcoming = fetch_tickets(
    $conn,
    "SELECT t.id, t.title, t.priority, t.deadline, creator.name AS creator_name, assignee.name AS assignee_name
     FROM tickets t
     JOIN users creator ON t.created_by = creator.id
     LEFT JOIN users assignee ON t.assigned_to = assignee.id
     WHERE {$baseWhere}t.deadline BETWEEN ? AND ? AND t.status NOT IN ('resolved', 'closed')
     ORDER BY t.deadline ASC
     LIMIT 8",
    $baseTypes . 'ss',
    [...$baseParams, $today, $weekAhead]
);

$overdue = fetch_tickets(
    $conn,
    "SELECT t.id, t.title, t.priority, t.deadline, creator.name AS creator_name, assignee.name AS assignee_name
     FROM tickets t
     JOIN users creator ON t.created_by = creator.id
     LEFT JOIN users assignee ON t.assigned_to = assignee.id
     WHERE {$baseWhere}t.deadline < ? AND t.status NOT IN ('resolved', 'closed')
     ORDER BY t.deadline ASC
     LIMIT 8",
    $baseTypes . 's',
    [...$baseParams, $today]
);

$employees = $role === 'admin'
    ? $conn->query("SELECT id, name FROM users WHERE role = 'employee' ORDER BY name")->fetch_all(MYSQLI_ASSOC)
    : [];

$pageTitle = 'Ticket Calendar';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>
<div class="container-fluid">
    <div class="page-header d-flex align-items-center gap-3 mb-4">
        <div class="page-icon"><i class="bi bi-calendar3"></i></div>
        <div>
            <h3 class="mb-0">Ticket Calendar</h3>
            <p class="mb-0">Deadlines, upcoming due dates, and overdue tickets</p>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-8">
            <div class="card p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <a class="btn btn-sm btn-outline-secondary" href="?<?= http_build_query(['month' => $prevMonth, 'employee' => $employee_filter]) ?>">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                    <h5 class="mb-0"><?= e($monthStart->format('F Y')) ?></h5>
                    <a class="btn btn-sm btn-outline-secondary" href="?<?= http_build_query(['month' => $nextMonth, 'employee' => $employee_filter]) ?>">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </div>

                <?php if ($role === 'admin'): ?>
                <form method="get" class="mb-3">
                    <input type="hidden" name="month" value="<?= e($monthParam) ?>">
                    <select name="employee" class="form-select form-select-sm" style="max-width: 260px;" onchange="this.form.submit()">
                        <option value="">All Employees</option>
                        <option value="unassigned" <?= $employee_filter === 'unassigned' ? 'selected' : '' ?>>Unassigned</option>
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?= (int) $emp['id'] ?>" <?= $employee_filter === (string) $emp['id'] ? 'selected' : '' ?>><?= e($emp['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
                <?php endif; ?>

                <table class="table table-bordered cal-table mb-0">
                    <thead>
                    <tr class="text-center small text-muted">
                        <th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $firstWeekday = (int) $monthStart->format('w');
                    $daysInMonth = (int) $monthStart->format('t');
                    $cell = 0;
                    $totalCells = $firstWeekday + $daysInMonth;
                    $totalCells += (7 - $totalCells % 7) % 7;
                    for ($row = 0; $row < $totalCells / 7; $row++):
                    ?>
                        <tr>
                        <?php for ($col = 0; $col < 7; $col++, $cell++):
                            $day = $cell - $firstWeekday + 1;
                            $isValidDay = $day >= 1 && $day <= $daysInMonth;
                            $dateStr = $isValidDay ? $monthStart->format('Y-m') . '-' . str_pad($day, 2, '0', STR_PAD_LEFT) : null;
                        ?>
                            <td class="cal-cell <?= $dateStr === $today ? 'cal-today' : '' ?>">
                                <?php if ($isValidDay): ?>
                                    <div class="cal-day-num"><?= $day ?></div>
                                    <?php foreach ($byDay[$day] as $t): $isOverdue = is_overdue($t['deadline'], $t['status']); ?>
                                        <a href="view.php?id=<?= (int) $t['id'] ?>" class="cal-chip bg-<?= $isOverdue ? 'danger' : ($t['priority'] === 'high' ? 'danger' : ($t['priority'] === 'medium' ? 'warning' : 'success')) ?>">
                                            <?= e($t['title']) ?>
                                        </a>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </td>
                        <?php endfor; ?>
                        </tr>
                    <?php endfor; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card p-3 mb-3">
                <h6 class="mb-3"><i class="bi bi-hourglass-split me-1 text-warning"></i>Upcoming (7 days)</h6>
                <?php if ($upcoming->num_rows === 0): ?>
                    <p class="text-muted small mb-0">Nothing due in the next 7 days.</p>
                <?php endif; ?>
                <div class="dot-list">
                    <?php while ($t = $upcoming->fetch_assoc()): ?>
                        <div class="dot-row">
                            <a href="view.php?id=<?= (int) $t['id'] ?>"><?= e($t['title']) ?></a>
                            <span class="text-muted small"><?= e($t['deadline']) ?></span>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <div class="card p-3">
                <h6 class="mb-3"><i class="bi bi-exclamation-triangle me-1 text-danger"></i>Overdue</h6>
                <?php if ($overdue->num_rows === 0): ?>
                    <p class="text-muted small mb-0">No overdue tickets.</p>
                <?php endif; ?>
                <div class="dot-list">
                    <?php while ($t = $overdue->fetch_assoc()): ?>
                        <div class="dot-row">
                            <a href="view.php?id=<?= (int) $t['id'] ?>"><?= e($t['title']) ?></a>
                            <span class="text-danger small"><?= e($t['deadline']) ?></span>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
