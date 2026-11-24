<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="#">Smart Helpdesk</a>
        <div class="navbar-nav">
            <?php if (current_role() === 'admin'): ?>
                <a class="nav-link" href="../admin/dashboard.php">Dashboard</a>
                <a class="nav-link" href="../admin/manage_tickets.php">Tickets</a>
                <a class="nav-link" href="../admin/manage_users.php">Users</a>
            <?php elseif (current_role() === 'employee'): ?>
                <a class="nav-link" href="../employee/my_assigned.php">My Assigned Tickets</a>
            <?php elseif (current_role() === 'user'): ?>
                <a class="nav-link" href="../user/my_tickets.php">My Tickets</a>
                <a class="nav-link" href="../user/create_ticket.php">New Ticket</a>
            <?php endif; ?>
        </div>
        <div class="navbar-nav ms-auto">
            <span class="nav-link text-white-50"><?= e($_SESSION['name'] ?? '') ?> (<?= e(current_role()) ?>)</span>
            <a class="nav-link" href="../auth/logout.php">Logout</a>
        </div>
    </div>
</nav>
