<?php
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
if (!is_admin_logged_in()) {
    header('Location: login.php');
    exit;
}
$db = (new Database())->getConnection();
// Count active, scheduled, expired notices
$now = date('Y-m-d H:i:s');
$active = $db->query("SELECT COUNT(*) FROM notices WHERE is_deleted = 0 AND published_at <= '$now' AND expiry_at > '$now'")->fetchColumn();
$scheduled = $db->query("SELECT COUNT(*) FROM notices WHERE is_deleted = 0 AND published_at > '$now'")->fetchColumn();
$expired = $db->query("SELECT COUNT(*) FROM notices WHERE is_deleted = 0 AND expiry_at <= '$now'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Tailwind CSS removed -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <?php $settings = json_decode(file_get_contents('../config/settings.json'), true); ?>
    <style>
        :root {
            --theme-color: <?= htmlspecialchars($settings['theme_color']) ?>;
            --theme-color-light: <?= htmlspecialchars($settings['theme_color']) ?>20;
            --theme-color-dark: <?= htmlspecialchars($settings['theme_color']) ?>;
        }
        .theme-bg { background-color: var(--theme-color-light); }
        .theme-text { color: var(--theme-color-dark); }
        .theme-btn { background-color: var(--theme-color); color: #fff; }
        .theme-btn:hover { filter: brightness(0.9); }
    </style>
</head>
<body class="theme-bg min-h-screen">
    <div class="max-w-3xl mx-auto p-4">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold theme-text">Admin Dashboard</h1>
            <a href="logout.php" class="text-red-600 font-semibold">Logout</a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
            <div class="bg-white rounded shadow p-4 text-center">
                <div class="text-3xl font-bold theme-text"><?= $active ?></div>
                <div class="text-gray-600">Active Notices</div>
            </div>
            <div class="bg-white rounded shadow p-4 text-center">
                <div class="text-3xl font-bold text-blue-600"><?= $scheduled ?></div>
                <div class="text-gray-600">Scheduled Notices</div>
            </div>
            <div class="bg-white rounded shadow p-4 text-center">
                <div class="text-3xl font-bold text-gray-500"><?= $expired ?></div>
                <div class="text-gray-600">Expired Notices</div>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <a href="notices.php" class="block theme-btn rounded p-4 text-center font-bold">Manage Notices</a>
            <a href="categories.php" class="block theme-btn rounded p-4 text-center font-bold">Manage Categories</a>
            <a href="settings.php" class="block theme-btn rounded p-4 text-center font-bold">Site Settings</a>
            <?php if (is_master_admin()): ?>
            <a href="admins.php" class="block theme-btn rounded p-4 text-center font-bold">Manage Admins</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 