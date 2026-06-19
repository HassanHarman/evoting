
<?php
define('ACCESS_ALLOWED', true);
require_once '../config/database.php';

if (!isset($_SESSION['admin_logged'])) {
    header("Location: login.php");
    exit();
}

$pageTitle = 'Categories';
$pageSubtitle = 'Configure positions, seat counts, and ballot order.';
include 'partials/admin_header.php';
?>

<div class="card-panel">
    <div class="card-header-row">
        <div>
            <h3>Election Categories</h3>
            <p>Define the positions and voting order for the ballot.</p>
        </div>
        <span class="badge-soft">Seat setup</span>
    </div>
    <?php
    $stmt = $pdo->query("SELECT cat.*, COUNT(c.id) as candidate_count
        FROM categories cat
        LEFT JOIN candidates c ON c.category_id = cat.id
        GROUP BY cat.id, cat.category_name, cat.description, cat.max_votes_per_voter, cat.sort_order, cat.is_active, cat.created_at
        ORDER BY cat.sort_order, cat.category_name
    ");
    $categories = $stmt->fetchAll();
    ?>
    <?php if (empty($categories)): ?>
        <div class="empty-state">
            <div class="empty-icon">📋</div>
            <h4>No categories found.</h4>
            <p>Once categories are added, they will show here.</p>
        </div>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Max Votes</th>
                    <th>Order</th>
                    <th>Candidates</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                        <td><?php echo htmlspecialchars($category['description'] ?? ''); ?></td>
                        <td><?php echo (int)$category['max_votes_per_voter']; ?></td>
                        <td><?php echo (int)$category['sort_order']; ?></td>
                        <td><?php echo number_format($category['candidate_count']); ?></td>
                        <td>
                            <span class="badge-soft">
                                <?php echo ((int)$category['is_active'] === 1) ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include 'partials/admin_footer.php'; ?>

