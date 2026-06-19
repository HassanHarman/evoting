<?php
// agent/dashboard.php - Read-only view for campaign agents
define('ACCESS_ALLOWED', true);
require_once '../config/database.php';

if(!isset($_SESSION['agent_id'])) {
    header("Location: login.php");
    exit();
}

$candidate_id = $_SESSION['candidate_id'];

// Get vote statistics (same as candidate but read-only)
$stmt = $pdo->prepare("SELECT COUNT(*) as my_votes FROM votes WHERE candidate_id = ?");
$stmt->execute([$candidate_id]);
$my_votes = $stmt->fetchColumn();

// Get total votes in category
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total FROM votes v 
    JOIN candidates c ON v.candidate_id = c.id 
    WHERE c.category_id = (SELECT category_id FROM candidates WHERE id = ?)
");
$stmt->execute([$candidate_id]);
$total_category_votes = $stmt->fetchColumn();

$percentage = $total_category_votes > 0 ? round(($my_votes / $total_category_votes) * 100, 2) : 0;

// Get opponents
$stmt = $pdo->prepare("
    SELECT c.full_name, COUNT(v.id) as votes 
    FROM candidates c 
    LEFT JOIN votes v ON v.candidate_id = c.id 
    WHERE c.category_id = (SELECT category_id FROM candidates WHERE id = ?) AND c.id != ? 
    GROUP BY c.id 
    ORDER BY votes DESC
");
$stmt->execute([$candidate_id, $candidate_id]);
$opponents = $stmt->fetchAll();

// Get faculty breakdown
$stmt = $pdo->prepare("
    SELECT s.faculty, COUNT(*) as votes 
    FROM votes v 
    JOIN students s ON v.voter_registration = s.registration_number 
    WHERE v.candidate_id = ? 
    GROUP BY s.faculty 
    ORDER BY votes DESC
");
$stmt->execute([$candidate_id]);
$faculty_breakdown = $stmt->fetchAll();

// Get election status
$stmt = $pdo->query("SELECT setting_value FROM election_settings WHERE setting_key = 'election_status'");
$election_status = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Agent Dashboard - <?php echo htmlspecialchars($_SESSION['agent_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: #f5f5f5;
        }
        .navbar-agent {
            background: linear-gradient(135deg, #20bf55 0%, #01baef 100%);
        }
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .readonly-badge {
            position: fixed;
            top: 70px;
            right: 20px;
            background: #ffc107;
            color: #000;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <div class="readonly-badge">
        <i class="fas fa-eye"></i> Read-Only Access | Agent: <?php echo htmlspecialchars($_SESSION['agent_name']); ?>
    </div>
    
    <nav class="navbar navbar-agent navbar-dark">
        <div class="container">
            <span class="navbar-brand">
                <i class="fas fa-user-check"></i> Agent Portal - Monitoring: <?php echo htmlspecialchars($_SESSION['candidate_name']); ?>
            </span>
            <a href="../logout.php" class="btn btn-outline-light btn-sm">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>
    
    <div class="container mt-4">
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> You have read-only access to monitor <strong><?php echo htmlspecialchars($_SESSION['candidate_name']); ?></strong>'s performance for <strong><?php echo htmlspecialchars($_SESSION['category_name']); ?></strong> position.
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="stats-card text-center">
                    <h4>Campaign Performance</h4>
                    <div class="display-1 text-success"><?php echo number_format($my_votes); ?></div>
                    <p>Total Votes Received</p>
                    <div class="progress">
                        <div class="progress-bar bg-success" style="width: <?php echo $percentage; ?>%">
                            <?php echo $percentage; ?>% of Category
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="stats-card">
                    <h5>Position Standings</h5>
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Candidate</th><th>Votes</th></tr>
                        </thead>
                        <tbody>
                            <tr class="table-warning">
                                <td><strong><?php echo htmlspecialchars($_SESSION['candidate_name']); ?></strong> (Your Candidate)</td>
                                <td><strong><?php echo number_format($my_votes); ?></strong></td>
                            </tr>
                            <?php foreach($opponents as $opponent): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($opponent['full_name']); ?></td>
                                <td><?php echo number_format($opponent['votes']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="stats-card">
                    <h5>Support by Faculty</h5>
                    <canvas id="facultyChart" height="200"></canvas>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="stats-card">
                    <h5>Election Statistics</h5>
                    <?php
                    $stmt = $pdo->query("SELECT COUNT(*) FROM students WHERE is_active = 1");
                    $total_voters = $stmt->fetchColumn();
                    
                    $stmt = $pdo->query("SELECT COUNT(*) FROM students WHERE has_voted = 1");
                    $voted = $stmt->fetchColumn();
                    
                    $turnout = $total_voters > 0 ? round(($voted / $total_voters) * 100, 1) : 0;
                    ?>
                    <p><strong>Total Voters:</strong> <?php echo number_format($total_voters); ?></p>
                    <p><strong>Votes Cast:</strong> <?php echo number_format($voted); ?></p>
                    <p><strong>Turnout:</strong> <?php echo $turnout; ?>%</p>
                    <hr>
                    <div class="alert alert-secondary small">
                        <i class="fas fa-chart-line"></i> Data updates every 30 seconds
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        const facultyCtx = document.getElementById('facultyChart').getContext('2d');
        new Chart(facultyCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_column($faculty_breakdown, 'faculty')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($faculty_breakdown, 'votes')); ?>,
                    backgroundColor: ['#20bf55', '#01baef', '#ff6b6b', '#ffd700', '#9b59b6']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
        
        // Auto-refresh every 30 seconds (read-only, safe to refresh)
        setTimeout(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>