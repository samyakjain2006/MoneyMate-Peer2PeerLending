<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<div class="navbar">
    <h1>MoneyMate</h1>
    <div class="nav-links">
        <?php if ($_SESSION['role'] == 'ADMIN'): ?>
            <a href="dashboard.php">Dashboard</a>
            <a href="create_user.php">Create User</a>
            <a href="loan_history.php">Loan History</a>
            <a href="reliability_scores.php">Reliability Scores</a>
            <a href="overdue_alerts.php">Overdue Alerts</a>
        <?php else: ?>
            <a href="dashboard.php">Dashboard</a>
            <a href="new_loan.php">New Loan</a>
            <a href="make_payment.php">Make Payment</a>
        <?php endif; ?>
        <a href="../backend/logout.php">Logout</a>
    </div>
</div>
