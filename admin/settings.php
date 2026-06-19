
<?php
define('ACCESS_ALLOWED', true);
require_once '../config/database.php';

if (!isset($_SESSION['admin_logged'])) {
    header("Location: login.php");
    exit();
}

$pageTitle = 'Settings';
$pageSubtitle = 'Configure election parameters and portal preferences.';
include 'partials/admin_header.php';
?>

<div class="card-panel">
    <div class="card-header-row">
        <div>
            <h3>Election Configuration</h3>
            <p>Update voting windows, publishing rules, and system preferences.</p>
        </div>
        <span class="badge-soft">Settings</span>
    </div>
    <?php
    $stmt = $pdo->query("SELECT setting_key, setting_value, description, updated_at
        FROM election_settings
        ORDER BY setting_key
    ");
    $settings = $stmt->fetchAll();
    ?>
    <?php if (empty($settings)): ?>
        <div class="empty-state">
            <div class="empty-icon">⚙️</div>
            <h4>No settings available.</h4>
            <p>Election configuration values will appear here when defined.</p>
        </div>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Key</th>
                    <th>Value</th>
                    <th>Description</th>
                    <th>Updated</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($settings as $setting): ?>
                    <tr>
                        <td><code><?php echo htmlspecialchars($setting['setting_key']); ?></code></td>
                        <td><?php echo htmlspecialchars($setting['setting_value'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($setting['description'] ?? ''); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($setting['updated_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include 'partials/admin_footer.php'; ?>

