<?php $current = basename($_SERVER['PHP_SELF']); ?>
<aside class="sidebar">
    <div class="sidebar-brand"><i class="bi bi-headset"></i><span class="label">Smart Helpdesk</span></div>

    <?php if (current_role() === 'admin'): ?>
        <a href="../admin/manage_users.php" class="sidebar-cta" title="Create New User"><i class="bi bi-plus-lg"></i><span class="label">Create New User</span></a>
    <?php elseif (current_role() === 'user'): ?>
        <a href="../user/create_ticket.php" class="sidebar-cta" title="New Ticket"><i class="bi bi-plus-lg"></i><span class="label">New Ticket</span></a>
    <?php endif; ?>

    <?php if (current_role() === 'admin'): ?>
        <div class="sidebar-section-label">Overview</div>
        <a class="sidebar-link <?= $current === 'dashboard.php' ? 'active' : '' ?>" href="../admin/dashboard.php" title="Dashboard"><i class="bi bi-speedometer2"></i><span class="label">Dashboard</span></a>

        <div class="sidebar-section-label">Tickets</div>
        <a class="sidebar-link <?= $current === 'manage_tickets.php' ? 'active' : '' ?>" href="../admin/manage_tickets.php" title="All Tickets"><i class="bi bi-ticket-detailed"></i><span class="label">All Tickets</span></a>
        <a class="sidebar-link <?= $current === 'calendar.php' ? 'active' : '' ?>" href="../ticket/calendar.php" title="Calendar"><i class="bi bi-calendar3"></i><span class="label">Calendar</span></a>

        <div class="sidebar-section-label">Team</div>
        <a class="sidebar-link <?= $current === 'manage_users.php' ? 'active' : '' ?>" href="../admin/manage_users.php" title="Users"><i class="bi bi-people"></i><span class="label">Users</span></a>
    <?php elseif (current_role() === 'employee'): ?>
        <div class="sidebar-section-label">Tickets</div>
        <a class="sidebar-link <?= $current === 'my_assigned.php' ? 'active' : '' ?>" href="../employee/my_assigned.php" title="My Assigned Tickets"><i class="bi bi-list-check"></i><span class="label">My Assigned Tickets</span></a>
        <a class="sidebar-link <?= $current === 'calendar.php' ? 'active' : '' ?>" href="../ticket/calendar.php" title="Calendar"><i class="bi bi-calendar3"></i><span class="label">Calendar</span></a>
    <?php elseif (current_role() === 'user'): ?>
        <div class="sidebar-section-label">Tickets</div>
        <a class="sidebar-link <?= $current === 'my_tickets.php' ? 'active' : '' ?>" href="../user/my_tickets.php" title="My Tickets"><i class="bi bi-ticket-detailed"></i><span class="label">My Tickets</span></a>
        <a class="sidebar-link <?= $current === 'calendar.php' ? 'active' : '' ?>" href="../ticket/calendar.php" title="Calendar"><i class="bi bi-calendar3"></i><span class="label">Calendar</span></a>
    <?php endif; ?>

    <div class="sidebar-footer">
        <div class="sidebar-user" title="<?= e($_SESSION['name'] ?? '') ?> (<?= e(current_role()) ?>)"><i class="bi bi-person-circle"></i><span class="label"><?= e($_SESSION['name'] ?? '') ?> (<?= e(current_role()) ?>)</span></div>
        <a class="sidebar-link" href="../auth/logout.php" title="Logout" onclick="return confirm('Are you sure you want to log out?');"><i class="bi bi-box-arrow-right"></i><span class="label">Logout</span></a>
    </div>
</aside>
<main class="main-content">
    <button type="button" class="sidebar-toggle-btn" onclick="toggleSidebar()" title="Toggle sidebar">
        <i class="bi bi-list"></i>
    </button>
