<?php
// forgot_password.php
define('ACCESS_ALLOWED', true);
require_once 'config/database.php';
require_once 'includes/security.php';

$step = 1;
$reg_number = '';
$error = '';
$success = '';

// For demo purposes, we'll simulate SMS
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['send_code'])) {
        $reg_number = preventSQLInjection($_POST['registration_number']);
        
        $stmt = $pdo->prepare("SELECT * FROM students WHERE registration_number = ? AND is_active = 1");
        $stmt->execute([$reg_number]);
        $student = $stmt->fetch();
        
        if($student) {
            // Generate 6-digit code
            $code = rand(100000, 999999);
            $_SESSION['reset_code'] = $code;
            $_SESSION['reset_reg'] = $reg_number;
            $_SESSION['reset_expires'] = time() + 300; // 5 minutes
            
            // In production, integrate SMS API here
            // For demo, display code on screen
            $success = "Verification code sent to {$student['mobile_number']}<br>
                        <div class='alert alert-info mt-2'>
                            <strong>Demo Mode:</strong> Your verification code is: <code>$code</code>
                        </div>";
            $step = 2;
        } else {
            $error = "Registration number not found or account inactive";
        }
    } elseif(isset($_POST['verify_code'])) {
        $entered_code = $_POST['code'];
        
        if(isset($_SESSION['reset_code']) && $_SESSION['reset_code'] == $entered_code && time() < $_SESSION['reset_expires']) {
            $step = 3;
        } else {
            $error = "Invalid or expired verification code";
        }
    } elseif(isset($_POST['reset_password'])) {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if($new_password === $confirm_password && strlen($new_password) >= 6) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE students SET password = ? WHERE registration_number = ?");
            $stmt->execute([$hashed, $_SESSION['reset_reg']]);
            
            // Clear reset session
            unset($_SESSION['reset_code']);
            unset($_SESSION['reset_reg']);
            unset($_SESSION['reset_expires']);
            
            $success = "Password reset successful! Redirecting to login...";
            header("refresh:2;url=login.php");
        } else {
            $error = "Passwords do not match or too short (minimum 6 characters)";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - I.C.U.C Voting System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .reset-container {
            max-width: 500px;
            margin: 0 auto;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .step {
            flex: 1;
            text-align: center;
            position: relative;
        }
        .step .circle {
            width: 40px;
            height: 40px;
            background: #ddd;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .step.active .circle {
            background: #667eea;
            color: white;
        }
        .step.completed .circle {
            background: #28a745;
            color: white;
        }
        .step-label {
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="reset-container">
            <div class="card">
                <div class="card-header text-center">
                    <h4><i class="fas fa-key"></i> Reset Password</h4>
                    <p class="mb-0">I.C.U.C Electronic Voting System</p>
                </div>
                <div class="card-body p-4">
                    <!-- Step Indicators -->
                    <div class="step-indicator">
                        <div class="step <?php echo $step >= 1 ? ($step > 1 ? 'completed' : 'active') : ''; ?>">
                            <div class="circle"><?php echo $step > 1 ? '<i class="fas fa-check"></i>' : '1'; ?></div>
                            <div class="step-label">Verify Identity</div>
                        </div>
                        <div class="step <?php echo $step >= 2 ? ($step > 2 ? 'completed' : 'active') : ''; ?>">
                            <div class="circle"><?php echo $step > 2 ? '<i class="fas fa-check"></i>' : '2'; ?></div>
                            <div class="step-label">Verify Code</div>
                        </div>
                        <div class="step <?php echo $step >= 3 ? 'active' : ''; ?>">
                            <div class="circle">3</div>
                            <div class="step-label">Reset Password</div>
                        </div>
                    </div>
                    
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <?php if($step == 1): ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="registration_number" class="form-label">Registration Number</label>
                                <input type="text" class="form-control" id="registration_number" name="registration_number" 
                                       required placeholder="e.g., ICU/2024/001">
                                <small class="text-muted">Enter your registration number to receive a verification code</small>
                            </div>
                            <button type="submit" name="send_code" class="btn btn-primary w-100">
                                <i class="fas fa-paper-plane"></i> Send Verification Code
                            </button>
                            <div class="text-center mt-3">
                                <a href="login.php" class="text-decoration-none">← Back to Login</a>
                            </div>
                        </form>
                    <?php elseif($step == 2): ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="code" class="form-label">Verification Code</label>
                                <input type="text" class="form-control" id="code" name="code" 
                                       required placeholder="Enter 6-digit code" maxlength="6">
                                <small class="text-muted">Enter the code sent to your mobile number</small>
                            </div>
                            <button type="submit" name="verify_code" class="btn btn-primary w-100">
                                <i class="fas fa-check-circle"></i> Verify Code
                            </button>
                            <div class="text-center mt-3">
                                <a href="forgot_password.php" class="text-decoration-none">← Start Over</a>
                            </div>
                        </form>
                    <?php elseif($step == 3): ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" 
                                       required minlength="6">
                                <small class="text-muted">Minimum 6 characters</small>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                       required>
                            </div>
                            <button type="submit" name="reset_password" class="btn btn-primary w-100">
                                <i class="fas fa-save"></i> Reset Password
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="text-center mt-3 text-white">
                <small><i class="fas fa-shield-alt"></i> Secure password recovery • SMS verification</small>
            </div>
        </div>
    </div>
</body>
</html>