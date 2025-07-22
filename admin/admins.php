<?php
require_once '../includes/auth.php';
require_once '../includes/helpers.php';
if (!is_master_admin()) {
    header('Location: dashboard.php');
    exit;
}
$db = (new Database())->getConnection();
$error = '';
// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $name = $_POST['name'] ?? '';
    $role = $_POST['role'] ?? 'regular';
    $password = $_POST['password'] ?? '';
    if ($username && $name && ($password || !empty($_POST['id']))) {
        if (!empty($_POST['id'])) {
            // Edit
            if ($password) {
                $stmt = $db->prepare('UPDATE admins SET username=?, name=?, role=?, password_hash=? WHERE id=?');
                $stmt->execute([$username, $name, $role, password_hash($password, PASSWORD_DEFAULT), $_POST['id']]);
            } else {
                $stmt = $db->prepare('UPDATE admins SET username=?, name=?, role=? WHERE id=?');
                $stmt->execute([$username, $name, $role, $_POST['id']]);
            }
        } else {
            // Add
            $stmt = $db->prepare('INSERT INTO admins (username, name, role, password_hash) VALUES (?, ?, ?, ?)');
            $stmt->execute([$username, $name, $role, password_hash($password, PASSWORD_DEFAULT)]);
        }
        header('Location: admins.php');
        exit;
    } else {
        $error = 'All fields are required (password only for new admin or to change).';
    }
}
// Handle activate/deactivate
if (isset($_GET['toggle'])) {
    $stmt = $db->prepare('UPDATE admins SET is_active = NOT is_active WHERE id=?');
    $stmt->execute([$_GET['toggle']]);
    header('Location: admins.php');
    exit;
}
// Handle delete
if (isset($_GET['delete'])) {
    $db->prepare('DELETE FROM admins WHERE id=?')->execute([$_GET['delete']]);
    header('Location: admins.php');
    exit;
}
// Fetch admins
$admins = $db->query('SELECT * FROM admins')->fetchAll(PDO::FETCH_ASSOC);
// For edit form
$edit_admin = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare('SELECT * FROM admins WHERE id=?');
    $stmt->execute([$_GET['edit']]);
    $edit_admin = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins</title>
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
            <h1 class="text-2xl font-bold theme-text">Manage Admins</h1>
            <a href="dashboard.php" class="theme-text font-semibold">Back to Dashboard</a>
        </div>
        <!-- Add/Edit Admin Form -->
        <div class="bg-white p-4 rounded shadow mb-6">
            <h2 class="font-bold mb-2"> <?= $edit_admin ? 'Edit' : 'Add' ?> Admin </h2>
            <?php if ($error): ?>
                <div class="mb-2 text-red-600"> <?= htmlspecialchars($error) ?> </div>
            <?php endif; ?>
            <form method="post" class="grid grid-cols-1 sm:grid-cols-4 gap-2 items-end">
                <input type="hidden" name="id" value="<?= $edit_admin['id'] ?? '' ?>">
                <div>
                    <label class="block text-sm">Username</label>
                    <input type="text" name="username" class="w-full border rounded px-2 py-1" value="<?= htmlspecialchars($edit_admin['username'] ?? '') ?>" required>
                </div>
                <div>
                    <label class="block text-sm">Name</label>
                    <input type="text" name="name" class="w-full border rounded px-2 py-1" value="<?= htmlspecialchars($edit_admin['name'] ?? '') ?>" required>
                </div>
                <div>
                    <label class="block text-sm">Role</label>
                    <select name="role" class="w-full border rounded px-2 py-1">
                        <option value="regular" <?= ($edit_admin['role'] ?? '')=='regular'?'selected':'' ?>>Regular</option>
                        <option value="master" <?= ($edit_admin['role'] ?? '')=='master'?'selected':'' ?>>Master</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm">Password <?= $edit_admin ? '(leave blank to keep)' : '' ?></label>
                    <input type="password" name="password" class="w-full border rounded px-2 py-1">
                </div>
                <button type="submit" class="theme-btn px-4 py-2 rounded col-span-1 sm:col-span-4">Save</button>
            </form>
        </div>
        <!-- Admins Table -->
        <table class="min-w-full bg-white rounded shadow">
            <thead>
                <tr class="theme-bg">
                    <th class="px-2 py-2">Username</th>
                    <th class="px-2 py-2">Name</th>
                    <th class="px-2 py-2">Role</th>
                    <th class="px-2 py-2">Status</th>
                    <th class="px-2 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($admins as $admin): ?>
                <tr>
                    <td class="border px-2 py-1"> <?= htmlspecialchars($admin['username']) ?> </td>
                    <td class="border px-2 py-1"> <?= htmlspecialchars($admin['name']) ?> </td>
                    <td class="border px-2 py-1"> <?= htmlspecialchars(ucfirst($admin['role'])) ?> </td>
                    <td class="border px-2 py-1"> <?= $admin['is_active'] ? 'Active' : 'Inactive' ?> </td>
                    <td class="border px-2 py-1">
                        <a href="admins.php?edit=<?= $admin['id'] ?>" class="text-blue-600">Edit</a> |
                        <a href="admins.php?toggle=<?= $admin['id'] ?>" class="text-yellow-600">Toggle</a> |
                        <a href="admins.php?delete=<?= $admin['id'] ?>" class="text-red-600" onclick="return confirm('Delete this admin?')">Delete</a>
                        <?php if ($admin['role'] === 'regular'): ?> |
                            <a href="category_access.php?admin_id=<?= $admin['id'] ?>" class="theme-text">Category Access</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($admins)): ?>
                <tr><td colspan="5" class="text-center text-gray-400 py-4">No admins found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html> 