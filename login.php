<?php
// login.php
define('ACCESS_ALLOWED', true);
require_once 'config/database.php';
require_once 'includes/security.php';

$error = '';
$show_remember = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reg_no = preventSQLInjection($_POST['registration_number']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']);
    
    // Rate limiting
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_audit WHERE ip_address = ? AND action_timestamp > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
    $stmt->execute([$ip]);
    $attempts = $stmt->fetchColumn();
    
    if($attempts > 5) {
        $error = "Too many login attempts. Please try again later.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE registration_number = ? AND is_active = 1");
        $stmt->execute([$reg_no]);
        $student = $stmt->fetch();
        
        if($student && password_verify($password, $student['password'])) {
            // Check if first login (password equals mobile number)
            $is_first_login = ($password == $student['mobile_number']);
            
            $_SESSION['voter_reg'] = $student['registration_number'];
            $_SESSION['voter_name'] = $student['full_name'];
            $_SESSION['voter_id'] = $student['id'];
            $_SESSION['login_time'] = time();
            
            if($remember) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + (86400 * 30), "/");
                $stmt = $pdo->prepare("UPDATE students SET remember_token = ? WHERE registration_number = ?");
                $stmt->execute([$token, $student['registration_number']]);
            }
            
            if($is_first_login) {
                header("Location: student/change_password.php");
            } else {
                header("Location: student/dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid registration number or password";
            // Log failed attempt
            logAdminAction($reg_no, 'FAILED_LOGIN', 'students', 0, '', $ip);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Login - I.C.U.C Voting System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container {
            max-width: 450px;
            margin: 0 auto;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .forgot-link {
            cursor: pointer;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-header">
                    <h4>🎓 Student Login</h4>
                    <p class="mb-0">I.C.U.C Electronic Voting System</p>
                </div>
                <div class="card-body p-4">
                    <?php if($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="registration_number" class="form-label">Registration Number</label>
                            <input type="text" class="form-control" id="registration_number" name="registration_number" required 
                                   placeholder="e.g., ICU/2024/001">
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <small class="text-muted">Default password: Your registered mobile number</small>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">Remember this device</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mb-3">Login to Vote</button>
                        <div class="text-center">
                            <a href="forgot_password.php" class="forgot-link">Forgot Password?</a>
                        </div>
                    </form>
                </div>
            </div>
            <div class="text-center mt-3 text-white">
                <small>🔒 Secure voting system | One person, one vote</small>
            </div>
        </div>
    </div>
</body>
</html>