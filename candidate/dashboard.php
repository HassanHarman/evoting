<?php
// candidate/dashboard.php
define('ACCESS_ALLOWED', true);
require_once '../config/database.php';

if(!isset($_SESSION['candidate_id'])) {
    header("Location: login.php");
    exit();
}

$candidate_id = $_SESSION['candidate_id'];

// Get candidate details
$stmt = $pdo->prepare("
    SELECT c.*, cat.category_name, cat.id as category_id 
    FROM candidates c 
    JOIN categories cat ON c.category_id = cat.id 
    WHERE c.id = ?
");
$stmt->execute([$candidate_id]);
$candidate = $stmt->fetch();

// Get vote statistics
$stmt = $pdo->prepare("SELECT COUNT(*) as my_votes FROM votes WHERE candidate_id = ?");
$stmt->execute([$candidate_id]);
$my_votes = $stmt->fetchColumn();

// Get total votes in category
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM votes WHERE category_id = ?");
$stmt->execute([$candidate['category_id']]);
$total_category_votes = $stmt->fetchColumn();

$percentage = $total_category_votes > 0 ? round(($my_votes / $total_category_votes) * 100, 2) : 0;

// Get opponents
$stmt = $pdo->prepare("
    SELECT c.id, c.full_name, COUNT(v.id) as votes 
    FROM candidates c 
    LEFT JOIN votes v ON v.candidate_id = c.id 
    WHERE c.category_id = ? AND c.id != ? 
    GROUP BY c.id 
    ORDER BY votes DESC
");
$stmt->execute([$candidate['category_id'], $candidate_id]);
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

// Get hourly trend (last 24 hours)
$stmt = $pdo->prepare("
    SELECT HOUR(voted_at) as hour, COUNT(*) as votes 
    FROM votes 
    WHERE candidate_id = ? AND voted_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
    GROUP BY HOUR(voted_at)
    ORDER BY hour
");
$stmt->execute([$candidate_id]);
$hourly_trend = $stmt->fetchAll();

// Get agents
$stmt = $pdo->prepare("SELECT * FROM campaign_agents WHERE candidate_id = ? AND is_active = 1");
$stmt->execute([$candidate_id]);
$agents = $stmt->fetchAll();

// Check if live results are enabled
$stmt = $pdo->query("SELECT setting_value FROM election_settings WHERE setting_key = 'display_live_results'");
$live_results_enabled = $stmt->fetchColumn() == '1';

// Get election status
$stmt = $pdo->query("SELECT setting_value FROM election_settings WHERE setting_key = 'election_status'");
$election_status = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Candidate Dashboard - <?php echo htmlspecialchars($candidate['full_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: #f5f5f5;
        }
        .navbar-candidate {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
        }
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .vote-number {
            font-size: 48px;
            font-weight: bold;
            color: #ee5a24;
        }
        .progress-bar-custom {
            height: 30px;
            border-radius: 15px;
        }
        .agent-card {
            background: #f8f9fa;
            border-left: 4px solid #28a745;
            padding: 10px;
            margin-bottom: 10px;
        }
        .rank-badge {
            position: absolute;
            top: -10px;
            right: -10px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
        }
        .rank-1 { background: #ffd700; color: #000; }
        .rank-2 { background: #c0c0c0; }
        .rank-3 { background: #cd7f32; }
    </style>
</head>
<body>
    <nav class="navbar navbar-candidate navbar-dark">
        <div class="container">
            <span class="navbar-brand">
                <i class="fas fa-user-tie"></i> Candidate Portal - <?php echo htmlspecialchars($candidate['full_name']); ?>
            </span>
            <div>
                <span class="text-white me-3">
                    <i class="fas fa-tag"></i> <?php echo htmlspecialchars($candidate['category_name']); ?>
                </span>
                <a href="../logout.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <?php if($election_status == 'closed'): ?>
            <div class="alert alert-secondary">
                <i class="fas fa-flag-checkered"></i> Election has ended. Final results have been published.
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-8">
                <!-- Main Stats -->
                <div class="stats-card text-center">
                    <h4>Your Performance</h4>
                    <div class="vote-number"><?php echo number_format($my_votes); ?></div>
                    <p>Total Votes Received</p>
                    <div class="progress progress-bar-custom">
                        <div class="progress-bar bg-success" style="width: <?php echo $percentage; ?>%">
                            <?php echo $percentage; ?>% of Category Votes
                        </div>
                    </div>
                    <p class="mt-2 text-muted">Out of <?php echo number_format($total_category_votes); ?> total votes in this category</p>
                </div>
                
                <!-- Opponents Comparison -->
                <div class="stats-card">
                    <h5><i class="fas fa-chart-line"></i> Position Standings</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr><th>Rank</th><th>Candidate</th><th>Votes</th><th>Percentage</th><th>Bar</th></tr>
                            </thead>
                            <tbody>
                                <?php 
                                $all_candidates = array_merge([['full_name' => $candidate['full_name'], 'votes' => $my_votes]], $opponents);
                                usort($all_candidates, function($a, $b) {
                                    return $b['votes'] - $a['votes'];
                                });
                                $rank = 1;
                                foreach($all_candidates as $opp):
                                    $opp_percent = $total_category_votes > 0 ? round(($opp['votes'] / $total_category_votes) * 100, 1) : 0;
                                    $is_me = ($opp['full_name'] == $candidate['full_name']);
                                ?>
                                <tr class="<?php echo $is_me ? 'table-warning' : ''; ?>">
                                    <td>
                                        <?php if($rank == 1): ?>🏆
                                        <?php elseif($rank == 2): ?>🥈
                                        <?php elseif($rank == 3): ?>🥉
                                        <?php else: echo $rank; endif; ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($opp['full_name']); ?>
                                        <?php if($is_me): ?> <span class="badge bg-warning">You</span><?php endif; ?>
                                    </td>
                                    <td><strong><?php echo number_format($opp['votes']); ?></strong></td>
                                    <td><?php echo $opp_percent; ?>%</td>
                                    <td width="30%">
                                        <div class="progress">
                                            <div class="progress-bar bg-info" style="width: <?php echo ($opp['votes'] / max($all_candidates[0]['votes'], 1)) * 100; ?>%"></div>
                                        </div>
                                    </td>
                                </tr>
                                <?php $rank++; endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Faculty Breakdown -->
                <div class="stats-card">
                    <h5><i class="fas fa-university"></i> Support by Faculty</h5>
                    <canvas id="facultyChart" height="200"></canvas>
                </div>
                
                <!-- Hourly Trend (if enabled) -->
                <?php if($live_results_enabled && $election_status == 'active'): ?>
                <div class="stats-card">
                    <h5><i class="fas fa-chart-line"></i> Hourly Vote Trend (Last 24 Hours)</h5>
                    <canvas id="hourlyChart" height="200"></canvas>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="col-md-4">
                <!-- Campaign Agents Management -->
                <div class="stats-card">
                    <h5><i class="fas fa-users"></i> Campaign Agents</h5>
                    <p class="small text-muted">Maximum 3 agents allowed</p>
                    
                    <?php foreach($agents as $agent): ?>
                        <div class="agent-card">
                            <strong><?php echo htmlspecialchars($agent['full_name']); ?></strong><br>
                            <small><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($agent['email']); ?></small><br>
                            <small><i class="fas fa-phone"></i> <?php echo htmlspecialchars($agent['phone']); ?></small><br>
                            <small class="text-muted">Agent Code: <?php echo $agent['agent_code']; ?></small>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if(count($agents) < 3): ?>
                        <button class="btn btn-primary w-100 mt-2" data-bs-toggle="modal" data-bs-target="#addAgentModal">
                            <i class="fas fa-plus"></i> Add Campaign Agent
                        </button>
                    <?php endif; ?>
                </div>
                
                <!-- Election Info -->
                <div class="stats-card">
                    <h5><i class="fas fa-info-circle"></i> Election Information</h5>
                    <hr>
                    <p><strong>Total Voters:</strong> <?php 
                        $stmt = $pdo->query("SELECT COUNT(*) FROM students WHERE is_active = 1");
                        echo number_format($stmt->fetchColumn());
                    ?></p>
                    <p><strong>Voter Turnout:</strong> <?php 
                        $stmt = $pdo->query("SELECT ROUND((COUNT(CASE WHEN has_voted=1 THEN 1 END) / COUNT(*)) * 100, 1) FROM students WHERE is_active=1");
                        echo $stmt->fetchColumn() . '%';
                    ?></p>
                    <hr>
                    <div class="alert alert-info small">
                        <i class="fas fa-eye-slash"></i> <strong>Privacy Note:</strong> Individual voter identities are hidden. Only aggregate statistics are shown.
                    </div>
                </div>
                
                <!-- Download Report -->
                <div class="stats-card text-center">
                    <button class="btn btn-secondary w-100" onclick="downloadReport()">
                        <i class="fas fa-download"></i> Download Performance Report
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Agent Modal -->
    <div class="modal fade" id="addAgentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Campaign Agent</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="add_agent.php">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Full Name</label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Email Address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Phone Number</label>
                            <input type="text" name="phone" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="add_agent" class="btn btn-primary">Add Agent</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Faculty breakdown chart
        const facultyCtx = document.getElementById('facultyChart').getContext('2d');
        new Chart(facultyCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($faculty_breakdown, 'faculty')); ?>,
                datasets: [{
                    label: 'Votes Received',
                    data: <?php echo json_encode(array_column($faculty_breakdown, 'votes')); ?>,
                    backgroundColor: '#ee5a24'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
        
        <?php if($live_results_enabled && $election_status == 'active'): ?>
        // Hourly trend chart
        const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
        new Chart(hourlyCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($hourly_trend, 'hour')); ?>,
                datasets: [{
                    label: 'Votes per Hour',
                    data: <?php echo json_encode(array_column($hourly_trend, 'votes')); ?>,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40,167,69,0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
        <?php endif; ?>
        
        function downloadReport() {
            alert("Performance report will be generated. This would include:\n- Total votes\n- Faculty breakdown\n- Hourly trends\n- Comparison with opponents");
        }
    </script>
</body>
</html>