<?php
// admin/live_results.php
define('ACCESS_ALLOWED', true);
require_once '../config/database.php';

if(!isset($_SESSION['admin_logged'])) {
    header("Location: login.php");
    exit();
}

// IP restriction check
$allowed_ips = ['127.0.0.1', '::1']; // Add EC Chairperson IPs
if(!in_array($_SERVER['REMOTE_ADDR'], $allowed_ips)) {
    die("Access restricted to Electoral Commission only.");
}

// Get real-time results
$stmt = $pdo->query("
    SELECT 
        c.id as category_id,
        c.category_name,
        cand.id as candidate_id,
        cand.full_name,
        cand.photo,
        COUNT(v.id) as vote_count
    FROM categories c
    CROSS JOIN candidates cand
    LEFT JOIN votes v ON v.candidate_id = cand.id AND v.category_id = c.id
    WHERE cand.category_id = c.id AND c.is_active = 1
    GROUP BY c.id, cand.id
    ORDER BY c.sort_order, vote_count DESC
");
$results = $stmt->fetchAll();

// Group by category
$categories_results = [];
foreach($results as $row) {
    if(!isset($categories_results[$row['category_id']])) {
        $categories_results[$row['category_id']] = [
            'name' => $row['category_name'],
            'candidates' => []
        ];
    }
    $categories_results[$row['category_id']]['candidates'][] = $row;
}

// Get overall stats
$stmt = $pdo->query("SELECT COUNT(*) FROM students WHERE has_voted = 1");
$total_voted = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM students WHERE is_active = 1");
$total_voters = $stmt->fetchColumn();

$turnout = $total_voters > 0 ? round(($total_voted / $total_voters) * 100, 1) : 0;

// Auto-refresh interval (10 seconds)
header('Refresh: 10');
$pageTitle = 'Live Results';
$pageSubtitle = 'Real-time vote distribution for authorized staff only.';
$topbarActions = '<span class="badge-soft">Auto-refresh every 10s</span>';
include 'partials/admin_header.php';
?>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-label">Turnout</div>
        <div class="stat-number"><?php echo $turnout; ?>%</div>
        <div class="stat-meta"><?php echo number_format($total_voted); ?> / <?php echo number_format($total_voters); ?> voted</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Voters</div>
        <div class="stat-number"><?php echo number_format($total_voters); ?></div>
        <div class="stat-meta">Eligible students</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Votes Cast</div>
        <div class="stat-number"><?php echo number_format($total_voted); ?></div>
        <div class="stat-meta">Submitted ballots</div>
    </div>
</div>

<?php foreach($categories_results as $cat_id => $category): ?>
    <div class="result-card">
        <div class="card-header-row">
            <div>
                <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                <p>Live standings and vote distribution.</p>
            </div>
            <span class="badge-soft">Category</span>
        </div>
        <?php
        $total_votes_in_cat = array_sum(array_column($category['candidates'], 'vote_count'));
        foreach($category['candidates'] as $index => $candidate):
            $percentage = $total_votes_in_cat > 0 ? round(($candidate['vote_count'] / $total_votes_in_cat) * 100, 1) : 0;
            $is_leading = ($index == 0 && $candidate['vote_count'] > 0);
        ?>
            <div class="candidate-row">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong><?php echo htmlspecialchars($candidate['full_name']); ?></strong>
                        <?php if($is_leading): ?>
                            <span class="lead-badge">LEADING</span>
                        <?php endif; ?>
                    </div>
                    <div class="vote-count"><?php echo number_format($candidate['vote_count']); ?> votes</div>
                </div>
                <div class="progress mt-2" style="height: 20px;">
                    <div class="progress-bar" style="width: <?php echo $percentage; ?>%; background: #0f766e;">
                        <?php echo $percentage; ?>%
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endforeach; ?>

<div class="result-card">
    <div class="card-header-row">
        <div>
            <h3>Recent Voting Activity</h3>
            <p>Most recent ballot submissions.</p>
        </div>
        <span class="badge-soft">Latest</span>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Time</th>
                <th>Category</th>
                <th>Candidate</th>
                <th>Voter (Anon)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $pdo->query("
                SELECT v.voted_at, cat.category_name, c.full_name as candidate_name, LEFT(v.voting_token, 8) as anon_id
                FROM votes v
                JOIN categories cat ON v.category_id = cat.id
                JOIN candidates c ON v.candidate_id = c.id
                ORDER BY v.voted_at DESC
                LIMIT 20
            ");
            while($row = $stmt->fetch()):
            ?>
            <tr>
                <td><?php echo date('H:i:s', strtotime($row['voted_at'])); ?></td>
                <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                <td><?php echo htmlspecialchars($row['candidate_name']); ?></td>
                <td><code><?php echo $row['anon_id']; ?>...</code></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script>
    let seconds = 10;
    setInterval(() => {
        seconds--;
        if (seconds <= 0) {
            location.reload();
        }
    }, 1000);
</script>

<?php include 'partials/admin_footer.php'; ?>
