<?php
// candidates_list.php
define('ACCESS_ALLOWED', true);
require_once 'config/database.php';

// Get all active candidates grouped by category
$stmt = $pdo->query("
    SELECT c.*, cat.category_name, cat.sort_order 
    FROM candidates c 
    JOIN categories cat ON c.category_id = cat.id 
    WHERE c.is_active = 1 AND cat.is_active = 1
    ORDER BY cat.sort_order, c.full_name
");
$candidates = $stmt->fetchAll();

// Group by category
$grouped = [];
foreach($candidates as $candidate) {
    if(!isset($grouped[$candidate['category_id']])) {
        $grouped[$candidate['category_id']] = [
            'category_name' => $candidate['category_name'],
            'candidates' => []
        ];
    }
    $grouped[$candidate['category_id']]['candidates'][] = $candidate;
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
    <title>All Candidates - I.C.U.C Election 2026</title>
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

        .hero-header {
            background: linear-gradient(135deg, rgba(15, 118, 110, 0.14), rgba(245, 158, 11, 0.2));
            border-radius: 22px;
            padding: 30px;
            box-shadow: var(--shadow-soft);
            border: 1px solid rgba(15, 23, 42, 0.06);
        }

        .category-section {
            margin-bottom: 50px;
        }

        .category-title {
            background: #0f172a;
            color: white;
            padding: 18px 22px;
            border-radius: 16px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .candidate-card {
            background: var(--surface);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: var(--shadow-soft);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
            cursor: pointer;
            border: 1px solid rgba(15, 23, 42, 0.05);
        }

        .candidate-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow);
        }

        .candidate-img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            background: #e2e8f0;
        }

        .candidate-info {
            padding: 20px;
        }

        .candidate-name {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .party-badge {
            font-size: 11px;
            padding: 6px 12px;
            border-radius: 999px;
        }

        .btn-view {
            border-radius: 999px;
            padding: 6px 16px;
            font-size: 13px;
        }

        .search-box {
            margin-bottom: 30px;
        }

        .search-input {
            border-radius: 999px;
            padding: 14px 18px;
            border: 1px solid rgba(15, 23, 42, 0.08);
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

        .footer {
            color: var(--muted);
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
                <?php if($election_status == 'active'): ?>
                    <a href="login.php" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Login to Vote
                    </a>
                <?php else: ?>
                    <a href="index.php" class="btn btn-outline-light">
                        <i class="fas fa-home"></i> Home
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <div class="container mt-4">
        <!-- Header -->
        <div class="hero-header mb-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h1 class="mb-2">2026 Election Candidates</h1>
                    <p class="lead mb-0">Meet your student leaders. Review their manifestos before voting.</p>
                </div>
                <?php if($election_status == 'active'): ?>
                    <span class="badge bg-success">Election Active</span>
                <?php elseif($election_status == 'upcoming'): ?>
                    <span class="badge bg-warning text-dark">Election Starts Soon</span>
                <?php elseif($election_status == 'closed'): ?>
                    <span class="badge bg-secondary">Election Closed</span>
                <?php endif; ?>
            </div>
            <div class="mt-3">
                <?php if($election_status == 'active'): ?>
                    <small class="text-success"><i class="fas fa-check-circle"></i> Election is currently ACTIVE. <a href="login.php" class="text-decoration-none">Login to cast your vote →</a></small>
                <?php elseif($election_status == 'upcoming'): ?>
                    <small class="text-warning"><i class="fas fa-clock"></i> Election starts soon. Get ready to vote!</small>
                <?php elseif($election_status == 'closed'): ?>
                    <small class="text-muted"><i class="fas fa-flag-checkered"></i> Election has ended. <a href="results/public.php" class="text-decoration-none">View Results →</a></small>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Search and Filter -->
        <div class="row search-box">
            <div class="col-md-8 mx-auto">
                <div class="input-group">
                    <input type="text" class="form-control search-input" id="searchInput" placeholder="Search candidates by name, position, or party...">
                    <button class="btn btn-primary" onclick="searchCandidates()">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Candidates by Category -->
        <div id="candidatesContainer">
            <?php foreach($grouped as $category_id => $group): ?>
                <div class="category-section" data-category="<?php echo strtolower($group['category_name']); ?>">
                    <div class="category-title">
                        <h3 class="mb-0"><i class="fas fa-trophy"></i> <?php echo htmlspecialchars($group['category_name']); ?></h3>
                        <small><?php echo count($group['candidates']); ?> candidate(s) running</small>
                    </div>
                    
                    <div class="row">
                        <?php foreach($group['candidates'] as $candidate): ?>
                            <div class="col-md-6 col-lg-4 mb-4 candidate-item" 
                                 data-name="<?php echo strtolower($candidate['full_name']); ?>"
                                 data-party="<?php echo strtolower($candidate['party_affiliation']); ?>">
                                <div class="candidate-card" onclick="location.href='candidate_details.php?id=<?php echo $candidate['id']; ?>'">
                                    <?php if($candidate['photo'] && file_exists($candidate['photo'])): ?>
                                        <img src="<?php echo $candidate['photo']; ?>" class="candidate-img" alt="<?php echo $candidate['full_name']; ?>">
                                    <?php else: ?>
                                        <div class="candidate-img d-flex align-items-center justify-content-center bg-secondary text-white">
                                            <i class="fas fa-user-circle fa-5x"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="candidate-info">
                                        <div class="candidate-name"><?php echo htmlspecialchars($candidate['full_name']); ?></div>
                                        <?php if($candidate['party_affiliation']): ?>
                                            <span class="party-badge badge bg-info"><?php echo htmlspecialchars($candidate['party_affiliation']); ?></span>
                                        <?php endif; ?>
                                        <?php if($candidate['is_independent']): ?>
                                            <span class="party-badge badge bg-warning">Independent</span>
                                        <?php endif; ?>
                                        <p class="mt-2 small text-muted">
                                            <?php echo htmlspecialchars(substr($candidate['manifesto'] ?? 'No manifesto uploaded yet.', 0, 100)); ?>...
                                        </p>
                                        <button class="btn btn-view btn-sm btn-outline-primary mt-2">
                                            View Full Profile →
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <?php if(empty($grouped)): ?>
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle"></i> No candidates have been nominated yet. Check back soon!
            </div>
        <?php endif; ?>
        
        <!-- Call to Action -->
        <div class="text-center mt-5 mb-4">
            <div class="hero-header">
                <h5>Ready to vote?</h5>
                <p class="text-muted">Your vote determines the future of student leadership at I.C.U.C.</p>
                <?php if($election_status == 'active'): ?>
                    <a href="login.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-vote-yea"></i> Vote Now
                    </a>
                <?php else: ?>
                    <button class="btn btn-secondary btn-lg" disabled>
                        <i class="fas fa-clock"></i> Voting Not Open Yet
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <footer class="text-center mt-4 mb-3 footer">
        <small>© 2026 I.C.U.C University - Electronic Voting System | Secure • Transparent • Fair</small>
    </footer>
    
    <script>
        function searchCandidates() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const candidates = document.querySelectorAll('.candidate-item');
            
            candidates.forEach(candidate => {
                const name = candidate.dataset.name || '';
                const party = candidate.dataset.party || '';
                const category = candidate.closest('.category-section').dataset.category || '';
                
                if(name.includes(searchTerm) || party.includes(searchTerm) || category.includes(searchTerm)) {
                    candidate.style.display = '';
                } else {
                    candidate.style.display = 'none';
                }
            });
            
            // Show/hide empty categories
            document.querySelectorAll('.category-section').forEach(section => {
                const visibleItems = section.querySelectorAll('.candidate-item[style="display: block;"], .candidate-item:not([style*="display: none"])');
                if(visibleItems.length === 0 && section.querySelectorAll('.candidate-item').length > 0) {
                    section.style.display = 'none';
                } else {
                    section.style.display = '';
                }
            });
        }
        
        // Allow Enter key to trigger search
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if(e.key === 'Enter') {
                searchCandidates();
            }
        });
    </script>
</body>
</html>