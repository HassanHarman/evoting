<?php
// student/change_password.php
define('ACCESS_ALLOWED', true);
require_once '../config/database.php';

if(!isset($_SESSION['voter_reg'])) {
    header("Location: ../login.php");
    exit();
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    
    $stmt = $pdo->prepare("SELECT * FROM students WHERE registration_number = ?");
    $stmt->execute([$_SESSION['voter_reg']]);
    $student = $stmt->fetch();
    
    if(password_verify($current, $student['password']) || $current == $student['mobile_number']) {
        if($new === $confirm && strlen($new) >= 6) {
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE students SET password = ? WHERE registration_number = ?");
            $stmt->execute([$hashed, $_SESSION['voter_reg']]);
            
            $_SESSION['password_changed'] = true;
            $success = "Password changed successfully! Redirecting...";
            header("refresh:2;url=dashboard.php");
        } else {
            $error = "Passwords do not match or too short";
        }
    } else {
        $error = "Current password is incorrect";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Change Password - First Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .card {
            border-radius: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h4 class="mb-0">⚠️ First Time Login - Change Password Required</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <strong>Welcome <?php echo htmlspecialchars($_SESSION['voter_name']); ?>!</strong><br>
                            For security reasons, you must change your default password before voting.
                        </div>
                        
                        <?php if($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label>Current Password (Default: Mobile Number)</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>New Password</label>
                                <input type="password" name="new_password" class="form-control" required minlength="6">
                                <small class="text-muted">Minimum 6 characters</small>
                            </div>
                            <div class="mb-3">
                                <label>Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Change Password & Continue</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>