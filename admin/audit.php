<?php
define('ACCESS_ALLOWED', true);
require_once '../config/database.php';

if (!isset($_SESSION['admin_logged'])) {
    header("Location: login.php");
    exit();
}

$pageTitle = 'Audit Log';
$pageSubtitle = 'Monitor sensitive actions and system access.';
include 'partials/admin_header.php';
?>

<div class="card-panel">
    <div class="card-header-row">
        <div>
            <h3>Security & Activity Log</h3>
            <p>Track administrative actions and review security events.</p>
        </div>
        <span class="badge-soft">Audit trail</span>
    </div>
    <?php
    $stmt = $pdo->query("SELECT admin_username, action_type, target_table, target_id, ip_address, action_timestamp
        FROM admin_audit
        ORDER BY action_timestamp DESC
        LIMIT 50
    ");
    $audits = $stmt->fetchAll();
    ?>
    <?php if (empty($audits)): ?>
        <div class="empty-state">
            <div class="empty-icon">🧾</div>
            <h4>No audit entries yet.</h4>
            <p>System activity will appear here when actions are logged.</p>
        </div>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Admin/User</th>
                    <th>Action</th>
                    <th>Target</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($audits as $audit): ?>
                    <tr>
                        <td><?php echo date('Y-m-d H:i', strtotime($audit['action_timestamp'])); ?></td>
                        <td><?php echo htmlspecialchars($audit['admin_username']); ?></td>
                        <td><?php echo htmlspecialchars($audit['action_type']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($audit['target_table'] ?? ''); ?>
                            <?php if (!empty($audit['target_id'])): ?>
                                #<?php echo (int)$audit['target_id']; ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($audit['ip_address'] ?? ''); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include 'partials/admin_footer.php'; ?>
