<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');

$status_counts_raw = $conn->query("SELECT status, COUNT(*) AS count FROM tickets GROUP BY status")->fetch_all(MYSQLI_ASSOC);
$counts = ['open' => 0, 'in_progress' => 0, 'resolved' => 0, 'closed' => 0];
foreach ($status_counts_raw as $row) {
    $counts[$row['status']] = (int) $row['count'];
}

$total_count = array_sum($counts);
$resolved_rate = $total_count > 0 ? round(($counts['resolved'] + $counts['closed']) / $total_count * 100) : 0;

$overdue_count = $conn->query(
    "SELECT COUNT(*) AS count FROM tickets
     WHERE deadline IS NOT NULL AND deadline < CURDATE() AND status NOT IN ('resolved', 'closed')"
)->fetch_assoc()['count'];

$workload = $conn->query(
    "SELECT assignee.name AS name, COUNT(*) AS count
     FROM tickets t
     LEFT JOIN users assignee ON t.assigned_to = assignee.id
     WHERE t.status NOT IN ('resolved', 'closed')
     GROUP BY t.assigned_to
     ORDER BY count DESC
     LIMIT 6"
)->fetch_all(MYSQLI_ASSOC);

$recent_tickets = $conn->query(
    "SELECT t.id, t.title, t.priority, t.status, t.created_at, creator.name AS creator_name, assignee.name AS assignee_name
     FROM tickets t
     JOIN users creator ON t.created_by = creator.id
     LEFT JOIN users assignee ON t.assigned_to = assignee.id
     ORDER BY t.created_at DESC
     LIMIT 6"
);

$activity = $conn->query(
    "SELECT c.comment, c.created_at, u.name AS author_name, tk.id AS ticket_id, tk.title AS ticket_title
     FROM ticket_comments c
     JOIN users u ON c.user_id = u.id
     JOIN tickets tk ON c.ticket_id = tk.id
     ORDER BY c.created_at DESC
     LIMIT 6"
);

$pageTitle = 'Dashboard';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>
<div class="container-fluid">
    <div class="page-header d-flex align-items-center gap-3 mb-4">
        <div class="page-icon"><i class="bi bi-speedometer2"></i></div>
        <div>
            <h3 class="mb-0">Help Desk Dashboard</h3>
            <p class="mb-0">A live overview of your helpdesk</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-5">
            <div class="card p-3 h-100">
                <div class="row">
                    <div class="col-6 big-stat">
                        <div class="value text-primary"><?= $counts['open'] ?></div>
                        <div class="label">Open Tickets</div>
                    </div>
                    <div class="col-6 big-stat">
                        <div class="value" style="color:#8b5cf6"><?= $counts['in_progress'] ?></div>
                        <div class="label">In Progress</div>
                    </div>
                    <div class="col-6 big-stat">
                        <div class="value text-success"><?= $counts['resolved'] + $counts['closed'] ?></div>
                        <div class="label">Resolved / Closed</div>
                    </div>
                    <div class="col-6 big-stat">
                        <div class="value text-danger"><?= (int) $overdue_count ?></div>
                        <div class="label">Overdue</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 h-100 text-center">
                <h6 class="text-muted">Resolution Rate</h6>
                <div class="donut-wrap">
                    <canvas id="resolutionDonut"></canvas>
                    <div class="donut-center">
                        <div class="value"><?= $resolved_rate ?>%</div>
                        <div class="label">Resolved</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 h-100">
                <h6 class="text-muted mb-2">Workload by Employee</h6>
                <div class="dot-list">
                    <?php if (empty($workload)): ?>
                        <p class="text-muted small mb-0">No open tickets right now.</p>
                    <?php endif; ?>
                    <?php foreach ($workload as $w): $label = $w['name'] ?? 'Unassigned'; ?>
                        <div class="dot-row">
                            <span><span class="dot" style="background:<?= dot_color($label) ?>"></span><?= e($label) ?></span>
                            <strong><?= (int) $w['count'] ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-8">
            <div class="card p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="mb-0">Recent Tickets</h6>
                        <p class="text-muted small mb-0">Latest submissions</p>
                    </div>
                    <a href="manage_tickets.php" class="btn btn-success pill-btn btn-sm">
                        <i class="bi bi-arrow-right-circle me-1"></i>Manage Tickets
                    </a>
                </div>
                <?php if ($recent_tickets->num_rows === 0): ?>
                    <div class="empty-state"><i class="bi bi-inbox"></i>No tickets yet.</div>
                <?php else: ?>
                <table class="table table-borderless align-middle mb-0">
                    <thead>
                    <tr class="text-muted small">
                        <th>#</th>
                        <th>Title</th>
                        <th>Created By</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Assignee</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php while ($t = $recent_tickets->fetch_assoc()): ?>
                        <tr>
                            <td class="text-muted">#<?= (int) $t['id'] ?></td>
                            <td><a href="../ticket/view.php?id=<?= (int) $t['id'] ?>"><?= e($t['title']) ?></a></td>
                            <td><?= avatar($t['creator_name']) ?></td>
                            <td><?= priority_badge($t['priority']) ?></td>
                            <td><?= status_badge($t['status']) ?></td>
                            <td><?= $t['assignee_name'] ? avatar($t['assignee_name']) : '<span class="text-muted">Unassigned</span>' ?></td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3">
                <h6 class="mb-0">Activity Log</h6>
                <p class="text-muted small mb-3">Timeline of recent comments</p>
                <?php if ($activity->num_rows === 0): ?>
                    <p class="text-muted small mb-0">No activity yet.</p>
                <?php else: ?>
                <div class="timeline">
                    <?php while ($a = $activity->fetch_assoc()): ?>
                        <div class="timeline-item">
                            <?= avatar($a['author_name']) ?>
                            <div class="small text-muted"><?= time_ago($a['created_at']) ?></div>
                            <div class="small">commented on <a href="../ticket/view.php?id=<?= (int) $a['ticket_id'] ?>">#<?= (int) $a['ticket_id'] ?> &mdash; <?= e($a['ticket_title']) ?></a></div>
                        </div>
                    <?php endwhile; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('resolutionDonut'), {
    type: 'doughnut',
    data: {
        labels: ['Open', 'In Progress', 'Resolved', 'Closed'],
        datasets: [{
            data: [<?= $counts['open'] ?>, <?= $counts['in_progress'] ?>, <?= $counts['resolved'] ?>, <?= $counts['closed'] ?>],
            backgroundColor: ['#6b7280', '#8b5cf6', '#22c55e', '#0ea5e9'],
            borderWidth: 0,
        }]
    },
    options: {
        cutout: '72%',
        plugins: { legend: { display: false } }
    }
});
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
