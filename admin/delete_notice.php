<?php
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
if (!is_admin_logged_in()) {
    header('Location: login.php');
    exit;
}
$db = (new Database())->getConnection();
$admin_id = $_SESSION['admin_id'];
$is_master = is_master_admin();
$id = $_GET['id'] ?? 0;
if ($id) {
    // Check ownership
    $stmt = $db->prepare('SELECT * FROM notices WHERE id = ?');
    $stmt->execute([$id]);
    $notice = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($notice && ($is_master || $notice['admin_id'] == $admin_id)) {
        $db->prepare('UPDATE notices SET is_deleted = 1 WHERE id = ?')->execute([$id]);
        log_action($admin_id, 'delete_notice', $notice['title_en'], $db);
    }
}
header('Location: notices.php');
exit; 