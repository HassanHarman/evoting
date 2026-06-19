<?php
// agent/login.php
define('ACCESS_ALLOWED', true);
require_once '../config/database.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $agent_code = trim($_POST['agent_code']);
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("
        SELECT a.*, c.full_name as candidate_name, c.id as candidate_id, cat.category_name 
        FROM campaign_agents a 
        JOIN candidates c ON a.candidate_id = c.id 
        JOIN categories cat ON c.category_id = cat.id 
        WHERE a.agent_code = ? AND a.is_active = 1
    ");
    $stmt->execute([$agent_code]);
    $agent = $stmt->fetch();
    
    if($agent && ($agent['password'] === md5($password) || password_verify($password, $agent['password']))) {
        $_SESSION['agent_id'] = $agent['id'];
        $_SESSION['agent_name'] = $agent['full_name'];
        $_SESSION['agent_code'] = $agent['agent_code'];
        $_SESSION['candidate_id'] = $agent['candidate_id'];
        $_SESSION['candidate_name'] = $agent['candidate_name'];
        $_SESSION['category_name'] = $agent['category_name'];
        $_SESSION['login_type'] = 'agent';
        
        // Update last login
        $stmt = $pdo->prepare("UPDATE campaign_agents SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$agent['id']]);
        
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid agent code or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Agent Login - I.C.U.C Voting System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #20bf55 0%, #01baef 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            max-width: 450px;
            margin: 0 auto;
        }
        .card-header {
            background: linear-gradient(135deg, #20bf55 0%, #01baef 100%);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-card">
            <div class="card">
                <div class="card-header text-center">
                    <h4><i class="fas fa-user-check"></i> Campaign Agent Portal</h4>
                    <p>Monitor your candidate's performance</p>
                </div>
                <div class="card-body p-4">
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="agent_code" class="form-label">Agent Code</label>
                            <input type="text" class="form-control" id="agent_code" name="agent_code" required 
                                   placeholder="e.g., AGT001">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-sign-in-alt"></i> Access Dashboard
                        </button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <a href="../index.php" class="text-decoration-none">← Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>