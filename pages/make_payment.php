<?php
session_start();
include '../backend/db_connect.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'USER') {
    header("Location: login.php");
    exit();
}

// Check if user_id is set in session, if not, get it
if (!isset($_SESSION['user_id'])) {
    $current_user_sql = "SELECT userID FROM users WHERE email = ?";
    $stmt = $conn->prepare($current_user_sql);
    $stmt->bind_param("s", $_SESSION['username']);
    $stmt->execute();
    $user_result = $stmt->get_result();
    
    if ($user_result->num_rows == 1) {
        $current_user = $user_result->fetch_assoc();
        $_SESSION['user_id'] = $current_user['userID'];
    } else {
        // Handle the case where user doesn't exist in users table
        echo "<script>alert('User account not found. Please contact administrator.');</script>";
        echo "<script>window.location.href = 'dashboard.php';</script>";
        exit();
    }
}

// Get user's active loans
$loans_sql = "SELECT l.loanID, u.name as counterparty, l.amount, 
             COALESCE(SUM(p.amount_paid), 0) as amount_paid,
             l.amount - COALESCE(SUM(p.amount_paid), 0) as amount_remaining
             FROM loans l
             JOIN users u ON (l.lenderID = ? AND l.borrowerID = u.userID) 
                          OR (l.borrowerID = ? AND l.lenderID = u.userID)
             LEFT JOIN payments p ON l.loanID = p.loanID
             WHERE l.status = 'ACTIVE'
             GROUP BY l.loanID";
$stmt = $conn->prepare($loans_sql);
$stmt->bind_param("ii", $_SESSION['user_id'], $_SESSION['user_id']);
$stmt->execute();
$loans = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $loan_id = $_POST['loan_id'];
    $amount = $_POST['amount'];
    
    // Get current loan details
    $loan_details_sql = "SELECT l.amount, COALESCE(SUM(p.amount_paid), 0) as amount_paid 
                        FROM loans l 
                        LEFT JOIN payments p ON l.loanID = p.loanID 
                        WHERE l.loanID = ? 
                        GROUP BY l.loanID";
    $stmt = $conn->prepare($loan_details_sql);
    $stmt->bind_param("i", $loan_id);
    $stmt->execute();
    $loan_details = $stmt->get_result()->fetch_assoc();
    
    $total_amount = $loan_details['amount'];
    $amount_paid = $loan_details['amount_paid'];
    $new_amount_paid = $amount_paid + $amount;
    $amount_remaining = $total_amount - $new_amount_paid;
    
    // Record payment
    $payment_sql = "INSERT INTO payments (loanID, amount_paid, amount_remaining, payment_date)
                   VALUES (?, ?, ?, CURDATE())";
    $stmt = $conn->prepare($payment_sql);
    $stmt->bind_param("idd", $loan_id, $amount, $amount_remaining);
    $stmt->execute();
    
    // Update loan status based on remaining amount
    $status = ($amount_remaining <= 0) ? 'PAID' : 'ACTIVE';
    $update_sql = "UPDATE loans SET status = ? WHERE loanID = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("si", $status, $loan_id);
    $stmt->execute();
    
    // If loan is paid off, update reliability score
    if ($status == 'PAID') {
        // Get the borrower ID
        $borrower_sql = "SELECT borrowerID FROM loans WHERE loanID = ?";
        $stmt = $conn->prepare($borrower_sql);
        $stmt->bind_param("i", $loan_id);
        $stmt->execute();
        $borrower_result = $stmt->get_result()->fetch_assoc();
        $borrower_id = $borrower_result['borrowerID'];
        
        // Update reliability score
        $update_score_sql = "UPDATE users SET reliability_score = reliability_score + 10 
                           WHERE userID = ?";
        $stmt = $conn->prepare($update_score_sql);
        $stmt->bind_param("i", $borrower_id);
        $stmt->execute();
        
        // Update loan history
        $history_sql = "UPDATE loan_history SET status = 'PAID' WHERE loanID = ?";
        $stmt = $conn->prepare($history_sql);
        $stmt->bind_param("i", $loan_id);
        $stmt->execute();
    }
    
    $success = "Payment recorded successfully!";
    
    // Refresh the loans list
    $stmt = $conn->prepare($loans_sql);
    $stmt->bind_param("ii", $_SESSION['user_id'], $_SESSION['user_id']);
    $stmt->execute();
    $loans = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Make Payment</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="form-container">
        <h2>Make Payment</h2>
        
        <?php if (isset($success)): ?>
            <div class="alert success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="loan_id">Select Loan:</label>
                <select id="loan_id" name="loan_id" required>
                    <?php if ($loans->num_rows > 0): ?>
                        <?php while ($loan = $loans->fetch_assoc()): ?>
                        <option value="<?= $loan['loanID'] ?>">
                            Loan #<?= $loan['loanID'] ?> with <?= $loan['counterparty'] ?> 
                            ($<?= number_format($loan['amount'], 2) ?>, 
                            $<?= number_format($loan['amount_paid'], 2) ?> paid, 
                            $<?= number_format($loan['amount_remaining'], 2) ?> remaining)
                        </option>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <option value="">No active loans found</option>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="amount">Amount ($):</label>
                <input type="number" step="0.01" id="amount" name="amount" required>
            </div>
            
            <button type="submit" class="btn" <?= ($loans->num_rows == 0) ? 'disabled' : '' ?>>Submit Payment</button>
        </form>
    </div>
</body>
</html>
