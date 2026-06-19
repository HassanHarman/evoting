<?php
// student/dashboard.php
define('ACCESS_ALLOWED', true);
require_once '../config/database.php';

if(!isset($_SESSION['voter_reg'])) {
    header("Location: ../login.php");
    exit();
}

// Get total categories
$stmt = $pdo->query("SELECT COUNT(*) as total FROM categories WHERE is_active = 1");
$total_categories = $stmt->fetchColumn();

// Get voted categories for this student
$stmt = $pdo->prepare("SELECT COUNT(DISTINCT category_id) as voted FROM votes WHERE voter_registration = ?");
$stmt->execute([$_SESSION['voter_reg']]);
$voted_categories = $stmt->fetchColumn();

$progress = ($total_categories > 0) ? round(($voted_categories / $total_categories) * 100) : 0;

// Check if already completed all voting
$stmt = $pdo->prepare("SELECT * FROM vote_completion WHERE voter_registration = ?");
$stmt->execute([$_SESSION['voter_reg']]);
$completed = $stmt->fetch();

// Get election status
$stmt = $pdo->query("SELECT setting_value FROM election_settings WHERE setting_key = 'election_status'");
$election_status = $stmt->fetchColumn();

// Get turnout statistics (for transparency)
$stmt = $pdo->query("SELECT COUNT(*) FROM students WHERE is_active = 1");
$total_voters = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM students WHERE has_voted = 1");
$total_voted = $stmt->fetchColumn();

$turnout = ($total_voters > 0) ? round(($total_voted / $total_voters) * 100, 1) : 0;

// Get faculty turnout
$stmt = $pdo->prepare("SELECT faculty, COUNT(*) as total, SUM(has_voted) as voted FROM students WHERE is_active = 1 GROUP BY faculty ORDER BY voted DESC LIMIT 5");
$stmt->execute();
$faculty_turnout = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard - I.C.U.C Voting</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: #f5f5f5;
        }
        .navbar-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .progress-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .progress {
            height: 30px;
            border-radius: 15px;
        }
        .category-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.3s;
            cursor: pointer;
        }
        .category-card:hover {
            transform: translateX(5px);
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
        }
        .voted-badge {
            background: #28a745;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
        }
        .pending-badge {
            background: #ffc107;
            color: #000;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
        }
        .btn-vote {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 25px;
            padding: 8px 20px;
        }
        .receipt-card {
            background: #f8f9fa;
            border-left: 4px solid #28a745;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        .pulse {
            animation: pulse 2s infinite;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-custom navbar-dark">
        <div class="container">
            <span class="navbar-brand">🎓 I.C.U.C Electronic Voting System</span>
            <div class="text-white">
                <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['voter_name']); ?>
                <a href="../logout.php" class="text-white ms-3"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <!-- Welcome Section -->
        <div class="alert alert-success">
            <h4>Welcome, <?php echo htmlspecialchars($_SESSION['voter_name']); ?>!</h4>
            <p class="mb-0">Cast your vote wisely. Your voice matters!</p>
        </div>
        
        <?php if($election_status == 'closed'): ?>
            <div class="alert alert-danger">
                <i class="fas fa-flag-checkered"></i> Voting has ended. Results will be announced soon.
            </div>
        <?php elseif($completed): ?>
            <div class="alert alert-info receipt-card">
                <i class="fas fa-check-circle"></i> <strong>Thank you for voting!</strong><br>
                You have successfully completed voting in all categories.<br>
                Results will be published after the election closes.
                <hr>
                <small>Verification Hash: <?php echo substr($completed['verification_hash'], 0, 16); ?>...</small>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <!-- Left Column - Voting Progress -->
            <div class="col-md-7">
                <div class="progress-card">
                    <h5>📊 Your Voting Progress</h5>
                    <div class="progress mb-3">
                        <div class="progress-bar bg-success" style="width: <?php echo $progress; ?>%">
                            <?php echo $progress; ?>%
                        </div>
                    </div>
                    <p><?php echo $voted_categories; ?> out of <?php echo $total_categories; ?> positions voted</p>
                    
                    <?php if(!$completed && $election_status == 'active'): ?>
                        <a href="ballot.php" class="btn btn-vote pulse">
                            <i class="fas fa-vote-yea"></i> Continue Voting →
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- Categories List -->
                <div class="progress-card">
                    <h5>📋 Available Positions</h5>
                    <?php
                    $stmt = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order");
                    $categories = $stmt->fetchAll();
                    
                    foreach($categories as $category):
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM votes WHERE voter_registration = ? AND category_id = ?");
                        $stmt->execute([$_SESSION['voter_reg'], $category['id']]);
                        $hasVoted = $stmt->fetchColumn() > 0;
                    ?>
                    <div class="category-card" onclick="location.href='vote.php?category_id=<?php echo $category['id']; ?>'">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?php echo htmlspecialchars($category['category_name']); ?></strong>
                                <br><small class="text-muted"><?php echo htmlspecialchars($category['description']); ?></small>
                            </div>
                            <?php if($hasVoted): ?>
                                <span class="voted-badge"><i class="fas fa-check"></i> Voted</span>
                            <?php else: ?>
                                <span class="pending-badge"><i class="fas fa-clock"></i> Pending</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Right Column - Statistics -->
            <div class="col-md-5">
                <div class="progress-card">
                    <h5>📈 Live Voter Turnout</h5>
                    <div class="text-center">
                        <div class="display-4 text-primary"><?php echo $turnout; ?>%</div>
                        <p><?php echo $total_voted; ?> / <?php echo $total_voters; ?> students have voted</p>
                    </div>
                    <canvas id="turnoutChart" height="150"></canvas>
                </div>
                
                <div class="progress-card">
                    <h5>🏆 Faculty Turnout Leaderboard</h5>
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Faculty</th><th>Turnout</th><th>Progress</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($faculty_turnout as $faculty): 
                                $faculty_turnout_pct = $faculty['total'] > 0 ? round(($faculty['voted'] / $faculty['total']) * 100) : 0;
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($faculty['faculty']); ?></td>
                                <td><?php echo $faculty_turnout_pct; ?>%</td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-info" style="width: <?php echo $faculty_turnout_pct; ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="progress-card">
                    <h5>ℹ️ Important Information</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-shield-alt text-success"></i> Your vote is secret and secure</li>
                        <li><i class="fas fa-lock text-success"></i> One vote per position</li>
                        <li><i class="fas fa-chart-line text-success"></i> Results are verifiable via SHA-256</li>
                        <li><i class="fas fa-eye-slash text-warning"></i> Live results hidden to ensure fairness</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Turnout chart
        const ctx = document.getElementById('turnoutChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Voted (<?php echo $total_voted; ?>)', 'Not Voted (<?php echo $total_voters - $total_voted; ?>)'],
                datasets: [{
                    data: [<?php echo $total_voted; ?>, <?php echo $total_voters - $total_voted; ?>],
                    backgroundColor: ['#28a745', '#dc3545']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    </script>
</body>
</html>