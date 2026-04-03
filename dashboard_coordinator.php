<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireRole('coordinator');

// Fetch all applications
$stmt = $pdo->query("SELECT a.*, u.username as student_name, i.title as internship_title, c.username as company_name
                     FROM applications a
                     JOIN users u ON a.student_id = u.id
                     JOIN internships i ON a.internship_id = i.id
                     JOIN users c ON i.company_id = c.id
                     ORDER BY a.applied_at DESC");
$applications = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coordinator Dashboard - InternMatch</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="logo">InternMatch - <span style="color: var(--text-muted); font-size: 1.1rem; font-weight: normal;">Coordinator Portal</span></div>
        <div class="nav-links">
            <span style="color: var(--text-muted);">Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</span>
            <a href="api/auth_actions.php?action=logout" class="btn btn-outline">Logout</a>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <div class="card glass-panel">
            <div class="flex justify-between items-center mb-4">
                <h3>Manage Student Placements</h3>
                <div class="search-container" style="margin-bottom: 0;">
                    <input type="text" id="searchInput" class="search-input" placeholder="Search students or companies...">
                </div>
            </div>

            <div class="table-responsive">
                <table id="dataTable">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Applied Position</th>
                            <th>Company</th>
                            <th>Status</th>
                            <th>Date Applied</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($applications as $app): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($app['student_name']) ?></strong></td>
                                <td><?= htmlspecialchars($app['internship_title']) ?></td>
                                <td><?= htmlspecialchars($app['company_name']) ?></td>
                                <td>
                                    <span class="badge badge-<?= $app['status'] ?>">
                                        <?= htmlspecialchars($app['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('M d, Y', strtotime($app['applied_at'])) ?></td>
                                <td>
                                    <?php if($app['status'] == 'pending'): ?>
                                        <a href="api/application_actions.php?action=update_status&id=<?= $app['id'] ?>&status=approved" class="btn btn-primary" style="padding: 0.2rem 0.5rem; font-size: 0.8rem;" onclick="return confirmAction('Approve this placement?')">Approve</a>
                                        <a href="api/application_actions.php?action=update_status&id=<?= $app['id'] ?>&status=rejected" class="btn btn-danger" style="padding: 0.2rem 0.5rem; font-size: 0.8rem;" onclick="return confirmAction('Reject this placement?')">Reject</a>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted); font-size: 0.85rem;">Processed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if(empty($applications)): ?>
                            <tr><td colspan="6" class="text-center">No applications found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
