<?php
// admin/close_election.php
define('ACCESS_ALLOWED', true);
require_once '../config/database.php';

if(!isset($_SESSION['admin_logged'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';
$verification_hash = '';

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['declare_results'])) {
    try {
        $pdo->beginTransaction();
        
        // Close voting
        $stmt = $pdo->prepare("UPDATE election_settings SET setting_value = 'closed' WHERE setting_key = 'election_status'");
        $stmt->execute();
        
        // Calculate final hash
        $stmt = $pdo->query("SELECT COUNT(*) as total_votes, SUM(vote_count) as sum FROM votes");
        $stats = $stmt->fetch();
        
        $hash_data = $stats['total_votes'] . $stats['sum'] . time() . rand();
        $verification_hash = hash('sha256', $hash_data);
        
        // Save hash
        $stmt = $pdo->prepare("INSERT INTO election_settings (setting_key, setting_value) VALUES ('results_hash', ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$verification_hash, $verification_hash]);
        
        // Mark results as published
        $stmt = $pdo->prepare("UPDATE election_settings SET setting_value = '1' WHERE setting_key = 'results_published'");
        $stmt->execute();
        
        // Log the action
        logAdminAction($_SESSION['admin_username'], 'DECLARE_RESULTS', 'election', 0, '', 'Election closed and results published');
        
        $pdo->commit();
        $success = "Election closed successfully! Results have been finalized and published.";
        
    } catch(Exception $e) {
        $pdo->rollBack();
        $error = "Error closing election: " . $e->getMessage();
    }
}

// Check current status
$stmt = $pdo->query("SELECT setting_value FROM election_settings WHERE setting_key = 'election_status'");
$current_status = $stmt->fetchColumn();
$pageTitle = 'Close Election';
$pageSubtitle = 'Finalize voting, publish results, and lock the ballot.';
$topbarActions = '<a class="btn btn-primary btn-sm" href="../results/public.php">Public Results</a>';
include 'partials/admin_header.php';
?>

<div class="card-panel">
    <div class="card-header-row">
        <div>
            <h3>🏁 Declare Final Results</h3>
            <p>Close the election, generate the verification hash, and publish results.</p>
        </div>
        <span class="badge-soft">Critical action</span>
    </div>
    <?php if($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if($success): ?>
        <div class="alert alert-success">
            <?php echo $success; ?>
            <hr>
            <strong>Verification Hash:</strong><br>
            <code><?php echo $verification_hash; ?></code>
            <hr>
            <a href="../results/public.php" class="btn btn-primary">View Public Results Portal →</a>
        </div>
    <?php endif; ?>
    
    <?php if($current_status != 'closed'): ?>
        <div class="alert alert-warning">
            <strong>⚠️ WARNING: This action is irreversible.</strong><br>
            Once you close the election:
            <ul>
                <li>Voting will stop immediately</li>
                <li>Final results will be calculated</li>
                <li>A SHA-256 verification hash will be generated</li>
                <li>Results will be published to the public portal</li>
                <li>No further changes can be made</li>
            </ul>
        </div>
        
        <form method="POST">
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="confirm" required>
                <label class="form-check-label" for="confirm">
                    I confirm that all votes have been counted and verified. I understand this action cannot be undone.
                </label>
            </div>
            <button type="submit" name="declare_results" class="btn btn-danger btn-lg w-100">
                🔒 DECLARE FINAL RESULTS & CLOSE ELECTION
            </button>
        </form>
    <?php else: ?>
        <div class="alert alert-success">
            ✅ Election has been closed. Results are published.
            <br>
            <a href="../results/public.php" class="btn btn-success mt-3">View Results Portal</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'partials/admin_footer.php'; ?>
