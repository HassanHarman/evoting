<?php
// student/ballot.php
define('ACCESS_ALLOWED', true);
require_once '../config/database.php';

if(!isset($_SESSION['voter_reg'])) {
    header("Location: ../login.php");
    exit();
}

// Check if election is active
$stmt = $pdo->query("SELECT setting_value FROM election_settings WHERE setting_key = 'election_status'");
$election_status = $stmt->fetchColumn();

if($election_status != 'active') {
    header("Location: dashboard.php?error=Election not active");
    exit();
}

// Get all categories with voting status
$stmt = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order");
$categories = $stmt->fetchAll();

$voted_categories = [];
$stmt = $pdo->prepare("SELECT category_id FROM votes WHERE voter_registration = ?");
$stmt->execute([$_SESSION['voter_reg']]);
$voted_categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

$total_categories = count($categories);
$completed_votes = count($voted_categories);
$remaining = $total_categories - $completed_votes;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ballot Paper - I.C.U.C Voting</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #f0f2f5;
        }
        .navbar-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .ballot-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        .ballot-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        .category-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }
        .btn-vote {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 25px;
            padding: 8px 25px;
        }
        .btn-vote:disabled {
            opacity: 0.6;
        }
        .progress-ring {
            position: relative;
        }
        .completion-badge {
            background: #28a745;
            color: white;
            padding: 8px 15px;
            border-radius: 25px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-custom navbar-dark">
        <div class="container">
            <span class="navbar-brand">🎓 I.C.U.C Electronic Voting System</span>
            <div class="text-white">
                <i class="fas fa-user-check"></i> <?php echo htmlspecialchars($_SESSION['voter_name']); ?>
                <a href="dashboard.php" class="text-white ms-3"><i class="fas fa-arrow-left"></i> Back</a>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="ballot-card text-center">
                    <h2><i class="fas fa-ballot"></i> Electronic Ballot Paper</h2>
                    <p class="text-muted">Review all positions below and cast your votes carefully</p>
                    
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="border rounded p-2">
                                <strong>Total Positions:</strong> <?php echo $total_categories; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-2">
                                <strong>Completed:</strong> <?php echo $completed_votes; ?>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-2">
                                <strong>Remaining:</strong> <?php echo $remaining; ?>
                            </div>
                        </div>
                    </div>
                    
                    <?php if($remaining == 0): ?>
                        <div class="alert alert-success mt-3">
                            <i class="fas fa-check-circle"></i> 🎉 Congratulations! You have voted in all positions!
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Categories Grid -->
        <div class="row">
            <?php foreach($categories as $category):
                $hasVoted = in_array($category['id'], $voted_categories);
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="ballot-card">
                    <div class="d-flex align-items-center mb-3">
                        <div class="category-icon me-3">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-0"><?php echo htmlspecialchars($category['category_name']); ?></h5>
                            <small class="text-muted"><?php echo htmlspecialchars($category['description']); ?></small>
                        </div>
                        <?php if($hasVoted): ?>
                            <span class="completion-badge"><i class="fas fa-check"></i> Voted</span>
                        <?php endif; ?>
                    </div>
                    
                    <?php if($hasVoted): ?>
                        <button class="btn btn-secondary w-100" disabled>
                            <i class="fas fa-check-circle"></i> Already Voted
                        </button>
                    <?php else: ?>
                        <a href="vote.php?category_id=<?php echo $category['id']; ?>" class="btn btn-vote w-100">
                            <i class="fas fa-vote-yea"></i> Vote Now →
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Warning Message -->
        <div class="alert alert-warning text-center mt-3">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Remember:</strong> Once you click "Confirm Vote", your choice is FINAL and cannot be changed!
        </div>
    </div>
</body>
</html>