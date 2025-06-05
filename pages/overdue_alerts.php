<?php
session_start();
include '../backend/db_connect.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'ADMIN') {
    header("Location: login.php");
    exit();
}

$overdue_sql = "SELECT l.loanID, u1.name as lender, u2.name as borrower, 
               l.amount, l.due_date, DATEDIFF(CURDATE(), l.due_date) as days_overdue
               FROM loans l
               JOIN users u1 ON l.lenderID = u1.userID
               JOIN users u2 ON l.borrowerID = u2.userID
               WHERE l.status = 'OVERDUE'";
$overdue = $conn->query($overdue_sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Overdue Alerts</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="dashboard">
        <h2>Overdue Loans</h2>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>Loan ID</th>
                    <th>Lender</th>
                    <th>Borrower</th>
                    <th>Amount</th>
                    <th>Due Date</th>
                    <th>Days Overdue</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($loan = $overdue->fetch_assoc()): ?>
                <tr>
                    <td><?= $loan['loanID'] ?></td>
                    <td><?= $loan['lender'] ?></td>
                    <td><?= $loan['borrower'] ?></td>
                    <td>$<?= number_format($loan['amount'], 2) ?></td>
                    <td><?= $loan['due_date'] ?></td>
                    <td class="<?= $loan['days_overdue'] > 30 ? 'text-danger' : '' ?>">
                        <?= $loan['days_overdue'] ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
