<?php
require_once '../includes/auth.php';
require_once '../includes/helpers.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    if (admin_login($username, $password)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
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
<body class="theme-bg min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded shadow max-w-sm w-full">
        <h2 class="text-2xl font-bold mb-4 theme-text">Admin Login</h2>
        <?php if ($error): ?>
            <div class="mb-4 text-red-600"> <?= htmlspecialchars($error) ?> </div>
        <?php endif; ?>
        <form method="post" class="space-y-4">
            <div>
                <label class="block mb-1 font-semibold">Username</label>
                <input type="text" name="username" class="w-full border rounded px-3 py-2" required>
            </div>
            <div>
                <label class="block mb-1 font-semibold">Password</label>
                <input type="password" name="password" class="w-full border rounded px-3 py-2" required>
            </div>
            <button type="submit" class="w-full theme-btn py-2 rounded font-bold">Login</button>
        </form>
    </div>
</body>
</html> 