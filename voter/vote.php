
<?php
define('ACCESS_ALLOWED', true);
require_once '../config/database.php';

if (!isset($_SESSION['voter_reg'])) {
    header("Location: ../index.php");
    exit();
}

$category_id = (int)$_GET['category_id'];

// Check if already voted in this category
$stmt = $pdo->prepare("SELECT * FROM votes WHERE voter_registration = ? AND category_id = ?");
$stmt->execute([$_SESSION['voter_reg'], $category_id]);
if($stmt->rowCount() > 0) {
    header("Location: dashboard.php");
    exit();
}

// Get category info
$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ? AND is_active = 1");
$stmt->execute([$category_id]);
$category = $stmt->fetch();
if(!$category) {
    header("Location: dashboard.php");
    exit();
}

// Get candidates for this category
$stmt = $pdo->prepare("SELECT * FROM candidates WHERE category_id = ? AND is_active = 1 ORDER BY full_name");
$stmt->execute([$category_id]);
$candidates = $stmt->fetchAll();

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['candidate_id'])) {
    $candidate_id = (int)$_POST['candidate_id'];
    
    // Double-check no vote exists
    $stmt = $pdo->prepare("SELECT * FROM votes WHERE voter_registration = ? AND category_id = ?");
    $stmt->execute([$_SESSION['voter_reg'], $category_id]);
    if($stmt->rowCount() == 0) {
        try {
            $pdo->beginTransaction();
            
            // Generate unique voting token
            $token = hash('sha256', $_SESSION['voter_reg'] . $candidate_id . $category_id . time() . rand());
            
            // Insert vote
            $stmt = $pdo->prepare("INSERT INTO votes (voter_registration, candidate_id, category_id, voting_token, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_SESSION['voter_reg'],
                $candidate_id,
                $category_id,
                $token,
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT']
            ]);
            
            // Check if all categories have been voted
            $stmt = $pdo->prepare("SELECT COUNT(DISTINCT category_id) as total_categories FROM categories WHERE is_active = 1");
            $stmt->execute();
            $total = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("SELECT COUNT(DISTINCT category_id) as voted FROM votes WHERE voter_registration = ?");
            $stmt->execute([$_SESSION['voter_reg']]);
            $voted_categories = $stmt->fetchColumn();
            
            if($voted_categories == $total) {
                // Mark as completed
                $completion_hash = hash('sha256', $_SESSION['voter_reg'] . time() . rand());
                $stmt = $pdo->prepare("INSERT INTO vote_completion (voter_registration, verification_hash) VALUES (?, ?)");
                $stmt->execute([$_SESSION['voter_reg'], $completion_hash]);
                
                // Update student record
                $stmt = $pdo->prepare("UPDATE students SET has_voted = 1 WHERE registration_number = ?");
                $stmt->execute([$_SESSION['voter_reg']]);
            }
            
            $pdo->commit();
            
            // Redirect to dashboard
            header("Location: dashboard.php?success=1");
            exit();
            
        } catch(Exception $e) {
            $pdo->rollBack();
            $error = "Error casting vote. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cast Vote - <?php echo htmlspecialchars($category['category_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f5f5;
        }
        .candidate-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        .candidate-card:hover {
            transform: scale(1.02);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        .candidate-card.selected {
            border-color: #667eea;
            background: #f0f0ff;
        }
        .radio-custom {
            width: 20px;
            height: 20px;
            margin-right: 10px;
        }
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 40px;
            font-size: 18px;
            border-radius: 50px;
        }
        .manifesto {
            font-size: 14px;
            color: #666;
            margin-top: 10px;
            padding: 10px;
            background: #f9f9f9;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4>🗳️ Vote for: <?php echo htmlspecialchars($category['category_name']); ?></h4>
                    </div>
                    <div class="card-body">
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" id="voteForm">
                            <p class="text-muted">Please select ONE candidate carefully. Your vote is final and cannot be changed.</p>
                            
                            <?php foreach($candidates as $candidate): ?>
                                <div class="candidate-card" onclick="selectCandidate(<?php echo $candidate['id']; ?>)">
                                    <div class="form-check">
                                        <input class="form-check-input radio-custom" type="radio" name="candidate_id" 
                                               id="candidate_<?php echo $candidate['id']; ?>" 
                                               value="<?php echo $candidate['id']; ?>">
                                        <label class="form-check-label" for="candidate_<?php echo $candidate['id']; ?>">
                                            <strong><?php echo htmlspecialchars($candidate['full_name']); ?></strong>
                                            <?php if($candidate['party_affiliation']): ?>
                                                <br><small class="text-muted">Party: <?php echo htmlspecialchars($candidate['party_affiliation']); ?></small>
                                            <?php endif; ?>
                                        </label>
                                    </div>
                                    <?php if($candidate['manifesto']): ?>
                                        <div class="manifesto">
                                            <strong>Manifesto:</strong> <?php echo htmlspecialchars(substr($candidate['manifesto'], 0, 200)); ?>...
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                            
                            <div class="alert alert-warning mt-3">
                                <strong>⚠️ Warning:</strong> Once you submit your vote, you CANNOT change it!
                            </div>
                            
                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-submit" onclick="return confirm('Are you absolutely sure? This vote is FINAL!');">
                                    ✅ Confirm & Cast Vote
                                </button>
                                <a href="dashboard.php" class="btn btn-secondary ms-2">← Back to Dashboard</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function selectCandidate(candidateId) {
            document.getElementById('candidate_' + candidateId).checked = true;
            
            // Remove selected class from all cards
            document.querySelectorAll('.candidate-card').forEach(card => {
                card.classList.remove('selected');
            });
            // Add selected class to clicked card
            event.currentTarget.classList.add('selected');
        }
    </script>
</body>
</html>
