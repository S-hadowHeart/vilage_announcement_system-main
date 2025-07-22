<?php
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
if (!is_admin_logged_in()) {
    header('Location: login.php');
    exit;
}
$db = (new Database())->getConnection();
$is_master = is_master_admin();
// Handle add/edit
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name_en = $_POST['name_en'] ?? '';
    $name_hi = $_POST['name_hi'] ?? '';
    $name_gu = $_POST['name_gu'] ?? '';
    $icon = $_POST['icon'] ?? '';
    if ($name_en) {
        if (!empty($_POST['id'])) {
            // Edit
            $stmt = $db->prepare('UPDATE categories SET name_en=?, name_hi=?, name_gu=?, icon=? WHERE id=?');
            $stmt->execute([$name_en, $name_hi, $name_gu, $icon, $_POST['id']]);
        } else {
            // Add
            $stmt = $db->prepare('INSERT INTO categories (name_en, name_hi, name_gu, icon) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name_en, $name_hi, $name_gu, $icon]);
        }
        header('Location: categories.php');
        exit;
    } else {
        $error = 'Category name (EN) is required.';
    }
}
// Handle delete (soft)
if ($is_master && isset($_GET['delete'])) {
    $db->prepare('UPDATE categories SET is_active=0 WHERE id=?')->execute([$_GET['delete']]);
    header('Location: categories.php');
    exit;
}
// Fetch categories
$categories = $db->query('SELECT * FROM categories WHERE is_active = 1')->fetchAll(PDO::FETCH_ASSOC);
// For edit form
$edit_cat = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare('SELECT * FROM categories WHERE id=?');
    $stmt->execute([$_GET['edit']]);
    $edit_cat = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories</title>
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
    <div class="max-w-2xl mx-auto p-4">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold theme-text">Manage Categories</h1>
            <a href="dashboard.php" class="theme-text font-semibold">Back to Dashboard</a>
        </div>
        <!-- Add/Edit Category Form -->
        <div class="bg-white p-4 rounded shadow mb-6">
            <h2 class="font-bold mb-2"> <?= $edit_cat ? 'Edit' : 'Add' ?> Category </h2>
            <?php if ($error): ?>
                <div class="mb-2 text-red-600"> <?= htmlspecialchars($error) ?> </div>
            <?php endif; ?>
            <form method="post" class="grid grid-cols-1 sm:grid-cols-4 gap-2 items-end">
                <input type="hidden" name="id" value="<?= $edit_cat['id'] ?? '' ?>">
                <div>
                    <label class="block text-sm">Name (EN)</label>
                    <input type="text" name="name_en" class="w-full border rounded px-2 py-1" value="<?= htmlspecialchars($edit_cat['name_en'] ?? '') ?>" required>
                </div>
                <div>
                    <label class="block text-sm">Name (HI)</label>
                    <input type="text" name="name_hi" class="w-full border rounded px-2 py-1" value="<?= htmlspecialchars($edit_cat['name_hi'] ?? '') ?>">
                </div>
                <div>
                    <label class="block text-sm">Name (GU)</label>
                    <input type="text" name="name_gu" class="w-full border rounded px-2 py-1" value="<?= htmlspecialchars($edit_cat['name_gu'] ?? '') ?>">
                </div>
                <button type="submit" class="theme-btn px-4 py-2 rounded col-span-1 sm:col-span-4">Save</button>
            </form>
        </div>
        <!-- Categories Table -->
        <table class="min-w-full bg-white rounded shadow">
            <thead>
                <tr class="theme-bg">
                    <th class="px-2 py-2">Name (EN)</th>
                    <th class="px-2 py-2">Name (HI)</th>
                    <th class="px-2 py-2">Name (GU)</th>
                    <th class="px-2 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($categories as $cat): ?>
                <tr>
                    <td class="border px-2 py-1"> <?= htmlspecialchars($cat['name_en']) ?> </td>
                    <td class="border px-2 py-1"> <?= htmlspecialchars($cat['name_hi']) ?> </td>
                    <td class="border px-2 py-1"> <?= htmlspecialchars($cat['name_gu']) ?> </td>
                    <td class="border px-2 py-1">
                        <a href="categories.php?edit=<?= $cat['id'] ?>" class="text-blue-600">Edit</a>
                        <?php if ($is_master): ?> |
                        <a href="categories.php?delete=<?= $cat['id'] ?>" class="text-red-600" onclick="return confirm('Delete this category?')">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($categories)): ?>
                <tr><td colspan="5" class="text-center text-gray-400 py-4">No categories found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html> 