<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoneyMate - Login</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="login-container">
        <h1>MoneyMate</h1>
        <form action="../backend/auth.php" method="post">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Login as:</label>
                <div class="radio-group">
                    <input type="radio" id="admin" name="role" value="ADMIN" checked>
                    <label for="admin">Admin</label>
                    <input type="radio" id="user" name="role" value="USER">
                    <label for="user">User</label>
                </div>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
    </div>
</body>
</html>
