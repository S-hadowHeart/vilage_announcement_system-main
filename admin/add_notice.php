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
// Fetch categories
if ($is_master) {
    $categories = $db->query('SELECT * FROM categories WHERE is_active = 1')->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Check for category access restriction
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
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title_en = $_POST['title_en'] ?? '';
    $title_hi = $_POST['title_hi'] ?? '';
    $title_gu = $_POST['title_gu'] ?? '';
    $desc_en = $_POST['description_en'] ?? '';
    $desc_hi = $_POST['description_hi'] ?? '';
    $desc_gu = $_POST['description_gu'] ?? '';
    $category_id = $_POST['category_id'] ?? '';
    $published_at = $_POST['published_at'] ?? date('Y-m-d');
    $expiry_at = $_POST['expiry_at'] ?? date('Y-m-d', strtotime('+30 days'));
    if ($title_en && $desc_en && $category_id) {
        $stmt = $db->prepare('INSERT INTO notices (title_en, title_hi, title_gu, description_en, description_hi, description_gu, category_id, admin_id, published_at, expiry_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$title_en, $title_hi, $title_gu, $desc_en, $desc_hi, $desc_gu, $category_id, $admin_id, $published_at, $expiry_at]);
        log_action($admin_id, 'add_notice', $title_en, $db);
        header('Location: notices.php');
        exit;
    } else {
        $error = 'Please fill all required fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Notice</title>
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
    <div class="max-w-xl mx-auto p-4">
        <h1 class="text-2xl font-bold theme-text mb-4">Add Notice</h1>
        <?php if ($error): ?>
            <div class="mb-4 text-red-600"> <?= htmlspecialchars($error) ?> </div>
        <?php endif; ?>
        <form method="post" class="space-y-4 bg-white p-4 rounded shadow">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                <div>
                    <label class="block font-semibold">Title (EN)</label>
                    <input type="text" name="title_en" class="w-full border rounded px-2 py-1" required>
                </div>
                <div>
                    <label class="block font-semibold">Title (HI)</label>
                    <input type="text" name="title_hi" class="w-full border rounded px-2 py-1">
                </div>
                <div>
                    <label class="block font-semibold">Title (GU)</label>
                    <input type="text" name="title_gu" class="w-full border rounded px-2 py-1">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                <div>
                    <label class="block font-semibold">Description (EN)</label>
                    <textarea name="description_en" class="w-full border rounded px-2 py-1" required></textarea>
                </div>
                <div>
                    <label class="block font-semibold">Description (HI)</label>
                    <textarea name="description_hi" class="w-full border rounded px-2 py-1"></textarea>
                </div>
                <div>
                    <label class="block font-semibold">Description (GU)</label>
                    <textarea name="description_gu" class="w-full border rounded px-2 py-1"></textarea>
                </div>
            </div>
            <div>
                <label class="block font-semibold">Category</label>
                <select name="category_id" class="w-full border rounded px-2 py-1" required>
                    <option value="">Select</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"> <?= htmlspecialchars($cat['name_en']) ?> </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block font-semibold">Publish Date</label>
                    <input type="date" name="published_at" class="w-full border rounded px-2 py-1" value="<?= date('Y-m-d') ?>">
                </div>
                <div>
                    <label class="block font-semibold">Expiry Date</label>
                    <input type="date" name="expiry_at" class="w-full border rounded px-2 py-1" value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                </div>
            </div>
            <button type="submit" class="theme-btn px-4 py-2 rounded">Add Notice</button>
            <a href="notices.php" class="ml-4 theme-text">Cancel</a>
        </form>
    </div>
</body>
</html> 