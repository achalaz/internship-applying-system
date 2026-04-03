<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard_{$_SESSION['role']}.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internship Placement System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <nav class="navbar">
        <div class="logo">InternMatch</div>
    </nav>

    <div class="container auth-wrapper">
        <div class="auth-box glass-panel">
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <div id="loginFormContainer">
                <h3 class="mb-4 text-center">Login to your account</h3>
                <form action="api/auth_actions.php" method="POST" class="validate-form">
                    <input type="hidden" name="action" value="login">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
                    <p class="text-center mt-4" style="color: var(--text-muted); font-size: 0.9rem;">
                        Don't have an account? <a href="#" id="showRegisterBtn">Register here</a>
                    </p>
                </form>
            </div>

            <div id="registerFormContainer" style="display: none;">
                <h3 class="mb-4 text-center">Create an account</h3>
                <form action="api/auth_actions.php" method="POST" class="validate-form">
                    <input type="hidden" name="action" value="register">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" class="form-control" required>
                            <option value="">Select a role...</option>
                            <option value="student">Student</option>
                            <option value="company">Company</option>
                            <!-- Note: Coordinators and Supervisors are usually created by admins -->
                        </select>
                    </div>
                    <button type="submit" class="btn btn-secondary" style="width: 100%;">Register</button>
                    <p class="text-center mt-4" style="color: var(--text-muted); font-size: 0.9rem;">
                        Already have an account? <a href="#" id="showLoginBtn">Login here</a>
                    </p>
                </form>
            </div>

        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        // DOM Manipulation to toggle forms without reloading page
        document.getElementById('showRegisterBtn').addEventListener('click', (e) => {
            e.preventDefault();
            document.getElementById('loginFormContainer').style.display = 'none';
            document.getElementById('registerFormContainer').style.display = 'block';
        });

        document.getElementById('showLoginBtn').addEventListener('click', (e) => {
            e.preventDefault();
            document.getElementById('registerFormContainer').style.display = 'none';
            document.getElementById('loginFormContainer').style.display = 'block';
        });
    </script>
</body>
</html>
