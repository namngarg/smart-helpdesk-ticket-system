<?php
require_once __DIR__ . '/../includes/functions.php';
require_role('admin');

$status_counts = $conn->query("SELECT status, COUNT(*) AS count FROM tickets GROUP BY status")->fetch_all(MYSQLI_ASSOC);
$priority_counts = $conn->query("SELECT priority, COUNT(*) AS count FROM tickets GROUP BY priority")->fetch_all(MYSQLI_ASSOC);
$total_count = $conn->query("SELECT COUNT(*) AS count FROM tickets")->fetch_assoc()['count'];
$overdue_count = $conn->query(
    "SELECT COUNT(*) AS count FROM tickets
     WHERE deadline IS NOT NULL AND deadline < CURDATE() AND status NOT IN ('resolved', 'closed')"
)->fetch_assoc()['count'];

$pageTitle = 'Dashboard';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/navbar.php';
?>
<div class="container">
    <h3 class="mb-4">Ticket Analytics</h3>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center p-3">
                <h2><?= (int) $total_count ?></h2>
                <p class="mb-0">Total Tickets</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center p-3 border-danger">
                <h2 class="text-danger"><?= (int) $overdue_count ?></h2>
                <p class="mb-0">Overdue</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <h5>By Status</h5>
            <canvas id="statusChart"></canvas>
        </div>
        <div class="col-md-6">
            <h5>By Priority</h5>
            <canvas id="priorityChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
const statusData = <?= json_encode($status_counts) ?>;
const priorityData = <?= json_encode($priority_counts) ?>;

new Chart(document.getElementById('statusChart'), {
    type: 'bar',
    data: {
        labels: statusData.map(r => r.status),
        datasets: [{ label: 'Tickets', data: statusData.map(r => r.count), backgroundColor: '#0d6efd' }]
    }
});

new Chart(document.getElementById('priorityChart'), {
    type: 'doughnut',
    data: {
        labels: priorityData.map(r => r.priority),
        datasets: [{ data: priorityData.map(r => r.count), backgroundColor: ['#198754', '#ffc107', '#dc3545'] }]
    }
});
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
