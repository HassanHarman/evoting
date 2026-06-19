
<?php
define('ACCESS_ALLOWED', true);
require_once '../config/database.php';

if (!isset($_SESSION['admin_logged'])) {
    header("Location: login.php");
    exit();
}

$pageTitle = 'Candidates';
$pageSubtitle = 'Manage candidate profiles, approvals, and manifesto updates.';
include 'partials/admin_header.php';
?>

<div class="card-panel">
    <div class="card-header-row">
        <div>
            <h3>Candidate Directory</h3>
            <p>Review nominations and campaign messaging in one place.</p>
        </div>
        <span class="badge-soft">Manage profiles</span>
    </div>
    <?php
    $stmt = $pdo->query("SELECT c.id, c.full_name, c.registration_number, c.email, c.party_affiliation, c.is_independent, c.is_active, c.created_at, cat.category_name, COUNT(v.id) as vote_count
        FROM candidates c
        JOIN categories cat ON c.category_id = cat.id
        LEFT JOIN votes v ON v.candidate_id = c.id
        GROUP BY c.id, c.full_name, c.registration_number, c.email, c.party_affiliation, c.is_independent, c.is_active, c.created_at, cat.category_name
        ORDER BY cat.sort_order, c.full_name
    ");
    $candidates = $stmt->fetchAll();
    ?>
    <?php if (empty($candidates)): ?>
        <div class="empty-state">
            <div class="empty-icon">👥</div>
            <h4>No candidates found.</h4>
            <p>Add candidate records to see them listed here.</p>
        </div>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Reg. No</th>
                    <th>Email</th>
                    <th>Affiliation</th>
                    <th>Votes</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($candidates as $candidate): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($candidate['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($candidate['category_name']); ?></td>
                        <td><?php echo htmlspecialchars($candidate['registration_number'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($candidate['email'] ?? ''); ?></td>
                        <td>
                            <?php
                            if ((int)$candidate['is_independent'] === 1) {
                                echo 'Independent';
                            } else {
                                echo htmlspecialchars($candidate['party_affiliation'] ?? '');
                            }
                            ?>
                        </td>
                        <td><?php echo number_format($candidate['vote_count']); ?></td>
                        <td>
                            <span class="badge-soft">
                                <?php echo ((int)$candidate['is_active'] === 1) ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include 'partials/admin_footer.php'; ?>

