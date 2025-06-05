<?php
session_start();
include '../backend/db_connect.php';

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Fetch stats for admin dashboard
$total_users = 0;
$active_loans = 0;
$overdue_loans = 0;

// Get total users
$sql = "SELECT COUNT(*) FROM users";
$result = $conn->query($sql);
if ($result) $total_users = $result->fetch_row()[0];

// Get active loans
$sql = "SELECT COUNT(*) FROM loans WHERE status = 'ACTIVE'";
$result = $conn->query($sql);
if ($result) $active_loans = $result->fetch_row()[0];

// Get overdue loans
$sql = "SELECT COUNT(*) FROM loans WHERE status = 'OVERDUE'";
$result = $conn->query($sql);
if ($result) $overdue_loans = $result->fetch_row()[0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoneyMate - Dashboard</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
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

    <div class="dashboard">
        <h2>Admin Dashboard</h2>
        <div class="stats-container">
            <div class="stat-card">
                <h3>Total Users</h3>
                <p><?php echo $total_users; ?></p>
                <img src="../assets/users-icon.png" alt="Users Icon" class="stat-icon">
            </div>
            <div class="stat-card">
                <h3>Active Loans</h3>
                <p><?php echo $active_loans; ?></p>
                <img src="../assets/active-loans-icon.png" alt="Active Loans Icon" class="stat-icon">
            </div>
            <div class="stat-card">
                <h3>Overdue Loans</h3>
                <p><?php echo $overdue_loans; ?></p>
                <img src="../assets/overdue-icon.png" alt="Overdue Icon" class="stat-icon">
            </div>
        </div>
        
        <!-- Chart placeholder -->
        <div class="chart-container">
            <img src="../assets/chart-placeholder.png" alt="Loan Activity Chart" style="max-width: 100%; max-height: 100%;">
        </div>
    </div>
</body>
</html>
