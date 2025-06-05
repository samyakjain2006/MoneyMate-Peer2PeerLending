<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $selected_role = isset($_POST['role']) ? $_POST['role'] : '';
    
    $sql = "SELECT * FROM usertype WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if ($password == $user['password']) { // Simple comparison for demo
            // Check if the selected role matches the user's actual role
            if ($selected_role != $user['role']) {
                echo "<script>alert('You do not have access to this role.');</script>";
                echo "<script>window.location.href = '../pages/login.php';</script>";
                exit();
            }
            
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // For regular users, get their userID from users table
            if ($user['role'] == 'USER') {
                $sql = "SELECT userID FROM users WHERE email = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows == 1) {
                    $user_data = $result->fetch_assoc();
                    $_SESSION['user_id'] = $user_data['userID'];
                } else {
                    // User doesn't exist in users table, create them automatically
                    $create_user_sql = "INSERT INTO users (name, email, role, reliability_score) VALUES (?, ?, 'BORROWER', 50)";
                    $stmt = $conn->prepare($create_user_sql);
                    $stmt->bind_param("ss", $username, $username); // Using username as name and email
                    $stmt->execute();
                    
                    // Get the newly created user ID
                    $_SESSION['user_id'] = $conn->insert_id;
                }
            }
            
            header("Location: ../pages/dashboard.php");
            exit();
        } else {
            echo "<script>alert('Invalid password!');</script>";
            echo "<script>window.location.href = '../pages/login.php';</script>";
        }
    } else {
        echo "<script>alert('User not found!');</script>";
        echo "<script>window.location.href = '../pages/login.php';</script>";
    }
}
?>


## The Fix: Modify auth.php to Auto-Create User Records

This change will automatically create a user record in the `users` table whenever someone logs in successfully but doesn't have a corresponding record;
