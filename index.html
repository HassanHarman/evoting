<?php
define('ACCESS_ALLOWED', true);
require_once 'config/database.php';
require_once 'includes/security.php';

// Check if election is active
$stmt = $pdo->query("SELECT setting_value FROM election_settings WHERE setting_key = 'election_status'");
$election_status = $stmt->fetchColumn();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reg_no = preventSQLInjection($_POST['registration_number']);
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM students WHERE registration_number = ? AND is_active = 1");
    $stmt->execute([$reg_no]);
    $student = $stmt->fetch();
    
    if ($student && password_verify($password, $student['password'])) {
        if ($student['has_voted']) {
            $error = "You have already voted!";
        } else {
            $_SESSION['voter_reg'] = $student['registration_number'];
            $_SESSION['voter_name'] = $student['full_name'];
            $_SESSION['login_time'] = time();
            
            // Track session
            $token = bin2hex(random_bytes(32));
            $stmt = $pdo->prepare("INSERT INTO active_sessions (registration_number, session_token, ip_address) VALUES (?, ?, ?)");
            $stmt->execute([$student['registration_number'], $token, $_SERVER['REMOTE_ADDR']]);
            
            header("Location: voter/dashboard.php");
            exit();
        }
    } else {
        $error = "Invalid registration number or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I.C.U.C. Voting System - Student Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 40px;
            width: 100%;
            max-width: 450px;
        }
        .university-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .university-logo h2 {
            color: #667eea;
            font-weight: bold;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .alert-info {
            background: #e3f2fd;
            border: none;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="university-logo">
            <h2>🎓 I.C.U.C. University</h2>
            <p>Student Union Voting System</p>
        </div>
        
        <?php if($election_status == 'upcoming'): ?>
            <div class="alert alert-warning">
                ⏰ Election is not yet started. Please check back later.
            </div>
        <?php elseif($election_status == 'closed'): ?>
            <div class="alert alert-danger">
                🏁 Election has ended. Thank you for participating!
            </div>
        <?php else: ?>
        
        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="mb-3">
                <label for="registration_number" class="form-label">Registration Number</label>
                <input type="text" class="form-control" id="registration_number" name="registration_number" required 
                       placeholder="ICU/2024/001">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required 
                       placeholder="Default: Mobile number">
                <small class="text-muted">Default password is your registered mobile number</small>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login to Vote</button>
        </form>
        
        <div class="alert alert-info mt-3">
            <small>🔒 Secure voting system | One person, one vote</small>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
