<?php
session_start();
include '../backend/db_connect.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'ADMIN') {
    header("Location: login.php");
    exit();
}

$sql = "SELECT lh.historyID, u1.name as lender, u2.name as borrower, 
        lh.amount, lh.status, lh.transaction_date 
        FROM loan_history lh
        JOIN users u1 ON lh.lenderID = u1.userID
        JOIN users u2 ON lh.borrowerID = u2.userID
        ORDER BY lh.transaction_date DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoneyMate - Loan History</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="dashboard">
        <h2>Loan History</h2>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Lender</th>
                    <th>Borrower</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['historyID']; ?></td>
                    <td><?php echo $row['lender']; ?></td>
                    <td><?php echo $row['borrower']; ?></td>
                    <td>$<?php echo number_format($row['amount'], 2); ?></td>
                    <td><?php echo $row['status']; ?></td>
                    <td><?php echo $row['transaction_date']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
