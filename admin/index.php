
<?php
define('ACCESS_ALLOWED', true);
require_once '../config/database.php';

// Simple admin authentication (use proper auth in production)
if(!isset($_SESSION['admin_logged'])) {
    header("Location: login.php");
    exit();
}

// Get statistics
$stats = [];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM students WHERE is_active = 1");
$stats['total_voters'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) as voted FROM students WHERE has_voted = 1");
$stats['voted'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(DISTINCT voter_registration) as unique_voters FROM votes");
$stats['unique_voters'] = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) as total_votes FROM votes");
$stats['total_votes_cast'] = $stmt->fetchColumn();

$turnout = ($stats['total_voters'] > 0) ? round(($stats['voted'] / $stats['total_voters']) * 100, 2) : 0;
$pageTitle = 'Election Dashboard';
$pageSubtitle = 'Monitor and manage the voting process in real time.';
$pageHeadExtras = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
$topbarActions = '<a class="btn btn-accent btn-sm" href="close_election.php">Close Election</a>';
include 'partials/admin_header.php';
?>
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-label">Total Voters</div>
        <div class="stat-number"><?php echo number_format($stats['total_voters']); ?></div>
        <div class="stat-meta">Active student roll</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Votes Cast</div>
        <div class="stat-number"><?php echo number_format($stats['voted']); ?></div>
        <div class="stat-meta">Ballots submitted</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Turnout</div>
        <div class="stat-number"><?php echo $turnout; ?>%</div>
        <div class="stat-meta">Participation rate</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Total Votes</div>
        <div class="stat-number"><?php echo number_format($stats['total_votes_cast']); ?></div>
        <div class="stat-meta">All ballots recorded</div>
    </div>
</div>

<div class="content-grid">
    <div class="card-panel">
        <div class="card-header-row">
            <div>
                <h3>Live Results Snapshot</h3>
                <p>Updated vote distribution across categories.</p>
            </div>
            <span class="badge-soft">Auto updates</span>
        </div>
        <div style="min-height: 260px;">
            <canvas id="resultsChart"></canvas>
        </div>
    </div>
    <div class="card-panel">
        <div class="card-header-row">
            <div>
                <h3>Recent Voting Activity</h3>
                <p>Latest submissions captured in the system.</p>
            </div>
            <a class="btn btn-ghost btn-sm" href="audit.php">View audit</a>
        </div>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Category</th>
                    <th>Vote ID</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->query("SELECT v.*, c.category_name FROM votes v JOIN categories c ON v.category_id = c.id ORDER BY v.voted_at DESC LIMIT 10");
                while ($row = $stmt->fetch()):
                ?>
                <tr>
                    <td><?php echo date('H:i:s', strtotime($row['voted_at'])); ?></td>
                    <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                    <td>#<?php echo $row['id']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    fetch('ajax_results.php')
        .then(response => response.json())
        .then(data => {
            const ctx = document.getElementById('resultsChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.categories,
                    datasets: [{
                        label: 'Votes',
                        data: data.votes,
                        backgroundColor: '#0f766e'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        });
</script>
<?php include 'partials/admin_footer.php'; ?>
