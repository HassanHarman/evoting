<?php
$resolvedPageTitle = isset($pageTitle) ? $pageTitle : 'Admin Dashboard';
$resolvedPageSubtitle = isset($pageSubtitle) ? $pageSubtitle : '';
$currentPage = basename($_SERVER['PHP_SELF']);
$adminName = isset($_SESSION['admin_name']) && $_SESSION['admin_name'] ? $_SESSION['admin_name'] : 'Admin User';
$adminRole = isset($_SESSION['admin_role']) && $_SESSION['admin_role'] ? ucwords(str_replace('_', ' ', $_SESSION['admin_role'])) : 'Administrator';
$initials = '';
foreach (explode(' ', trim($adminName)) as $part) {
    if ($part !== '') {
        $initials .= strtoupper($part[0]);
    }
}
$initials = $initials !== '' ? substr($initials, 0, 2) : 'AD';
$navItems = [
    'index.php' => ['icon' => 'fa-solid fa-chart-pie', 'label' => 'Dashboard'],
    'candidates.php' => ['icon' => 'fa-solid fa-users', 'label' => 'Candidates'],
    'categories.php' => ['icon' => 'fa-solid fa-layer-group', 'label' => 'Categories'],
    'students.php' => ['icon' => 'fa-solid fa-user-graduate', 'label' => 'Students'],
    'results.php' => ['icon' => 'fa-solid fa-trophy', 'label' => 'Results'],
    'audit.php' => ['icon' => 'fa-solid fa-clipboard-list', 'label' => 'Audit Log'],
    'settings.php' => ['icon' => 'fa-solid fa-gear', 'label' => 'Settings'],
    'close_election.php' => ['icon' => 'fa-solid fa-lock', 'label' => 'Close Election'],
    'live_results.php' => ['icon' => 'fa-solid fa-signal', 'label' => 'Live Results'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($resolvedPageTitle); ?> - I.C.U.C Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="assets/admin-ui.css" rel="stylesheet">
    <?php if (isset($pageHeadExtras)) { echo $pageHeadExtras; } ?>
</head>
<body class="admin-body">
    <div class="sidebar-overlay" onclick="document.body.classList.remove('sidebar-open')"></div>
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <div class="admin-brand">
                <img src="../logo-n.png" alt="ICUC Logo">
                <div>
                    <div class="admin-brand-title">I.C.U.C. Admin</div>
                    <div class="admin-brand-subtitle">Electoral Commission</div>
                </div>
            </div>
            <nav class="admin-nav">
                <?php foreach ($navItems as $link => $item): ?>
                    <a href="<?php echo $link; ?>" class="<?php echo $currentPage === $link ? 'active' : ''; ?>">
                        <i class="<?php echo $item['icon']; ?>"></i>
                        <span><?php echo $item['label']; ?></span>
                    </a>
                <?php endforeach; ?>
                <a href="../logout.php">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Logout</span>
                </a>
            </nav>
            <div class="admin-sidebar-footer">
                Secure Admin Panel · <?php echo date('Y'); ?>
            </div>
        </aside>
        <main class="admin-main">
            <header class="admin-topbar">
                <div class="topbar-left">
                    <button class="sidebar-toggle" type="button" onclick="document.body.classList.toggle('sidebar-open')">☰</button>
                    <div>
                        <div class="page-title"><?php echo htmlspecialchars($resolvedPageTitle); ?></div>
                        <?php if ($resolvedPageSubtitle): ?>
                            <div class="page-subtitle"><?php echo htmlspecialchars($resolvedPageSubtitle); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="topbar-right">
                    <?php if (isset($topbarActions)) { echo $topbarActions; } ?>
                    <div class="admin-user-card">
                        <div class="admin-user-avatar"><?php echo htmlspecialchars($initials); ?></div>
                        <div>
                            <div class="admin-user-name"><?php echo htmlspecialchars($adminName); ?></div>
                            <div class="admin-user-role"><?php echo htmlspecialchars($adminRole); ?></div>
                        </div>
                    </div>
                </div>
            </header>
            <section class="admin-content">
