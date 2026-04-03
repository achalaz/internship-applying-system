<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Student applies for an internship
    if ($action === 'apply' && hasRole('student')) {
        $internship_id = $_POST['internship_id'];
        $student_id = $_SESSION['user_id'];

        // Check if already applied
        $stmt = $pdo->prepare("SELECT id FROM applications WHERE student_id = ? AND internship_id = ?");
        $stmt->execute([$student_id, $internship_id]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = "You have already applied for this position.";
        } else {
            // INSERT Query
            $stmt = $pdo->prepare("INSERT INTO applications (student_id, internship_id) VALUES (?, ?)");
            $stmt->execute([$student_id, $internship_id]);
            $_SESSION['success'] = "Application submitted successfully!";
        }
        header("Location: ../dashboard_student.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    // Coordinator approves/rejects application
    if ($action === 'update_status' && hasRole('coordinator')) {
        $application_id = $_GET['id'];
        $status = $_GET['status']; // 'approved' or 'rejected'
        
        $stmt = $pdo->prepare("UPDATE applications SET status = ? WHERE id = ?");
        $stmt->execute([$status, $application_id]);
        
        $_SESSION['success'] = "Application status updated to $status.";
        header("Location: ../dashboard_coordinator.php");
        exit();
    }
}
?>
