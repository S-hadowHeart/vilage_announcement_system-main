<?php
require_once 'db.php';
require_once 'helpers.php';
$db = (new Database())->getConnection();
// Find notices expired more than 30 days ago
$cutoff = date('Y-m-d H:i:s', strtotime('-30 days'));
$stmt = $db->prepare('SELECT * FROM notices WHERE expiry_at < ? AND is_deleted = 0');
$stmt->execute([$cutoff]);
$expired = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($expired as $notice) {
    $db->prepare('DELETE FROM notices WHERE id = ?')->execute([$notice['id']]);
    log_action($notice['admin_id'], 'auto_delete_expired', $notice['title_en'], $db);
} 