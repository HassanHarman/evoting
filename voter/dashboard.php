<?php
define('ACCESS_ALLOWED', true);
require_once '../config/database.php';

if (!isset($_SESSION['voter_reg'])) {
    header("Location: ../index.php");
    exit();
}

// Check if voter has already completed voting
$stmt = $pdo->prepare("SELECT * FROM vote_completion WHERE voter_registration = ?");
$stmt->execute([$_SESSION['voter_reg']]);
if ($stmt->rowCount() > 0) {
    header("Location: vote_completed.php");
    exit();
}

// Get all active categories
$stmt = $pdo->prepare("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order");
$stmt->execute();
$categories = $stmt->fetchAll();

// Check which categories have been voted
$voted_categories = [];
$stmt = $pdo->prepare("SELECT category_id FROM votes WHERE voter_registration = ?");
$stmt->execute([$_SESSION['voter_reg']]);
$voted_categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voter Dashboard - I.C.U.C.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f5f5;
        }
        .navbar-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .category-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .category-card:hover {
            transform: translateY(-5px);
        }
        .voted-badge {
            background: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
        }
        .pending-badge {
            background: #ffc107;
            color: #000;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
        }
        .vote-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 25px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-custom navbar-dark">
        <div class="container">
            <span class="navbar-brand">🎓 I.C.U.C. Voting System</span>
            <div class="text-white">
                Welcome, <?php echo htmlspecialchars($_SESSION['voter_name']); ?> | 
                <a href="../logout.php" class="text-white">Logout</a>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-success">
                    <h4>🗳️ Your Voting Dashboard</h4>
                    <p>Cast your vote for each category below. You can only vote once per category.</p>
                </div>
            </div>
        </div>
        
        <div class="row">
            <?php foreach($categories as $category): ?>
                <?php $isVoted = in_array($category['id'], $voted_categories); ?>
                <div class="col-md-6">
                    <div class="category-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5><?php echo htmlspecialchars($category['category_name']); ?></h5>
                            <?php if($isVoted): ?>
                                <span class="voted-badge">✓ Voted</span>
                            <?php else: ?>
                                <span class="pending-badge">⏳ Pending</span>
                            <?php endif; ?>
                        </div>
                        <p class="text-muted small"><?php echo htmlspecialchars($category['description']); ?></p>
                        <?php if(!$isVoted): ?>
                            <a href="vote.php?category_id=<?php echo $category['id']; ?>" class="btn vote-btn">
                                Vote Now →
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary" disabled>Already Voted</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if(count($voted_categories) == count($categories)): ?>
            <div class="alert alert-success mt-3">
                🎉 You have completed voting in all categories! 
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
