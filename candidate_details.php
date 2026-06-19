<?php
// candidate_details.php
define('ACCESS_ALLOWED', true);
require_once 'config/database.php';

$candidate_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get candidate details
$stmt = $pdo->prepare("
    SELECT c.*, cat.category_name, cat.description as category_description 
    FROM candidates c 
    JOIN categories cat ON c.category_id = cat.id 
    WHERE c.id = ? AND c.is_active = 1
");
$stmt->execute([$candidate_id]);
$candidate = $stmt->fetch();

if(!$candidate) {
    header("Location: candidates_list.php?error=Candidate not found");
    exit();
}

// Get election status
$stmt = $pdo->query("SELECT setting_value FROM election_settings WHERE setting_key = 'election_status'");
$election_status = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($candidate['full_name']); ?> - Candidate Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;600;700&family=Sora:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0f766e;
            --primary-dark: #115e59;
            --accent: #f59e0b;
            --accent-soft: #fef3c7;
            --ink: #0f172a;
            --muted: #64748b;
            --surface: #ffffff;
            --bg: #f6f4ef;
            --shadow: 0 24px 55px rgba(15, 23, 42, 0.12);
            --shadow-soft: 0 16px 32px rgba(15, 23, 42, 0.08);
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Sora', 'Trebuchet MS', sans-serif;
            background: var(--bg);
            color: var(--ink);
        }

        h1, h2, h3, h4 {
            font-family: 'Playfair Display', serif;
        }

        .navbar-custom {
            background: rgba(255, 255, 255, 0.94);
            border-bottom: 1px solid rgba(15, 23, 42, 0.08);
            backdrop-filter: blur(12px);
        }

        .brand-logo {
            width: 42px;
            height: 42px;
            object-fit: contain;
        }

        .profile-header {
            background: var(--surface);
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 30px;
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(15, 23, 42, 0.06);
        }

        .profile-image {
            width: 100%;
            height: 420px;
            object-fit: cover;
        }

        .candidate-name {
            font-size: clamp(2rem, 3vw, 2.6rem);
            font-weight: 700;
            color: var(--primary-dark);
        }

        .manifesto-box {
            background: var(--surface);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(15, 23, 42, 0.05);
        }

        .info-card {
            background: var(--surface);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(15, 23, 42, 0.05);
        }

        .info-card h5 {
            font-family: 'Sora', sans-serif;
            font-weight: 600;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent), #f97316);
            border: none;
            color: #1f2937;
            font-weight: 600;
        }

        .btn-primary:hover {
            color: #1f2937;
        }

        .btn-outline-light {
            border-color: rgba(15, 23, 42, 0.2);
            color: var(--primary-dark);
        }

        .btn-outline-light:hover {
            background: rgba(15, 118, 110, 0.1);
            color: var(--primary-dark);
        }

        .badge-soft {
            background: rgba(15, 118, 110, 0.12);
            color: var(--primary-dark);
            font-weight: 600;
        }

        .social-share {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 1000;
        }

        .share-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #25D366;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 10px;
            cursor: pointer;
            transition: transform 0.3s;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.2);
        }

        .share-btn:hover {
            transform: scale(1.08);
        }

        .share-btn.fb {
            background: #1877f2;
        }

        .share-btn.twitter {
            background: #1da1f2;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-custom navbar-light py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand d-flex align-items-center gap-3" href="index.php">
                <img src="logo-n.png" alt="I.C.U.C University" class="brand-logo">
                <div>
                    <div class="fw-bold">I.C.U.C University</div>
                    <small class="text-muted">Electronic Voting System</small>
                </div>
            </a>
            <div>
                <a href="candidates_list.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to Candidates
                </a>
                <a href="login.php" class="btn btn-primary btn-sm ms-2">
                    <i class="fas fa-sign-in-alt"></i> Login to Vote
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="row g-0">
                <div class="col-md-4">
                    <?php if($candidate['photo'] && file_exists($candidate['photo'])): ?>
                        <img src="<?php echo $candidate['photo']; ?>" class="profile-image" alt="<?php echo $candidate['full_name']; ?>">
                    <?php else: ?>
                        <div class="profile-image d-flex align-items-center justify-content-center bg-secondary text-white">
                            <i class="fas fa-user-circle fa-8x"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-8">
                    <div class="p-4">
                        <h1 class="candidate-name"><?php echo htmlspecialchars($candidate['full_name']); ?></h1>
                        <h5 class="text-muted">
                            <i class="fas fa-tag"></i> Running for: <?php echo htmlspecialchars($candidate['category_name']); ?>
                        </h5>
                        <?php if($candidate['registration_number']): ?>
                            <p><i class="fas fa-id-card"></i> Reg No: <?php echo htmlspecialchars($candidate['registration_number']); ?></p>
                        <?php endif; ?>
                        <?php if($candidate['party_affiliation']): ?>
                            <span class="badge bg-primary fs-6"><?php echo htmlspecialchars($candidate['party_affiliation']); ?></span>
                        <?php endif; ?>
                        <?php if($candidate['is_independent']): ?>
                            <span class="badge bg-warning fs-6">Independent Candidate</span>
                        <?php endif; ?>
                        
                        <?php if($candidate['slogan']): ?>
                            <div class="alert alert-info mt-3">
                                <i class="fas fa-quote-left"></i> "<?php echo htmlspecialchars($candidate['slogan']); ?>"
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-8">
                <!-- Manifesto -->
                <div class="manifesto-box">
                    <h3><i class="fas fa-file-alt"></i> Manifesto & Vision</h3>
                    <hr>
                    <?php if($candidate['manifesto']): ?>
                        <div style="line-height: 1.8;">
                            <?php echo nl2br(htmlspecialchars($candidate['manifesto'])); ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Manifesto details will be updated soon.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-md-4">
                <!-- Position Info -->
                <div class="info-card">
                    <h5><i class="fas fa-info-circle"></i> About This Position</h5>
                    <hr>
                    <p><?php echo htmlspecialchars($candidate['category_description'] ?: 'No additional description available.'); ?></p>
                </div>
                
                <!-- Election Status -->
                <div class="info-card">
                    <h5><i class="fas fa-calendar-alt"></i> Election Status</h5>
                    <hr>
                    <?php if($election_status == 'active'): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-play-circle"></i> Election is ACTIVE
                        </div>
                        <a href="login.php" class="btn btn-primary w-100">
                            <i class="fas fa-vote-yea"></i> Vote Now
                        </a>
                    <?php elseif($election_status == 'upcoming'): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-clock"></i> Election Coming Soon
                        </div>
                    <?php elseif($election_status == 'closed'): ?>
                        <div class="alert alert-secondary">
                            <i class="fas fa-flag-checkered"></i> Election Completed
                        </div>
                        <a href="results/public.php" class="btn btn-info w-100">
                            <i class="fas fa-chart-bar"></i> View Results
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Social Share Floating Buttons -->
    <div class="social-share">
        <div class="share-btn fb" onclick="shareOnFacebook()">
            <i class="fab fa-facebook-f"></i>
        </div>
        <div class="share-btn twitter" onclick="shareOnTwitter()">
            <i class="fab fa-twitter"></i>
        </div>
        <div class="share-btn" onclick="shareOnWhatsApp()">
            <i class="fab fa-whatsapp"></i>
        </div>
    </div>
    
    <footer class="text-center mt-5 mb-3">
        <small class="text-muted">© 2026 I.C.U.C University - Electronic Voting System</small>
    </footer>
    
    <script>
        function shareOnFacebook() {
            const url = encodeURIComponent(window.location.href);
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank', 'width=600,height=400');
        }
        
        function shareOnTwitter() {
            const text = encodeURIComponent(`Check out ${document.title} on I.C.U.C Voting System`);
            const url = encodeURIComponent(window.location.href);
            window.open(`https://twitter.com/intent/tweet?text=${text}&url=${url}`, '_blank', 'width=600,height=400');
        }
        
        function shareOnWhatsApp() {
            const text = encodeURIComponent(`Check out ${document.title} - ${window.location.href}`);
            window.open(`https://wa.me/?text=${text}`, '_blank');
        }
    </script>
</body>
</html>