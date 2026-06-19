<?php
// index.php - Public Landing Page
define('ACCESS_ALLOWED', true);
require_once 'config/database.php';

// Get election status
$stmt = $pdo->query("SELECT setting_value FROM election_settings WHERE setting_key = 'election_status'");
$election_status = $stmt->fetchColumn();

// Get upcoming election dates
$stmt = $pdo->query("SELECT setting_value FROM election_settings WHERE setting_key = 'election_start_date'");
$start_date = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT setting_value FROM election_settings WHERE setting_key = 'election_end_date'");
$end_date = $stmt->fetchColumn();

// Get total voters and turnout
$stmt = $pdo->query("SELECT COUNT(*) FROM students WHERE is_active = 1");
$total_voters = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM students WHERE has_voted = 1");
$voted = $stmt->fetchColumn();

$turnout = $total_voters > 0 ? round(($voted / $total_voters) * 100, 1) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>I.C.U.C University - Electronic Voting System</title>
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
            --shadow: 0 25px 60px rgba(15, 23, 42, 0.12);
            --shadow-soft: 0 18px 40px rgba(15, 23, 42, 0.08);
        }
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Sora', 'Trebuchet MS', sans-serif;
            color: var(--ink);
            background: var(--bg);
            background-image:
                radial-gradient(circle at 10% 10%, rgba(15, 118, 110, 0.16), transparent 40%),
                radial-gradient(circle at 85% 0%, rgba(245, 158, 11, 0.18), transparent 45%);
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5 {
            font-family: 'Playfair Display', serif;
            color: var(--ink);
        }

        a {
            color: inherit;
        }

        .main-nav {
            background: rgba(255, 255, 255, 0.92);
            border-bottom: 1px solid rgba(15, 23, 42, 0.08);
            backdrop-filter: blur(12px);
        }

        .brand-logo {
            width: 46px;
            height: 46px;
            object-fit: contain;
        }

        .brand-title {
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .brand-subtitle {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.18em;
            color: var(--muted);
        }

        .hero {
            position: relative;
            color: #f8fafc;
            padding: 110px 0 120px;
            background: linear-gradient(135deg, #0f766e 0%, #0f172a 100%);
            clip-path: polygon(0 0, 100% 0, 100% 88%, 0 100%);
            overflow: hidden;
        }

        .hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 20% 20%, rgba(250, 204, 21, 0.3), transparent 45%);
            opacity: 0.7;
        }

        .hero::after {
            content: "";
            position: absolute;
            width: 320px;
            height: 320px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            top: -140px;
            right: -120px;
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .hero-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.16);
            padding: 8px 18px;
            border-radius: 999px;
            font-size: 0.85rem;
            letter-spacing: 0.16em;
            text-transform: uppercase;
        }

        .hero-title {
            font-size: clamp(2.6rem, 4vw, 3.8rem);
            margin-top: 20px;
            color: #f8fafc;
        }

        .hero-subtitle {
            font-size: 1.1rem;
            color: rgba(248, 250, 252, 0.85);
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            margin-top: 24px;
        }

        .hero-meta {
            margin-top: 18px;
            font-size: 0.92rem;
            color: rgba(248, 250, 252, 0.75);
        }

        .hero-panel {
            position: relative;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.18);
            padding: 24px;
            border-radius: 24px;
            backdrop-filter: blur(12px);
            color: #f8fafc;
            box-shadow: 0 25px 60px rgba(15, 23, 42, 0.35);
        }

        .hero-panel h5 {
            color: #f8fafc;
        }

        .snapshot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12);
        }

        .snapshot:last-child {
            border-bottom: none;
        }

        .snapshot strong {
            font-size: 1.4rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent), #f97316);
            border: none;
            color: #1f2937;
            font-weight: 600;
            box-shadow: 0 12px 30px rgba(245, 158, 11, 0.35);
        }

        .btn-primary:hover {
            color: #1f2937;
            transform: translateY(-2px);
        }

        .btn-outline-light {
            border: 1px solid rgba(248, 250, 252, 0.6);
            color: #f8fafc;
        }

        .btn-outline-primary {
            border-color: rgba(15, 118, 110, 0.3);
            color: var(--primary-dark);
        }

        .btn-outline-primary:hover {
            background: rgba(15, 118, 110, 0.1);
            color: var(--primary-dark);
        }

        .section-title {
            font-size: clamp(1.8rem, 3vw, 2.4rem);
        }

        .stat-card {
            background: var(--surface);
            border-radius: 20px;
            padding: 28px;
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(15, 23, 42, 0.06);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-align: left;
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow);
        }

        .stat-number {
            font-size: 42px;
            font-weight: 700;
            color: var(--primary-dark);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            background: var(--accent-soft);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-dark);
            margin-bottom: 16px;
        }

        .section-card {
            background: var(--surface);
            border-radius: 22px;
            padding: 28px;
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(15, 23, 42, 0.05);
        }

        .candidate-card {
            border-radius: 18px;
            overflow: hidden;
            background: var(--surface);
            box-shadow: var(--shadow-soft);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }

        .candidate-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow);
        }

        .candidate-img {
            width: 100%;
            height: 240px;
            object-fit: cover;
            background: #e2e8f0;
        }

        .candidate-meta {
            font-size: 0.85rem;
            color: var(--muted);
        }

        .badge-soft {
            background: rgba(15, 118, 110, 0.12);
            color: var(--primary-dark);
            font-weight: 600;
            border-radius: 999px;
            padding: 6px 14px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .cta-banner {
            background: linear-gradient(135deg, rgba(15, 118, 110, 0.12), rgba(245, 158, 11, 0.2));
            border-radius: 20px;
            padding: 22px 26px;
            border: 1px dashed rgba(15, 118, 110, 0.2);
        }

        .entry-section {
            position: relative;
        }

        .entry-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .entry-card {
            background: var(--surface);
            border-radius: 20px;
            padding: 22px;
            border: 1px solid rgba(15, 23, 42, 0.08);
            box-shadow: var(--shadow-soft);
            display: flex;
            flex-direction: column;
            gap: 12px;
            min-height: 220px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .entry-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow);
        }

        .entry-icon {
            width: 52px;
            height: 52px;
            border-radius: 18px;
            background: rgba(15, 118, 110, 0.12);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-dark);
            font-size: 20px;
        }

        .entry-button {
            margin-top: auto;
            align-self: flex-start;
        }

        .footer {
            background: #0f172a;
            color: rgba(248, 250, 252, 0.8);
            padding: 50px 0;
            margin-top: 60px;
        }

        .login-buttons .btn {
            margin-left: 8px;
        }

        @media (max-width: 991px) {
            .hero {
                text-align: center;
            }
            .hero-actions {
                justify-content: center;
            }
        }

        @media (max-width: 768px) {
            .hero {
                padding: 70px 0 90px;
                clip-path: polygon(0 0, 100% 0, 100% 94%, 0 100%);
            }
            .stat-number {
                font-size: 32px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light main-nav py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <a class="navbar-brand d-flex align-items-center gap-3" href="index.php">
                <img src="logo-n.png" alt="I.C.U.C University logo" class="brand-logo">
                <div>
                    <div class="brand-title">I.C.U.C University</div>
                    <div class="brand-subtitle">Electronic Voting System</div>
                </div>
            </a>
            <div class="login-buttons d-flex align-items-center">
                <a href="login.php" class="btn btn-primary">Student Login</a>
                <a href="admin/login.php" class="btn btn-outline-primary">Admin</a>
            </div>
        </div>
    </nav>
    
    <section class="hero">
        <div class="container hero-content">
            <div class="row align-items-center g-5">
                <div class="col-lg-7">
                    <span class="hero-eyebrow"><i class="fas fa-circle-check"></i> Student Union Elections</span>
                    <h1 class="hero-title">Student Union Elections 2026</h1>
                    <p class="hero-subtitle">A secure, transparent, and verifiable voting experience for the I.C.U.C community.</p>
                    <div class="hero-actions">
                        <?php if($election_status == 'active'): ?>
                            <a href="login.php" class="btn btn-primary btn-lg">Vote Now</a>
                            <a href="candidates_list.php" class="btn btn-outline-light btn-lg">Meet the Candidates</a>
                        <?php elseif($election_status == 'upcoming'): ?>
                            <a href="candidates_list.php" class="btn btn-primary btn-lg">Explore Candidates</a>
                            <a href="#guidelines" class="btn btn-outline-light btn-lg">Election Guidelines</a>
                        <?php elseif($election_status == 'closed'): ?>
                            <a href="results/public.php" class="btn btn-primary btn-lg">View Results</a>
                            <a href="candidates_list.php" class="btn btn-outline-light btn-lg">Candidate Archive</a>
                        <?php endif; ?>
                    </div>
                    <div class="hero-meta">
                        <?php if($election_status == 'active' && $end_date): ?>
                            Election closes on <?php echo date('F j, Y, g:i a', strtotime($end_date)); ?>
                        <?php elseif($election_status == 'upcoming' && $start_date): ?>
                            Election opens on <?php echo date('F j, Y', strtotime($start_date)); ?>
                        <?php elseif($election_status == 'closed'): ?>
                            Thank you for participating in the 2026 election.
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="hero-panel">
                        <h5 class="mb-3">Election Snapshot</h5>
                        <div class="snapshot">
                            <span>Registered voters</span>
                            <strong><?php echo number_format($total_voters); ?></strong>
                        </div>
                        <div class="snapshot">
                            <span>Votes cast</span>
                            <strong><?php echo number_format($voted); ?></strong>
                        </div>
                        <div class="snapshot">
                            <span>Voter turnout</span>
                            <strong><?php echo $turnout; ?>%</strong>
                        </div>
                        <div class="mt-3">
                            <span class="badge-soft"><i class="fas fa-shield-halved"></i> Integrity verified</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="container mt-5 entry-section" id="entry-points">
        <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
            <div>
                <h2 class="section-title">Quick Entry Points</h2>
                <p class="text-muted mb-0">Jump into the right portal for students, candidates, staff, and agents.</p>
            </div>
            <span class="badge-soft"><i class="fas fa-link"></i> Key Access</span>
        </div>
        <div class="entry-grid">
            <div class="entry-card">
                <div class="entry-icon"><i class="fas fa-user-check"></i></div>
                <h5>Student Voting</h5>
                <p class="candidate-meta">Access your ballot, verify eligibility, and submit your vote securely.</p>
                <?php if($election_status == 'active'): ?>
                    <a href="login.php" class="btn btn-primary btn-sm entry-button">Vote Now</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-primary btn-sm entry-button">Student Login</a>
                <?php endif; ?>
            </div>
            <div class="entry-card">
                <div class="entry-icon"><i class="fas fa-users"></i></div>
                <h5>Candidate Directory</h5>
                <p class="candidate-meta">Explore manifestos, positions, and campaign updates.</p>
                <a href="candidates_list.php" class="btn btn-outline-primary btn-sm entry-button">Meet Candidates</a>
            </div>
            <div class="entry-card">
                <div class="entry-icon"><i class="fas fa-user-tie"></i></div>
                <h5>Candidate Portal</h5>
                <p class="candidate-meta">Manage profile details, monitor participation, and track updates.</p>
                <a href="candidate/login.php" class="btn btn-outline-primary btn-sm entry-button">Candidate Login</a>
            </div>
            <div class="entry-card">
                <div class="entry-icon"><i class="fas fa-bullhorn"></i></div>
                <h5>Campaign Agent</h5>
                <p class="candidate-meta">Submit outreach updates and manage candidate campaign support.</p>
                <a href="agent/login.php" class="btn btn-outline-primary btn-sm entry-button">Agent Login</a>
            </div>
            <div class="entry-card">
                <div class="entry-icon"><i class="fas fa-shield-halved"></i></div>
                <h5>Admin Commission</h5>
                <p class="candidate-meta">Monitor election progress and verify audit logs.</p>
                <a href="admin/login.php" class="btn btn-outline-primary btn-sm entry-button">Admin Login</a>
            </div>
            <?php if($election_status == 'closed'): ?>
                <div class="entry-card">
                    <div class="entry-icon"><i class="fas fa-trophy"></i></div>
                    <h5>Public Results</h5>
                    <p class="candidate-meta">View certified results and verification hashes.</p>
                    <a href="results/public.php" class="btn btn-primary btn-sm entry-button">View Results</a>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <section class="container mt-5">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-number"><?php echo number_format($total_voters); ?></div>
                    <div class="candidate-meta">Registered voters across all programs.</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-vote-yea"></i></div>
                    <div class="stat-number"><?php echo number_format($voted); ?></div>
                    <div class="candidate-meta">Votes cast with real-time validation.</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                    <div class="stat-number"><?php echo $turnout; ?>%</div>
                    <div class="candidate-meta">Participation rate across the electorate.</div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="container mt-5" id="guidelines">
        <div class="text-center mb-4">
            <h2 class="section-title">Election Guidelines</h2>
            <p class="text-muted">Clear, secure, and accountable voting for every student.</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="section-card">
                    <ul class="list-unstyled mb-4">
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> One student = one vote. No duplicates allowed.</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Votes are final and cannot be changed once submitted.</li>
                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Results are verifiable via SHA-256 integrity hashes.</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i> Live tallies are hidden to maintain fairness.</li>
                    </ul>
                    <a href="guidelines.pdf" class="btn btn-outline-primary">Download Full Guidelines</a>
                </div>
            </div>
        </div>
    </section>
    
    <section class="container mt-5">
        <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
            <div>
                <h2 class="section-title">Meet the Candidates</h2>
                <p class="text-muted mb-0">Explore manifestos and campaigns across every position.</p>
            </div>
            <a href="candidates_list.php" class="btn btn-outline-primary">View All Candidates</a>
        </div>
        <div class="row g-4">
            <?php
            $stmt = $pdo->query("SELECT c.*, cat.category_name FROM candidates c JOIN categories cat ON c.category_id = cat.id WHERE c.is_active = 1 ORDER BY cat.sort_order, c.full_name LIMIT 6");
            $candidates = $stmt->fetchAll();
            foreach($candidates as $candidate):
            ?>
            <div class="col-md-4">
                <div class="candidate-card">
                    <?php if($candidate['photo'] && file_exists($candidate['photo'])): ?>
                        <img src="<?php echo $candidate['photo']; ?>" class="candidate-img" alt="<?php echo $candidate['full_name']; ?>">
                    <?php else: ?>
                        <div class="candidate-img d-flex align-items-center justify-content-center bg-secondary text-white">
                            <i class="fas fa-user fa-4x"></i>
                        </div>
                    <?php endif; ?>
                    <div class="p-4">
                        <div class="badge-soft mb-2"><i class="fas fa-award"></i> <?php echo htmlspecialchars($candidate['category_name']); ?></div>
                        <h5 class="mb-1"><?php echo htmlspecialchars($candidate['full_name']); ?></h5>
                        <p class="candidate-meta mb-3"><?php echo htmlspecialchars(substr($candidate['manifesto'], 0, 100)); ?>...</p>
                        <a href="candidate_details.php?id=<?php echo $candidate['id']; ?>" class="btn btn-sm btn-outline-primary">Read Manifesto</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    
    <section class="container mt-5">
        <div class="cta-banner d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h5 class="mb-1">Past Results Archive</h5>
                <p class="text-muted mb-0">Access certified results from previous elections.</p>
            </div>
            <a href="results/2025" class="btn btn-outline-primary">View 2025 Election Results</a>
        </div>
    </section>
    
    <footer class="footer">
        <div class="container text-center">
            <img src="logo-n.png" alt="I.C.U.C University" class="brand-logo mb-3">
            <p class="mb-1">&copy; 2026 I.C.U.C University. All rights reserved.</p>
            <small>Verified by Electoral Commission | System Integrity: SHA-256 Protected</small>
        </div>
    </footer>
</body>
</html>