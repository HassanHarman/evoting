<?php
// candidate/login.php
define('ACCESS_ALLOWED', true);
require_once '../config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM candidates WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    $candidate = $stmt->fetch();
    
    // Check password (MD5 for demo, use password_verify in production)
    if($candidate && ($candidate['password'] === md5($password) || password_verify($password, $candidate['password']))) {
        $_SESSION['candidate_id'] = $candidate['id'];
        $_SESSION['candidate_name'] = $candidate['full_name'];
        $_SESSION['candidate_email'] = $candidate['email'];
        $_SESSION['candidate_category'] = $candidate['category_id'];
        $_SESSION['login_type'] = 'candidate';
        
        // Update last login
        $stmt = $pdo->prepare("UPDATE candidates SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$candidate['id']]);
        
        // Log access
        $stmt = $pdo->prepare("INSERT INTO portal_access_log (portal_type, user_identifier, ip_address, user_agent, action, login_success) VALUES ('candidate', ?, ?, ?, 'login', 1)");
        $stmt->execute([$candidate['email'], $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);
        
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid email or password";
        
        // Log failed attempt
        $stmt = $pdo->prepare("INSERT INTO portal_access_log (portal_type, user_identifier, ip_address, user_agent, action, login_success) VALUES ('candidate', ?, ?, ?, 'login', 0)");
        $stmt->execute([$email, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Candidate Login - I.C.U.C Voting System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            max-width: 450px;
            margin: 0 auto;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .card-header {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
            text-align: center;
        }
        .btn-candidate {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            border: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-card">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-user-tie"></i> Candidate Portal</h4>
                    <p class="mb-0">I.C.U.C Election Candidate Login</p>
                </div>
                <div class="card-body p-4">
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required 
                                   placeholder="candidate@icuc.ac.ug">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-candidate w-100">
                            <i class="fas fa-sign-in-alt"></i> Login to Dashboard
                        </button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <a href="../index.php" class="text-decoration-none">← Back to Home</a>
                    </div>
                </div>
            </div>
            <div class="text-center mt-3 text-white">
                <small><i class="fas fa-shield-alt"></i> Authorized access only</small>
            </div>
        </div>
    </div>
</body>
</html>