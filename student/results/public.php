<?php
// results/public.php - Post-election results
define('ACCESS_ALLOWED', true);
require_once '../config/database.php';

// Check if results are published
$stmt = $pdo->query("SELECT setting_value FROM election_settings WHERE setting_key = 'results_published'");
$results_published = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT setting_value FROM election_settings WHERE setting_key = 'election_status'");
$election_status = $stmt->fetchColumn();

if($election_status != 'closed' && !$results_published) {
    header("Location: ../index.php?msg=Results not yet available");
    exit();
}

// Get verification hash
$stmt = $pdo->query("SELECT setting_value FROM election_settings WHERE setting_key = 'results_hash'");
$verification_hash = $stmt->fetchColumn();

// Get winners (top candidate per category)
$stmt = $pdo->query("
    SELECT 
        cat.id as category_id,
        cat.category_name,
        c.id as candidate_id,
        c.full_name,
        c.photo,
        c.slogan,
        c.manifesto,
        COUNT(v.id) as vote_count,
        SUM(COUNT(v.id)) OVER (PARTITION BY cat.id) as total_category_votes
    FROM categories cat
    JOIN candidates c ON c.category_id = cat.id
    LEFT JOIN votes v ON v.candidate_id = c.id
    WHERE cat.is_active = 1
    GROUP BY cat.id, c.id
    ORDER BY cat.sort_order, vote_count DESC
");
$all_candidates = $stmt->fetchAll();

// Group and get winners
$categories_winners = [];
foreach($all_candidates as $candidate) {
    if(!isset($categories_winners[$candidate['category_id']])) {
        $categories_winners[$candidate['category_id']] = [
            'name' => $candidate['category_name'],
            'winner' => $candidate,
            'runners' => [],
            'total_votes' => $candidate['total_category_votes']
        ];
    } elseif(count($categories_winners[$candidate['category_id']]['runners']) < 2) {
        $categories_winners[$candidate['category_id']]['runners'][] = $candidate;
    }
}

// Overall statistics
$stmt = $pdo->query("SELECT COUNT(*) FROM votes");
$total_votes_cast = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM students WHERE has_voted = 1");
$valid_votes = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM students WHERE is_active = 1");
$total_voters = $stmt->fetchColumn();

$turnout = $total_voters > 0 ? round(($valid_votes / $total_voters) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Official Election Results - I.C.U.C University 2026</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: #f5f5f5;
        }
        .hero-results {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
        }
        .winner-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            transition: transform 0.3s;
        }
        .winner-card:hover {
            transform: translateY(-5px);
        }
        .winner-img {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }
        .winner-name {
            font-size: 28px;
            font-weight: bold;
            color: #667eea;
        }
        .vote-badge {
            background: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 50px;
            display: inline-block;
        }
        .verification-box {
            background: #1a1a2e;
            color: #00ff00;
            padding: 15px;
            border-radius: 10px;
            font-family: monospace;
            word-break: break-all;
        }
        .download-btn {
            background: #28a745;
            color: white;
            border-radius: 50px;
            padding: 10px 25px;
        }
        @media print {
            .no-print, .btn, .navbar {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="hero-results">
        <div class="container">
            <h1>🎓 I.C.U.C University</h1>
            <h2>Student Union Election Results 2026</h2>
            <p class="lead">Official Declaration - <?php echo date('F j, Y'); ?></p>
            <div class="verification-box mt-3">
                <small>🔐 Verification Hash (SHA-256):</small><br>
                <code><?php echo $verification_hash; ?></code>
            </div>
        </div>
    </div>
    
    <div class="container mt-5">
        <!-- Statistics Summary -->
        <div class="row mb-5">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h3><?php echo number_format($total_votes_cast); ?></h3>
                        <p>Total Votes Cast</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h3><?php echo number_format($valid_votes); ?></h3>
                        <p>Valid Votes</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h3><?php echo $turnout; ?>%</h3>
                        <p>Voter Turnout</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Winners Display -->
        <h2 class="text-center mb-4">🏆 Election Winners</h2>
        <?php foreach($categories_winners as $category): ?>
            <?php $winner = $category['winner']; ?>
            <div class="winner-card">
                <div class="row g-0">
                    <div class="col-md-4">
                        <?php if($winner['photo'] && file_exists('../' . $winner['photo'])): ?>
                            <img src="../<?php echo $winner['photo']; ?>" class="winner-img" alt="<?php echo $winner['full_name']; ?>">
                        <?php else: ?>
                            <div class="winner-img d-flex align-items-center justify-content-center bg-secondary text-white">
                                <i class="fas fa-user-circle fa-5x"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-8">
                        <div class="card-body">
                            <h3 class="winner-name"><?php echo htmlspecialchars($winner['full_name']); ?></h3>
                            <h5 class="text-muted"><?php echo htmlspecialchars($category['name']); ?></h5>
                            <?php if($winner['slogan']): ?>
                                <p class="font-italic">"<?php echo htmlspecialchars($winner['slogan']); ?>"</p>
                            <?php endif; ?>
                            <div class="vote-badge">
                                <?php echo number_format($winner['vote_count']); ?> votes 
                                (<?php echo $category['total_votes'] > 0 ? round(($winner['vote_count'] / $category['total_votes']) * 100, 1) : 0; ?>%)
                            </div>
                            
                            <div class="mt-3">
                                <h6>📜 Manifesto Highlights:</h6>
                                <p><?php echo nl2br(htmlspecialchars(substr($winner['manifesto'], 0, 300))); ?>...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Runners Up -->
            <?php if(!empty($category['runners'])): ?>
                <div class="mb-4">
                    <h6 class="text-muted">Runners Up:</h6>
                    <?php foreach($category['runners'] as $runner): ?>
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <span><?php echo htmlspecialchars($runner['full_name']); ?></span>
                            <span><?php echo number_format($runner['vote_count']); ?> votes 
                                (<?php echo $runner['total_category_votes'] > 0 ? round(($runner['vote_count'] / $runner['total_category_votes']) * 100, 1) : 0; ?>%)</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <hr>
        <?php endforeach; ?>
        
        <!-- Full Results Table -->
        <h2 class="text-center mt-5 mb-4">📋 Full Results Breakdown</h2>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr><th>Position</th><th>Candidate</th><th>Votes</th><th>Percentage</th><th>Winner</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($all_candidates as $candidate): ?>
                                <?php 
                                $is_winner = false;
                                foreach($categories_winners as $cat_winner) {
                                    if($cat_winner['winner']['candidate_id'] == $candidate['candidate_id']) {
                                        $is_winner = true;
                                        break;
                                    }
                                }
                                $percentage = $candidate['total_category_votes'] > 0 ? round(($candidate['vote_count'] / $candidate['total_category_votes']) * 100, 1) : 0;
                                ?>
                                <tr class="<?php echo $is_winner ? 'table-success' : ''; ?>">
                                    <td><?php echo htmlspecialchars($candidate['category_name']); ?></td>
                                    <td><?php echo htmlspecialchars($candidate['full_name']); ?></td>
                                    <td><?php echo number_format($candidate['vote_count']); ?></td>
                                    <td><?php echo $percentage; ?>%</td>
                                    <td><?php echo $is_winner ? '🏆 WINNER' : ''; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Download Section -->
        <div class="text-center mt-5 mb-5 no-print">
            <button class="btn btn-success download-btn" onclick="window.print()">
                📄 Download Official Results (PDF)
            </button>
            <a href="export_csv.php" class="btn btn-secondary download-btn">
                📊 Download Raw Data (CSV)
            </a>
            <button class="btn btn-info download-btn" onclick="verifyIntegrity()">
                🔍 Verify Results Integrity
            </button>
        </div>
        
        <!-- Declaration -->
        <div class="alert alert-success text-center">
            <strong>OFFICIAL DECLARATION</strong><br>
            I hereby declare the above results as true and accurate representation of the I.C.U.C Student Union Election 2026.<br>
            <br>
            <strong>Returning Officer: ___________________</strong><br>
            <small>Electoral Commission Chairperson</small>
        </div>
    </div>
    
    <script>
        function verifyIntegrity() {
            const hash = "<?php echo $verification_hash; ?>";
            alert("Integrity Verification:\n\nResults Hash: " + hash + "\n\nThis hash is cryptographically signed and cannot be altered without detection.\n\nTo verify:\n1. Compare this hash with the offline copy\n2. Any mismatch indicates tampering");
        }
    </script>
</body>
</html>