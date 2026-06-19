
<?php
define('ACCESS_ALLOWED', true);
require_once '../config/database.php';

if (!isset($_SESSION['admin_logged'])) {
    header("Location: login.php");
    exit();
}

$pageTitle = 'Results';
$pageSubtitle = 'Review tallies, publish outcomes, and export summaries.';
$topbarActions = '<a class="btn btn-primary btn-sm" href="live_results.php">View live results</a>';
include 'partials/admin_header.php';
?>

<?php
$stmt = $pdo->query("
    SELECT c.id as category_id, c.category_name, c.sort_order, cand.id as candidate_id, cand.full_name,
           COUNT(v.id) as vote_count
    FROM categories c
    JOIN candidates cand ON cand.category_id = c.id
    LEFT JOIN votes v ON v.candidate_id = cand.id AND v.category_id = c.id
    WHERE c.is_active = 1
    GROUP BY c.id, c.category_name, c.sort_order, cand.id, cand.full_name
    ORDER BY c.sort_order, vote_count DESC
");
$rows = $stmt->fetchAll();
$results = [];
foreach ($rows as $row) {
    if (!isset($results[$row['category_id']])) {
        $results[$row['category_id']] = [
            'category_name' => $row['category_name'],
            'candidates' => []
        ];
    }
    $results[$row['category_id']]['candidates'][] = $row;
}
?>

<?php if (empty($results)): ?>
    <div class="card-panel">
        <div class="card-header-row">
            <div>
                <h3>Election Results Center</h3>
                <p>Finalize results and verify audit integrity before publishing.</p>
            </div>
            <span class="badge-soft">Results hub</span>
        </div>
        <div class="empty-state">
            <div class="empty-icon">🏆</div>
            <h4>No results data yet.</h4>
            <p>Once votes are recorded, results will be displayed here.</p>
        </div>
    </div>
<?php else: ?>
    <?php foreach ($results as $category): ?>
        <?php
        $totalVotes = array_sum(array_column($category['candidates'], 'vote_count'));
        ?>
        <div class="card-panel">
            <div class="card-header-row">
                <div>
                    <h3><?php echo htmlspecialchars($category['category_name']); ?></h3>
                    <p>Vote breakdown by candidate.</p>
                </div>
                <span class="badge-soft"><?php echo number_format($totalVotes); ?> votes</span>
            </div>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Candidate</th>
                        <th>Votes</th>
                        <th>Share</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($category['candidates'] as $candidate): ?>
                        <?php
                        $percentage = $totalVotes > 0 ? round(($candidate['vote_count'] / $totalVotes) * 100, 1) : 0;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($candidate['full_name']); ?></td>
                            <td><?php echo number_format($candidate['vote_count']); ?></td>
                            <td><?php echo $percentage; ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include 'partials/admin_footer.php'; ?>

