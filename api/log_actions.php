<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Student submits a progress log
    if ($action === 'submit_log' && hasRole('student')) {
        $week_no = $_POST['week_no'];
        $description = trim($_POST['description']);
        $student_id = $_SESSION['user_id'];

        if (empty($week_no) || empty($description)) {
            $_SESSION['error'] = "All fields are required.";
        } else {
            // Check if log already exists for exact week
            $stmt = $pdo->prepare("SELECT id FROM progress_logs WHERE student_id = ? AND week_no = ?");
            $stmt->execute([$student_id, $week_no]);
            if ($stmt->fetch()) {
                $_SESSION['error'] = "Log for this week already exists.";
            } else {
                // INSERT Query
                $stmt = $pdo->prepare("INSERT INTO progress_logs (student_id, week_no, description) VALUES (?, ?, ?)");
                $stmt->execute([$student_id, $week_no, $description]);
                $_SESSION['success'] = "Progress log submitted successfully!";
            }
        }
        header("Location: ../dashboard_student.php");
        exit();
    }
    
    // Supervisor submits evaluation/feedback
    elseif ($action === 'submit_feedback' && hasRole('supervisor')) {
        $log_id = $_POST['log_id'];
        $feedback = trim($_POST['supervisor_feedback']);

        if (empty($feedback)) {
            $_SESSION['error'] = "Feedback cannot be empty.";
        } else {
            // UPDATE Query
            $stmt = $pdo->prepare("UPDATE progress_logs SET supervisor_feedback = ? WHERE id = ?");
            $stmt->execute([$feedback, $log_id]);
            $_SESSION['success'] = "Feedback submitted successfully!";
        }
        header("Location: ../dashboard_supervisor.php");
        exit();
    }
}
?>
