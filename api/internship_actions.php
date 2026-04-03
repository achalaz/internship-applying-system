<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

// Ensure user is logged in
requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' && hasRole('company')) {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $location = trim($_POST['location']);
        $company_id = $_SESSION['user_id'];

        if (empty($title) || empty($description) || empty($location)) {
            $_SESSION['error'] = "All fields are required.";
        } else {
            // INSERT Query
            $stmt = $pdo->prepare("INSERT INTO internships (company_id, title, description, location) VALUES (?, ?, ?, ?)");
            $stmt->execute([$company_id, $title, $description, $location]);
            $_SESSION['success'] = "Internship posted successfully!";
        }
        header("Location: ../dashboard_company.php");
        exit();
    }
    
    elseif ($action === 'edit' && hasRole('company')) {
        $id = $_POST['id'];
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $location = trim($_POST['location']);
        $company_id = $_SESSION['user_id'];

        // UPDATE Query
        $stmt = $pdo->prepare("UPDATE internships SET title = ?, description = ?, location = ? WHERE id = ? AND company_id = ?");
        $stmt->execute([$title, $description, $location, $id, $company_id]);
        $_SESSION['success'] = "Internship updated successfully!";
        header("Location: ../dashboard_company.php");
        exit();
    }
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    if ($action === 'delete' && hasRole('company')) {
        $id = $_GET['id'];
        $company_id = $_SESSION['user_id'];
        // DELETE Query
        $stmt = $pdo->prepare("DELETE FROM internships WHERE id = ? AND company_id = ?");
        $stmt->execute([$id, $company_id]);
        $_SESSION['success'] = "Internship deleted successfully!";
        header("Location: ../dashboard_company.php");
        exit();
    }
    
    elseif ($action === 'toggle_status' && hasRole('company')) {
        $id = $_GET['id'];
        $status = $_GET['status'] === 'open' ? 'closed' : 'open';
        $company_id = $_SESSION['user_id'];
        
        $stmt = $pdo->prepare("UPDATE internships SET status = ? WHERE id = ? AND company_id = ?");
        $stmt->execute([$status, $id, $company_id]);
        $_SESSION['success'] = "Status updated!";
        header("Location: ../dashboard_company.php");
        exit();
    }
}
?>
