<?php
// Helper: Get translation for a key in the selected language
function t($key, $lang = 'en', $translations = []) {
    return $translations[$lang][$key] ?? $key;
}

// Helper: Format date for display
function format_date($date, $lang = 'en') {
    $dt = new DateTime($date);
    return $dt->format('d-m-Y');
}

// Helper: Log admin actions
function log_action($admin_id, $action, $details = '', $db) {
    $stmt = $db->prepare('INSERT INTO logs (admin_id, action, details) VALUES (?, ?, ?)');
    $stmt->execute([$admin_id, $action, $details]);
} 