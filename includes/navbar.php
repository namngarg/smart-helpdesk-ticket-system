<nav class="navbar navbar-expand-lg navbar-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="#"><i class="bi bi-headset me-2"></i>Smart Helpdesk</a>
        <div class="navbar-nav">
            <?php if (current_role() === 'admin'): ?>
                <a class="nav-link" href="../admin/dashboard.php"><i class="bi bi-speedometer2 me-1"></i>Dashboard</a>
                <a class="nav-link" href="../admin/manage_tickets.php"><i class="bi bi-ticket-detailed me-1"></i>Tickets</a>
                <a class="nav-link" href="../admin/manage_users.php"><i class="bi bi-people me-1"></i>Users</a>
            <?php elseif (current_role() === 'employee'): ?>
                <a class="nav-link" href="../employee/my_assigned.php"><i class="bi bi-list-check me-1"></i>My Assigned Tickets</a>
            <?php elseif (current_role() === 'user'): ?>
                <a class="nav-link" href="../user/my_tickets.php"><i class="bi bi-ticket-detailed me-1"></i>My Tickets</a>
                <a class="nav-link" href="../user/create_ticket.php"><i class="bi bi-plus-circle me-1"></i>New Ticket</a>
            <?php endif; ?>
        </div>
        <div class="navbar-nav ms-auto">
            <span class="nav-link text-white-50"><i class="bi bi-person-circle me-1"></i><?= e($_SESSION['name'] ?? '') ?> (<?= e(current_role()) ?>)</span>
            <a class="nav-link" href="../auth/logout.php"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
        </div>
    </div>
</nav>
