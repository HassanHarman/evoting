<?php
// includes/security.php
function generateVotingToken($reg_no, $candidate_id, $category_id) {
    return hash('sha256', $reg_no . $candidate_id . $category_id . date('Y-m-d') . SALT);
}

function preventSQLInjection($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function rateLimit($ip, $limit = 10, $timeframe = 60) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_audit WHERE ip_address = ? AND action_timestamp > DATE_SUB(NOW(), INTERVAL ? SECOND)");
    $stmt->execute([$ip, $timeframe]);
    return $stmt->fetchColumn() >= $limit;
}

function logAdminAction($admin, $action, $table, $id, $old_val, $new_val) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO admin_audit (admin_username, action_type, target_table, target_id, old_value, new_value, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$admin, $action, $table, $id, $old_val, $new_val, $_SERVER['REMOTE_ADDR']]);
}
?>
