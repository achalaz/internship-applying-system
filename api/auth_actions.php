<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action === 'login') {
            $username = trim($_POST['username']);
            $password = $_POST['password'];

            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            // verify password hash (we'll assume users registered with password_hash)
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                
                header("Location: ../dashboard_{$user['role']}.php");
                exit();
            } else {
                $_SESSION['error'] = "Invalid username or password";
                header("Location: ../index.php");
                exit();
            }
        } 
        
        elseif ($action === 'register') {
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $role = $_POST['role']; // student or company

            // Validation
            if (empty($username) || empty($email) || empty($password) || empty($role)) {
                $_SESSION['error'] = "All fields are required";
                header("Location: ../index.php");
                exit();
            }

            // Check if user exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $_SESSION['error'] = "Username or Email already exists";
                header("Location: ../index.php");
                exit();
            }

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            
            try {
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->execute([$username, $email, $hashedPassword, $role]);
                $_SESSION['success'] = "Registration successful! Please login.";
                header("Location: ../index.php");
                exit();
            } catch(PDOException $e) {
                $_SESSION['error'] = "Registration failed. Try again.";
                header("Location: ../index.php");
                exit();
            }
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: ../index.php");
    exit();
}
?>
