<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireRole('student');

$student_id = $_SESSION['user_id'];

// Fetch Open Internships
$stmt = $pdo->query("SELECT i.*, c.username as company_name 
                     FROM internships i 
                     JOIN users c ON i.company_id = c.id 
                     WHERE i.status = 'open' 
                     ORDER BY i.created_at DESC");
$open_internships = $stmt->fetchAll();

// Fetch My Applications
$stmt = $pdo->prepare("SELECT a.*, i.title, i.location, c.username as company_name 
                       FROM applications a
                       JOIN internships i ON a.internship_id = i.id
                       JOIN users c ON i.company_id = c.id
                       WHERE a.student_id = ?");
$stmt->execute([$student_id]);
$my_applications = $stmt->fetchAll();

// Fetch My Progress Logs
$stmt = $pdo->prepare("SELECT * FROM progress_logs WHERE student_id = ? ORDER BY week_no DESC");
$stmt->execute([$student_id]);
$logs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - InternMatch</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="logo">InternMatch</div>
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

        <div class="grid grid-cols-2">
            <!-- Left Column: Available Internships -->
            <div class="card glass-panel" style="grid-column: span 2;">
                <div class="flex justify-between items-center mb-4">
                    <h3>Available Internships</h3>
                    <div class="search-container" style="margin-bottom: 0;">
                        <input type="text" id="searchInput" class="search-input" placeholder="Search internships...">
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table id="dataTable">
                        <thead>
                            <tr>
                                <th>Position</th>
                                <th>Company</th>
                                <th>Location</th>
                                <th>Description</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($open_internships as $internship): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($internship['title']) ?></strong></td>
                                    <td><?= htmlspecialchars($internship['company_name']) ?></td>
                                    <td><?= htmlspecialchars($internship['location']) ?></td>
                                    <td><?= htmlspecialchars(substr($internship['description'], 0, 50)) ?>...</td>
                                    <td>
                                        <form action="api/application_actions.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="apply">
                                            <input type="hidden" name="internship_id" value="<?= $internship['id'] ?>">
                                            <button type="submit" class="btn btn-primary" onclick="return confirmAction('Are you sure you want to apply for this position?')">Apply</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if(empty($open_internships)): ?>
                                <tr><td colspan="5" class="text-center">No open internships currently available.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Bottom Left: My Applications -->
            <div class="card glass-panel mt-4">
                <h3>My Applications</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Position</th>
                                <th>Company</th>
                                <th>Status</th>
                                <th>Applied At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($my_applications as $app): ?>
                                <tr>
                                    <td><?= htmlspecialchars($app['title']) ?></td>
                                    <td><?= htmlspecialchars($app['company_name']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $app['status'] ?>">
                                            <?= htmlspecialchars($app['status']) ?>
                                        </span>
                                    </td>
                                    <td style="font-size: 0.85rem; color: var(--text-muted);">
                                        <?= date('M d, Y', strtotime($app['applied_at'])) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Bottom Right: Progress Logs -->
            <div class="card glass-panel mt-4">
                <div class="flex justify-between items-center mb-4">
                    <h3>My Progress Logs</h3>
                    <button class="btn btn-secondary" data-modal-target="true" data-action="add_log">Submit Log</button>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Week</th>
                                <th>Details</th>
                                <th>Feedback</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($logs as $log): ?>
                                <tr>
                                    <td>Week <?= htmlspecialchars($log['week_no']) ?></td>
                                    <td><?= nl2br(htmlspecialchars($log['description'])) ?></td>
                                    <td>
                                        <?php if($log['supervisor_feedback']): ?>
                                            <div style="background: rgba(16, 185, 129, 0.1); padding: 0.5rem; border-radius: 4px; font-size: 0.9rem;">
                                                <?= nl2br(htmlspecialchars($log['supervisor_feedback'])) ?>
                                            </div>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted); font-style: italic;">Pending review</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal for Submitting Log -->
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Submit Weekly Progress Log</h3>
                <button class="modal-close" id="modalClose">&times;</button>
            </div>
            <form action="api/log_actions.php" method="POST" class="validate-form">
                <input type="hidden" name="action" value="submit_log">
                <div class="form-group">
                    <label>Week Number</label>
                    <input type="number" name="week_no" class="form-control" min="1" max="52" required>
                </div>
                <div class="form-group">
                    <label>Activities & Progress</label>
                    <textarea name="description" class="form-control" placeholder="Describe what you worked on this week..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Submit Log</button>
            </form>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
