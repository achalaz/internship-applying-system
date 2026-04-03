<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireRole('supervisor');

// Fetch all student progress logs (or we could limit to students assigned to this supervisor, assuming all for simplicity)
$stmt = $pdo->query("SELECT l.*, u.username as student_name 
                     FROM progress_logs l
                     JOIN users u ON l.student_id = u.id
                     ORDER BY l.created_at DESC");
$logs = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor Dashboard - InternMatch</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="logo">InternMatch - <span style="color: var(--text-muted); font-size: 1.1rem; font-weight: normal;">Academic Supervisor Portal</span></div>
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
                <h3>Student Progress Logs</h3>
                <div class="search-container" style="margin-bottom: 0;">
                    <input type="text" id="searchInput" class="search-input" placeholder="Search by student...">
                </div>
            </div>

            <div class="table-responsive">
                <table id="dataTable">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Week</th>
                            <th>Log Details</th>
                            <th>Submission Date</th>
                            <th>Status/Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($logs as $log): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($log['student_name']) ?></strong></td>
                                <td>Week <?= htmlspecialchars($log['week_no']) ?></td>
                                <td>
                                    <div style="max-height: 100px; overflow-y: auto; font-size: 0.9rem; padding-right: 10px;">
                                        <?= nl2br(htmlspecialchars($log['description'])) ?>
                                    </div>
                                </td>
                                <td><?= date('M d, Y', strtotime($log['created_at'])) ?></td>
                                <td>
                                    <?php if(empty($log['supervisor_feedback'])): ?>
                                        <button class="btn btn-secondary review-btn" style="padding: 0.2rem 0.6rem; font-size: 0.8rem;"
                                                data-modal-target="true" 
                                                data-action="add_feedback"
                                                data-logid="<?= $log['id'] ?>"
                                                data-student="<?= htmlspecialchars($log['student_name']) ?>"
                                                data-week="<?= htmlspecialchars($log['week_no']) ?>">
                                            Give Feedback
                                        </button>
                                    <?php else: ?>
                                        <span class="badge badge-approved" style="margin-bottom: 5px;">Reviewed</span>
                                        <div style="font-size: 0.8rem; color: var(--text-muted); background: rgba(0,0,0,0.2); padding: 5px; border-radius: 4px;">
                                            <?= nl2br(htmlspecialchars(substr($log['supervisor_feedback'], 0, 30))) ?>...
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if(empty($logs)): ?>
                            <tr><td colspan="5" class="text-center">No progress logs submitted yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal for Supervisor Feedback -->
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Evaluate Progress</h3>
                <button class="modal-close" id="modalClose">&times;</button>
            </div>
            
            <form action="api/log_actions.php" method="POST" class="validate-form">
                <input type="hidden" name="action" value="submit_feedback">
                <input type="hidden" name="log_id" id="logIdInput">
                <div class="form-group" style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1rem;">
                    Evaluating: <strong id="studentNameDisplay" style="color: var(--text-main);"></strong> (Week <span id="weekDisplay"></span>)
                </div>
                <div class="form-group">
                    <label>Supervisor Feedback & Evaluation</label>
                    <textarea name="supervisor_feedback" class="form-control" placeholder="Provide constructive feedback..." required></textarea>
                </div>
                <button type="submit" class="btn btn-secondary" style="width: 100%;">Submit Evaluation</button>
            </form>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        // Set dynamic data for modal
        document.querySelectorAll('.review-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('logIdInput').value = this.getAttribute('data-logid');
                document.getElementById('studentNameDisplay').textContent = this.getAttribute('data-student');
                document.getElementById('weekDisplay').textContent = this.getAttribute('data-week');
            });
        });
    </script>
</body>
</html>
