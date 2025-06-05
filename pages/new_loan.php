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

// Get all other users (for dropdown)
$users_sql = "SELECT userID, name FROM users WHERE userID != ?";
$stmt = $conn->prepare($users_sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$users = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = $_POST['amount'];
    $due_date = $_POST['due_date'];
    $counterparty = $_POST['counterparty'];
    $type = $_POST['type']; // 'LEND' or 'BORROW'
    
    if ($type == 'LEND') {
        $lender = $_SESSION['user_id'];
        $borrower = $counterparty;
    } else {
        $borrower = $_SESSION['user_id'];
        $lender = $counterparty;
    }
    
    // Set the start_date to current date
    $start_date = date('Y-m-d');
    
    $insert_sql = "INSERT INTO loans (amount, start_date, due_date, lenderID, borrowerID) 
                  VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("dssii", $amount, $start_date, $due_date, $lender, $borrower);
    
    if ($stmt->execute()) {
        // Get the last inserted loan ID
        $loan_id = $conn->insert_id;
        
        // Also add to loan history
        $history_sql = "INSERT INTO loan_history (loanID, lenderID, borrowerID, amount, status) 
                       VALUES (?, ?, ?, ?, 'ACTIVE')";
        $stmt = $conn->prepare($history_sql);
        $stmt->bind_param("iiid", $loan_id, $lender, $borrower, $amount);
        $stmt->execute();
        
        $success = "Loan created successfully!";
    } else {
        $error = "Error creating loan: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create New Loan</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="form-container">
        <h2>Create New Loan</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Transaction Type:</label>
                <div class="radio-group">
                    <input type="radio" id="lend" name="type" value="LEND" checked>
                    <label for="lend">I'm lending money</label>
                    <input type="radio" id="borrow" name="type" value="BORROW">
                    <label for="borrow">I'm borrowing money</label>
                </div>
            </div>
            
            <div class="form-group">
                <label for="counterparty">With:</label>
                <select id="counterparty" name="counterparty" required>
                    <?php while ($user = $users->fetch_assoc()): ?>
                    <option value="<?php echo $user['userID']; ?>">
                        <?php echo htmlspecialchars($user['name']); ?>
                    </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="amount">Amount ($):</label>
                <input type="number" step="0.01" id="amount" name="amount" required>
            </div>
            
            <div class="form-group">
                <label for="due_date">Due Date:</label>
                <input type="date" id="due_date" name="due_date" required>
            </div>
            
            <button type="submit" class="btn">Create Loan</button>
        </form>
    </div>
</body>
</html>
