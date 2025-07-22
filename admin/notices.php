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
// Handle search/filter
$where = 'n.is_deleted = 0';
$params = [];
if (!$is_master) {
    $where .= ' AND n.admin_id = ?';
    $params[] = $admin_id;
}
if (!empty($_GET['cat'])) {
    $where .= ' AND n.category_id = ?';
    $params[] = $_GET['cat'];
}
if (!empty($_GET['date'])) {
    $where .= ' AND DATE(n.published_at) = ?';
    $params[] = $_GET['date'];
}
$notices = $db->prepare("SELECT n.*, c.name_en, a.name as admin_name FROM notices n JOIN categories c ON n.category_id = c.id JOIN admins a ON n.admin_id = a.id WHERE $where ORDER BY n.published_at DESC");
$notices->execute($params);
$notices = $notices->fetchAll(PDO::FETCH_ASSOC);
// Fetch categories for filter
if ($is_master) {
    $categories = $db->query('SELECT * FROM categories WHERE is_active = 1')->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $db->prepare('SELECT category_id FROM admin_category_access WHERE admin_id=?');
    $stmt->execute([$admin_id]);
    $allowed = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'category_id');
    if (count($allowed) > 0) {
        $in = implode(',', array_fill(0, count($allowed), '?'));
        $cat_stmt = $db->prepare('SELECT * FROM categories WHERE is_active = 1 AND id IN (' . $in . ')');
        $cat_stmt->execute($allowed);
        $categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $categories = $db->query('SELECT * FROM categories WHERE is_active = 1')->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Notices</title>
    <!-- Tailwind CSS removed -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-green-50 min-h-screen">
    <style>
        :root {
            --theme-color: <?= htmlspecialchars(json_decode(file_get_contents('../config/settings.json'), true)['theme_color']) ?>;
            --theme-color-light: <?= htmlspecialchars(json_decode(file_get_contents('../config/settings.json'), true)['theme_color']) ?>20;
            --theme-color-dark: <?= htmlspecialchars(json_decode(file_get_contents('../config/settings.json'), true)['theme_color']) ?>;
        }
        .theme-bg { background-color: var(--theme-color-light); }
        .theme-text { color: var(--theme-color-dark); }
        .theme-btn { background-color: var(--theme-color); color: #fff; }
        .theme-btn:hover { filter: brightness(0.9); }
    </style>
    <div class="max-w-4xl mx-auto p-4">
        <div class="flex flex-col sm:flex-row justify-between items-center mb-4">
            <h1 class="text-2xl font-bold theme-text mb-2 sm:mb-0">Manage Notices</h1>
            <a href="dashboard.php" class="theme-text font-semibold">Back to Dashboard</a>
        </div>
        <!-- Search/Filter -->
        <form method="get" class="flex flex-wrap gap-2 mb-4 items-end">
            <div>
                <label class="block text-sm">Category</label>
                <select name="cat" class="border rounded px-2 py-1">
                    <option value="">All</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= (!empty($_GET['cat']) && $_GET['cat']==$cat['id'])?'selected':'' ?>><?= htmlspecialchars($cat['name_en']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm">Date</label>
                <input type="date" name="date" value="<?= htmlspecialchars($_GET['date'] ?? '') ?>" class="border rounded px-2 py-1">
            </div>
            <button type="submit" class="theme-btn px-4 py-1 rounded">Search</button>
            <a href="notices.php" class="text-sm text-gray-500 ml-2">Reset</a>
        </form>
        <!-- Notices Table -->
        <div class="overflow-x-auto">
        <table class="min-w-full bg-white rounded shadow">
            <thead>
                <tr class="theme-bg">
                    <th class="px-2 py-2">Title</th>
                    <th class="px-2 py-2">Category</th>
                    <th class="px-2 py-2">Published</th>
                    <th class="px-2 py-2">Expiry</th>
                    <th class="px-2 py-2">Admin</th>
                    <th class="px-2 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($notices as $notice): ?>
                <tr>
                    <td class="border px-2 py-1"> <?= htmlspecialchars($notice['title_en']) ?> </td>
                    <td class="border px-2 py-1"> <?= htmlspecialchars($notice['name_en']) ?> </td>
                    <td class="border px-2 py-1"> <?= format_date($notice['published_at']) ?> </td>
                    <td class="border px-2 py-1"> <?= format_date($notice['expiry_at']) ?> </td>
                    <td class="border px-2 py-1"> <?= htmlspecialchars($notice['admin_name']) ?> </td>
                    <td class="border px-2 py-1">
                        <a href="edit_notice.php?id=<?= $notice['id'] ?>" class="text-blue-600">Edit</a> |
                        <a href="delete_notice.php?id=<?= $notice['id'] ?>" class="text-red-600" onclick="return confirm('Delete this notice?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($notices)): ?>
                <tr><td colspan="6" class="text-center text-gray-400 py-4">No notices found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
        </div>
        <div class="mt-4">
            <a href="add_notice.php" class="theme-btn px-4 py-2 rounded">Add Notice</a>
        </div>
    </div>
</body>
</html> 