<?php
require_once '../includes/auth.php';
if (!is_master_admin()) {
    header('Location: dashboard.php');
    exit;
}
$settings_file = '../config/settings.json';
$settings = json_decode(file_get_contents($settings_file), true);
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings['village_name'] = $_POST['village_name'] ?? $settings['village_name'];
    $settings['theme_color'] = $_POST['theme_color'] ?? $settings['theme_color'];
    $settings['default_language'] = $_POST['default_language'] ?? $settings['default_language'];
    if ($settings['village_name']) {
        file_put_contents($settings_file, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        header('Location: settings.php?success=1');
        exit;
    } else {
        $error = 'Village name is required.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Settings</title>
    <!-- Tailwind CSS removed -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-green-50 min-h-screen">
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
    <div class="max-w-lg mx-auto p-4">
        <div class="flex flex-col sm:flex-row justify-between items-center mb-4">
            <h1 class="text-2xl font-bold theme-text mb-2 sm:mb-0">Site Settings</h1>
            <a href="dashboard.php" class="theme-text font-semibold">Back to Dashboard</a>
        </div>
        <?php if ($error): ?>
            <div class="mb-4 text-red-600"> <?= htmlspecialchars($error) ?> </div>
        <?php elseif (isset($_GET['success'])): ?>
            <div class="mb-4 theme-text">Settings updated successfully.</div>
        <?php endif; ?>
        <form method="post" class="space-y-4 bg-white p-4 rounded shadow">
            <div>
                <label class="block font-semibold">Village Name</label>
                <input type="text" name="village_name" class="w-full border rounded px-2 py-1" value="<?= htmlspecialchars($settings['village_name']) ?>" required>
            </div>
            <div>
                <label class="block font-semibold">Theme Color</label>
                <div class="flex items-center gap-4">
                    <input type="color" name="theme_color" class="w-16 h-10 p-0 border rounded" value="<?= htmlspecialchars($settings['theme_color']) ?>" onchange="document.documentElement.style.setProperty('--theme-color', this.value);document.documentElement.style.setProperty('--theme-color-light', this.value+'20');document.documentElement.style.setProperty('--theme-color-dark', this.value);document.getElementById('themePreview').style.backgroundColor = this.value+'20';document.getElementById('themePreview').style.color = this.value;">
                    <span id="themePreview" class="px-4 py-2 rounded theme-bg theme-text border" style="background-color: var(--theme-color-light); color: var(--theme-color-dark);">Preview</span>
                </div>
            </div>
            <div>
                <label class="block font-semibold">Default Language</label>
                <select name="default_language" class="w-full border rounded px-2 py-1">
                    <option value="en" <?= $settings['default_language']=='en'?'selected':'' ?>>English</option>
                    <option value="hi" <?= $settings['default_language']=='hi'?'selected':'' ?>>हिन्दी</option>
                    <option value="gu" <?= $settings['default_language']=='gu'?'selected':'' ?>>ગુજરાતી</option>
                </select>
            </div>
            <button type="submit" class="theme-btn px-4 py-2 rounded w-full">Save Settings</button>
        </form>
    </div>
</body>
</html> 