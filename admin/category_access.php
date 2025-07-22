<?php
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
if (!is_master_admin()) {
    header('Location: dashboard.php');
    exit;
}
$db = (new Database())->getConnection();
$admin_id = $_GET['admin_id'] ?? 0;
// Fetch admin
$stmt = $db->prepare('SELECT * FROM admins WHERE id=? AND role="regular"');
$stmt->execute([$admin_id]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$admin) {
    echo "Invalid admin.";
    exit;
}
// Fetch all categories
$categories = $db->query('SELECT * FROM categories WHERE is_active = 1')->fetchAll(PDO::FETCH_ASSOC);
// Fetch assigned categories
$stmt = $db->prepare('SELECT category_id FROM admin_category_access WHERE admin_id=?');
$stmt->execute([$admin_id]);
$assigned = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'category_id');
$error = '';
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected = $_POST['categories'] ?? [];
    // Remove all current
    $db->prepare('DELETE FROM admin_category_access WHERE admin_id=?')->execute([$admin_id]);
    // Insert new
    foreach ($selected as $cat_id) {
        $db->prepare('INSERT INTO admin_category_access (admin_id, category_id) VALUES (?, ?)')->execute([$admin_id, $cat_id]);
    }
    $success = true;
    $assigned = $selected;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Category Access</title>
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
    <script>
    function selectAll() {
        const sel = document.getElementById('cat-select');
        for (let i = 0; i < sel.options.length; i++) sel.options[i].selected = true;
    }
    function deselectAll() {
        const sel = document.getElementById('cat-select');
        for (let i = 0; i < sel.options.length; i++) sel.options[i].selected = false;
    }
    </script>
</head>
<body class="theme-bg min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded shadow max-w-md w-full">
        <h2 class="text-2xl font-bold mb-4 theme-text">Assign Category Access</h2>
        <div class="mb-2 text-gray-700 font-semibold">Admin: <?= htmlspecialchars($admin['name']) ?> (<?= htmlspecialchars($admin['username']) ?>)</div>
        <?php if ($success): ?>
            <div class="mb-4 theme-text">Access updated!</div>
        <?php endif; ?>
        <form method="post" class="space-y-4">
            <div>
                <label class="block font-semibold mb-1">Allowed Categories</label>
                <div class="mb-2 flex gap-2">
                    <button type="button" onclick="selectAll()" class="px-2 py-1 theme-bg rounded">Select All</button>
                    <button type="button" onclick="deselectAll()" class="px-2 py-1 bg-gray-200 rounded">Deselect All</button>
                </div>
                <select id="cat-select" name="categories[]" multiple class="w-full border rounded px-2 py-1 h-40">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= in_array($cat['id'], $assigned) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name_en']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="text-xs text-gray-500 mt-1">If none selected, admin can post in all categories.</div>
            </div>
            <button type="submit" class="theme-btn px-4 py-2 rounded">Save Access</button>
            <a href="admins.php" class="ml-4 theme-text">Back</a>
        </form>
    </div>
</body>
</html> 