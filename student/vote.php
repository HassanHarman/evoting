<?php
// student/vote.php
define('ACCESS_ALLOWED', true);
require_once '../config/database.php';

if(!isset($_SESSION['voter_reg'])) {
    header("Location: ../login.php");
    exit();
}

$category_id = (int)$_GET['category_id'];

// Check if already voted
$stmt = $pdo->prepare("SELECT COUNT(*) FROM votes WHERE voter_registration = ? AND category_id = ?");
$stmt->execute([$_SESSION['voter_reg'], $category_id]);
if($stmt->fetchColumn() > 0) {
    header("Location: ballot.php?error=Already voted");
    exit();
}

// Get category info
$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ? AND is_active = 1");
$stmt->execute([$category_id]);
$category = $stmt->fetch();
if(!$category) {
    header("Location: ballot.php");
    exit();
}

// Get candidates
$stmt = $pdo->prepare("SELECT * FROM candidates WHERE category_id = ? AND is_active = 1 ORDER BY full_name");
$stmt->execute([$category_id]);
$candidates = $stmt->fetchAll();

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['candidate_id'])) {
    $candidate_id = (int)$_POST['candidate_id'];
    
    // Final check
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM votes WHERE voter_registration = ? AND category_id = ?");
    $stmt->execute([$_SESSION['voter_reg'], $category_id]);
    if($stmt->fetchColumn() == 0) {
        try {
            $pdo->beginTransaction();
            
            $token = hash('sha256', $_SESSION['voter_reg'] . $candidate_id . $category_id . time() . rand());
            
            $stmt = $pdo->prepare("INSERT INTO votes (voter_registration, candidate_id, category_id, voting_token, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_SESSION['voter_reg'],
                $candidate_id,
                $category_id,
                $token,
                $_SERVER['REMOTE_ADDR'],
                $_SERVER['HTTP_USER_AGENT']
            ]);
            
            // Check completion
            $stmt = $pdo->query("SELECT COUNT(*) FROM categories WHERE is_active = 1");
            $total_cats = $stmt->fetchColumn();
            
            $stmt = $pdo->prepare("SELECT COUNT(DISTINCT category_id) FROM votes WHERE voter_registration = ?");
            $stmt->execute([$_SESSION['voter_reg']]);
            $completed_cats = $stmt->fetchColumn();
            
            if($completed_cats == $total_cats) {
                $completion_hash = hash('sha256', $_SESSION['voter_reg'] . time() . rand());
                $stmt = $pdo->prepare("INSERT INTO vote_completion (voter_registration, verification_hash) VALUES (?, ?)");
                $stmt->execute([$_SESSION['voter_reg'], $completion_hash]);
                
                $stmt = $pdo->prepare("UPDATE students SET has_voted = 1 WHERE registration_number = ?");
                $stmt->execute([$_SESSION['voter_reg']]);
            }
            
            $pdo->commit();
            
            // Get candidate name for receipt
            $stmt = $pdo->prepare("SELECT full_name FROM candidates WHERE id = ?");
            $stmt->execute([$candidate_id]);
            $candidate_name = $stmt->fetchColumn();
            
            $_SESSION['last_vote'] = [
                'candidate' => $candidate_name,
                'category' => $category['category_name'],
                'time' => date('Y-m-d H:i:s'),
                'token' => substr($token, 0, 16)
            ];
            
            header("Location: receipt.php");
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea20 0%, #764ba220 100%);
            min-height: 100vh;
        }
        .candidate-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        .candidate-card:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        .candidate-card.selected {
            border-color: #667eea;
            background: linear-gradient(135deg, #667eea10 0%, #764ba210 100%);
        }
        .candidate-radio {
            width: 25px;
            height: 25px;
            margin-right: 15px;
            accent-color: #667eea;
        }
        .manifesto-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-top: 15px;
            font-size: 14px;
        }
        .modal-confirm {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .btn-submit {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 15px 40px;
            font-size: 18px;
            border-radius: 50px;
            border: none;
        }
        .timer-info {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: white;
            padding: 10px 20px;
            border-radius: 50px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <!-- Header -->
                <div class="text-center mb-4">
                    <h2><i class="fas fa-vote-yea"></i> Vote for <?php echo htmlspecialchars($category['category_name']); ?></h2>
                    <p class="text-muted">Select ONE candidate carefully. Your vote is FINAL.</p>
                </div>
                
                <?php if($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" id="voteForm">
                    <?php foreach($candidates as $index => $candidate): ?>
                        <div class="candidate-card" onclick="selectCandidate(<?php echo $candidate['id']; ?>, this)">
                            <div class="d-flex align-items-start">
                                <input type="radio" name="candidate_id" id="candidate_<?php echo $candidate['id']; ?>" 
                                       value="<?php echo $candidate['id']; ?>" class="candidate-radio mt-1">
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0"><?php echo htmlspecialchars($candidate['full_name']); ?></h5>
                                        <?php if($candidate['party_affiliation']): ?>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($candidate['party_affiliation']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if($candidate['slogan']): ?>
                                        <small class="text-muted">"<?php echo htmlspecialchars($candidate['slogan']); ?>"</small>
                                    <?php endif; ?>
                                    <?php if($candidate['manifesto']): ?>
                                        <div class="manifesto-box">
                                            <strong><i class="fas fa-file-alt"></i> Manifesto Highlights:</strong><br>
                                            <?php echo nl2br(htmlspecialchars(substr($candidate['manifesto'], 0, 300))); ?>
                                            <?php if(strlen($candidate['manifesto']) > 300): ?>...<?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="alert alert-warning mt-4">
                        <i class="fas fa-gavel"></i>
                        <strong>Election Rules:</strong> 
                        <ul class="mb-0 mt-2">
                            <li>You can only vote ONCE for this position</li>
                            <li>Your vote is anonymous and secure</li>
                            <li>No one can see how you voted</li>
                        </ul>
                    </div>
                    
                    <div class="text-center mt-4">
                        <button type="button" class="btn btn-submit" data-bs-toggle="modal" data-bs-target="#confirmModal" id="submitBtn" disabled>
                            <i class="fas fa-check-circle"></i> Submit Vote Securely
                        </button>
                        <a href="ballot.php" class="btn btn-secondary ms-2">← Back to Ballot</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header modal-confirm text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Confirm Your Vote</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="fas fa-ballot fa-4x text-warning mb-3"></i>
                    <h4>Are you absolutely sure?</h4>
                    <p id="selectedCandidateText" class="mt-3"></p>
                    <div class="alert alert-danger">
                        <strong>⚠️ WARNING:</strong> Once confirmed, you CANNOT change or retract your vote!
                    </div>
                    <p class="text-muted small">This action is irreversible and will be recorded in the blockchain audit trail.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="finalConfirmBtn">
                        <i class="fas fa-check"></i> Confirm & Cast Vote
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="timer-info">
        <i class="fas fa-clock"></i> Take your time. No timeout limit.
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedCandidateId = null;
        let selectedCandidateName = '';
        
        function selectCandidate(id, element) {
            // Remove selected class from all cards
            document.querySelectorAll('.candidate-card').forEach(card => {
                card.classList.remove('selected');
            });
            // Add selected class to clicked card
            element.classList.add('selected');
            
            // Check radio button
            const radio = document.getElementById('candidate_' + id);
            radio.checked = true;
            selectedCandidateId = id;
            selectedCandidateName = element.querySelector('h5').innerText;
            
            // Enable submit button
            document.getElementById('submitBtn').disabled = false;
            
            // Update modal text
            document.getElementById('selectedCandidateText').innerHTML = 
                'You are voting for: <strong>' + selectedCandidateName + '</strong>';
        }
        
        // Final confirmation
        document.getElementById('finalConfirmBtn').addEventListener('click', function() {
            if(selectedCandidateId) {
                document.getElementById('voteForm').submit();
            }
        });
        
        // Prevent accidental form submission
        document.getElementById('voteForm').addEventListener('submit', function(e) {
            e.preventDefault();
            if(selectedCandidateId) {
                this.submit();
            }
        });
    </script>
</body>
</html>