<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';

requireRole('company');

$company_id = $_SESSION['user_id'];

// Fetch company's internships
$stmt = $pdo->prepare("SELECT * FROM internships WHERE company_id = ? ORDER BY created_at DESC");
$stmt->execute([$company_id]);
$internships = $stmt->fetchAll();

// Fetch applications for this company's internships
$stmt = $pdo->prepare("SELECT a.*, u.username as student_name, i.title as internship_title 
                       FROM applications a
                       JOIN internships i ON a.internship_id = i.id
                       JOIN users u ON a.student_id = u.id
                       WHERE i.company_id = ?
                       ORDER BY a.applied_at DESC");
$stmt->execute([$company_id]);
$applicants = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Dashboard - InternMatch</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="logo">InternMatch - <span style="font-size: 1.1rem; font-weight: normal; color: var(--text-muted);">Company Portal</span></div>
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
            
            <div class="card glass-panel" style="grid-column: span 2;">
                <div class="flex justify-between items-center mb-4">
                    <h3>My Posted Internships</h3>
                    <button class="btn btn-secondary" data-modal-target="true" data-action="add_internship">Post New Opportunity</button>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Location</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($internships as $job): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($job['title']) ?></strong></td>
                                    <td><?= htmlspecialchars($job['location']) ?></td>
                                    <td><?= htmlspecialchars(substr($job['description'], 0, 50)) ?>...</td>
                                    <td>
                                        <span class="badge badge-<?= $job['status'] ?>">
                                            <?= htmlspecialchars($job['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-primary" style="padding: 0.2rem 0.5rem; font-size: 0.8rem;" 
                                                data-modal-target="true" 
                                                data-action="edit_internship"
                                                data-id="<?= $job['id'] ?>"
                                                data-title="<?= htmlspecialchars($job['title']) ?>"
                                                data-desc="<?= htmlspecialchars($job['description']) ?>"
                                                data-loc="<?= htmlspecialchars($job['location']) ?>"
                                                >Edit</button>
                                        <a href="api/internship_actions.php?action=toggle_status&id=<?= $job['id'] ?>&status=<?= $job['status'] ?>" 
                                           class="btn btn-outline" style="padding: 0.2rem 0.5rem; font-size: 0.8rem;">
                                           <?= $job['status'] == 'open' ? 'Close' : 'Open' ?>
                                        </a>
                                        <a href="api/internship_actions.php?action=delete&id=<?= $job['id'] ?>" 
                                           class="btn btn-danger" style="padding: 0.2rem 0.5rem; font-size: 0.8rem;"
                                           onclick="return confirmAction('Delete this internship permanently?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Internship Applications -->
            <div class="card glass-panel" style="grid-column: span 2;">
                <h3>Applicants for your positions</h3>
                <div class="table-responsive">
                    <table id="applicantsTable">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Applied Position</th>
                                <th>Current Status</th>
                                <th>Applied Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($applicants as $app): ?>
                                <tr>
                                    <td><?= htmlspecialchars($app['student_name']) ?></td>
                                    <td><?= htmlspecialchars($app['internship_title']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $app['status'] ?>">
                                            <?= htmlspecialchars($app['status']) ?>
                                        </span>
                                        <?php if($app['status'] == 'approved'): ?>
                                            <span style="font-size: 0.8rem; color: var(--secondary);">✔ Coordinator Approved</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($app['applied_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal for Add/Edit Internship -->
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Post Internship</h3>
                <button class="modal-close" id="modalClose">&times;</button>
            </div>
            
            <form action="api/internship_actions.php" method="POST" id="internshipForm" class="validate-form">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="internshipIdInput"> <!-- For editing -->
                
                <div class="form-group">
                    <label>Job Title</label>
                    <input type="text" name="title" id="titleInput" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" id="locInput" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Description (Requirements & Responsibilities)</label>
                    <textarea name="description" id="descInput" class="form-control" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Save Opportunity</button>
            </form>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        // Slightly extend modal behavior here to handle the formAction change depending on button clicked
        const formActionInput = document.getElementById('formAction');
        document.querySelectorAll('[data-modal-target]').forEach(trigger => {
            trigger.addEventListener('click', () => {
                const action = trigger.getAttribute('data-action');
                if (action === 'add_internship') {
                    formActionInput.value = 'add';
                } else if (action === 'edit_internship') {
                    formActionInput.value = 'edit';
                }
            });
        });
    </script>
</body>
</html>
