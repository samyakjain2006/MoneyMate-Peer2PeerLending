<?php
session_start();
include '../backend/db_connect.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'ADMIN') {
    header("Location: login.php");
    exit();
}

// Handle score adjustment
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['adjust_score'])) {
    $userID = $_POST['userID'];
    $change = (int)$_POST['change'];
    
    $update_sql = "UPDATE users SET reliability_score = reliability_score + ? WHERE userID = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ii", $change, $userID);
    $stmt->execute();
}

// Fetch scores
$scores_sql = "SELECT userID, name, email, reliability_score FROM users ORDER BY reliability_score DESC";
$scores = $conn->query($scores_sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reliability Scores</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="dashboard">
        <h2>User Reliability Scores</h2>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Score</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $scores->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= $user['reliability_score'] ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="userID" value="<?= $user['userID'] ?>">
                            <select name="change">
                                <option value="10">+10 (On-time payment)</option>
                                <option value="-5">-5 (Late payment)</option>
                            </select>
                            <button type="submit" name="adjust_score" class="btn-small">Update</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
