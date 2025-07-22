<?php
require_once __DIR__ . '/includes/db.php';

// Path to settings file
$settings_file = __DIR__ . '/config/settings.json';

// Load current settings
$settings = [
    'village_name' => 'My Village',
    'theme_color' => '#22c55e',
    'default_language' => 'en',
];
if (file_exists($settings_file)) {
    $json = file_get_contents($settings_file);
    $data = json_decode($json, true);
    if (is_array($data)) {
        $settings = array_merge($settings, $data);
    }
}

// Connect to DB
$db = (new Database())->getConnection();

// Check if any admin exists
$admin_exists = false;
try {
    $stmt = $db->query('SELECT COUNT(*) FROM admins');
    $admin_exists = $stmt->fetchColumn() > 0;
} catch (Exception $e) {
    $admin_exists = false;
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$admin_exists) {
    $username = trim($_POST['username'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $password = $_POST['password'] ?? '';
    $village_name = trim($_POST['village_name'] ?? $settings['village_name']);
    $theme_color = $_POST['theme_color'] ?? $settings['theme_color'];
    $default_language = $_POST['default_language'] ?? $settings['default_language'];

    if ($username && $name && $password && $village_name) {
        // Insert admin
        $stmt = $db->prepare('INSERT INTO admins (username, name, role, password_hash) VALUES (?, ?, ?, ?)');
        $stmt->execute([$username, $name, 'master', password_hash($password, PASSWORD_DEFAULT)]);
        // Update settings
        $settings['village_name'] = $village_name;
        $settings['theme_color'] = $theme_color;
        $settings['default_language'] = $default_language;
        file_put_contents($settings_file, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $success = true;
    } else {
        $error = 'All fields are required.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Initial Setup</title>
    <!-- Tailwind CSS removed -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-green-50 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded shadow max-w-md w-full">
        <h2 class="text-2xl font-bold mb-4 text-green-700">Initial Setup</h2>
        <?php if ($admin_exists): ?>
            <div class="mb-4 text-green-700 font-semibold">Setup is already complete. An admin exists.<br> <a href="admin/login.php" class="text-blue-600 underline">Go to Admin Login</a></div>
        <?php elseif ($success): ?>
            <div class="mb-4 text-green-700 font-semibold">Setup complete!<br> <a href="admin/login.php" class="text-blue-600 underline">Go to Admin Login</a></div>
        <?php else: ?>
            <?php if ($error): ?>
                <div class="mb-4 text-red-600"> <?= htmlspecialchars($error) ?> </div>
            <?php endif; ?>
            <form method="post" class="space-y-4">
                <div>
                    <label class="block font-semibold">Admin Username</label>
                    <input type="text" name="username" class="w-full border rounded px-2 py-1" required>
                </div>
                <div>
                    <label class="block font-semibold">Admin Name</label>
                    <input type="text" name="name" class="w-full border rounded px-2 py-1" required>
                </div>
                <div>
                    <label class="block font-semibold">Admin Password</label>
                    <input type="password" name="password" class="w-full border rounded px-2 py-1" required>
                </div>
                <hr>
                <div>
                    <label class="block font-semibold">Village Name</label>
                    <input type="text" name="village_name" class="w-full border rounded px-2 py-1" value="<?= htmlspecialchars($settings['village_name']) ?>" required>
                </div>
                <div>
                    <label class="block font-semibold">Theme Color</label>
                    <input type="color" name="theme_color" class="w-16 h-10 p-0 border rounded" value="<?= htmlspecialchars($settings['theme_color']) ?>">
                </div>
                <div>
                    <label class="block font-semibold">Default Language</label>
                    <select name="default_language" class="w-full border rounded px-2 py-1">
                        <option value="en" <?= $settings['default_language']=='en'?'selected':'' ?>>English</option>
                        <option value="hi" <?= $settings['default_language']=='hi'?'selected':'' ?>>हिन्दी</option>
                        <option value="gu" <?= $settings['default_language']=='gu'?'selected':'' ?>>ગુજરાતી</option>
                    </select>
                </div>
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded w-full">Complete Setup</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html> 