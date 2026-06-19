
<<<<<<< HEAD
<?php
define('ACCESS_ALLOWED', true);
require_once '../config/database.php';

if (!isset($_SESSION['admin_logged'])) {
    header("Location: login.php");
    exit();
}

$pageTitle = 'Students';
$pageSubtitle = 'Maintain the voter roll and student status.';
include 'partials/admin_header.php';
?>

<div class="card-panel">
    <div class="card-header-row">
        <div>
            <h3>Voter Registry</h3>
            <p>Track eligibility, voting status, and active enrollments.</p>
        </div>
        <span class="badge-soft">Registry</span>
    </div>
    <?php
    $stmt = $pdo->query("SELECT id, registration_number, full_name, email, mobile_number, faculty, department, year_of_study, has_voted, is_active, created_at
        FROM students
        ORDER BY created_at DESC
    ");
    $students = $stmt->fetchAll();
    ?>
    <?php if (empty($students)): ?>
        <div class="empty-state">
            <div class="empty-icon">🎓</div>
            <h4>No students found.</h4>
            <p>Add students to see them listed here.</p>
        </div>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Reg. No</th>
                    <th>Name</th>
                    <th>Faculty</th>
                    <th>Department</th>
                    <th>Year</th>
                    <th>Mobile</th>
                    <th>Voted</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['registration_number']); ?></td>
                        <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($student['faculty'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($student['department'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($student['year_of_study'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($student['mobile_number']); ?></td>
                        <td><?php echo ((int)$student['has_voted'] === 1) ? 'Yes' : 'No'; ?></td>
                        <td>
                            <span class="badge-soft">
                                <?php echo ((int)$student['is_active'] === 1) ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include 'partials/admin_footer.php'; ?>

=======
>>>>>>> ba5da9f0dc6af51688bdd8bfdbfbd1ccf8e3c6f0
