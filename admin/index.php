
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - I.C.U.C. Voting System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            display: block;
        }
        .sidebar a:hover {
            background: rgba(255,255,255,0.1);
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar p-3">
                <h4 class="text-white mb-4">I.C.U.C. Admin</h4>
                <a href="index.php">📊 Dashboard</a>
                <a href="candidates.php">👥 Candidates</a>
                <a href="categories.php">📋 Categories</a>
                <a href="students.php">👨‍🎓 Students</a>
                <a href="results.php">🏆 Results</a>
                <a href="audit.php">🔍 Audit Log</a>
                <a href="settings.php">⚙️ Settings</a>
                <a href="../logout.php">🚪 Logout</a>
            </div>
            
            <div class="col-md-10 p-4">
                <h2>Election Dashboard</h2>
                <p>Monitor and manage the voting process</p>
                
                <div class="row">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h6>Total Voters</h6>
                            <div class="stat-number"><?php echo $stats['total_voters']; ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h6>Votes Cast</h6>
                            <div class="stat-number"><?php echo $stats['voted']; ?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h6>Turnout</h6>
                            <div class="stat-number"><?php echo $turnout; ?>%</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <h6>Total Votes</h6>
                            <div class="stat-number"><?php echo $stats['total_votes_cast']; ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="stat-card">
                            <h5>Live Results</h5>
                            <canvas id="resultsChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="stat-card">
                            <h5>Recent Voting Activity</h5>
                            <table class="table table-sm">
                                <thead>
                                    <tr><th>Time</th><th>Category</th><th>Vote ID</th></tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $pdo->query("SELECT v.*, c.category_name FROM votes v JOIN categories c ON v.category_id = c.id ORDER BY v.voted_at DESC LIMIT 10");
                                    while($row = $stmt->fetch()):
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
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Load results for chart
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
                            backgroundColor: '#667eea'
                        }]
                    }
                });
            });
    </script>
</body>
</html>
