<?php
require_once 'includes/db.php';
require_once 'includes/helpers.php';

// Load settings and language
$settings = json_decode(file_get_contents('config/settings.json'), true);
$lang = $_GET['lang'] ?? $settings['default_language'];
$translations = json_decode(file_get_contents('config/languages.json'), true);

// Fetch categories
$db = (new Database())->getConnection();
$cat_stmt = $db->query('SELECT * FROM categories WHERE is_active = 1');
$categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch active notices (not expired, not deleted)
$now = date('Y-m-d H:i:s');
$notice_stmt = $db->prepare('SELECT n.*, c.name_en, c.name_hi, c.name_gu, a.name as admin_name FROM notices n JOIN categories c ON n.category_id = c.id JOIN admins a ON n.admin_id = a.id WHERE n.is_deleted = 0 AND n.published_at <= ? AND n.expiry_at > ? ORDER BY n.published_at DESC');
$notice_stmt->execute([$now, $now]);
$notices = $notice_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($settings['village_name']) ?> - <?= t('notices', $lang, $translations) ?></title>
    <script src="assets/voice.js"></script>
    <!-- Tailwind CSS removed -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-50 min-h-screen" style="--tw-bg-opacity:1;background-color:<?= htmlspecialchars($settings['theme_color']) ?>10;">
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
    <div class="max-w-2xl mx-auto p-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-center mb-4 relative">
            <h1 class="text-2xl font-bold theme-text mb-2 sm:mb-0">
                <?= htmlspecialchars($settings['village_name']) ?>
            </h1>
            <div class="flex items-center gap-2">
                <!-- Language Selector -->
                <form method="get" class="flex items-center gap-2">
                    <select name="lang" onchange="this.form.submit()" class="rounded border px-2 py-1">
                        <option value="en" <?= $lang=='en'?'selected':'' ?>>English</option>
                        <option value="hi" <?= $lang=='hi'?'selected':'' ?>>हिन्दी</option>
                        <option value="gu" <?= $lang=='gu'?'selected':'' ?>>ગુજરાતી</option>
                    </select>
                </form>
                <!-- Admin Login Button -->
                <a href="admin/login.php" class="theme-btn px-3 py-1 rounded font-semibold shadow ml-2 whitespace-nowrap">Admin Login</a>
            </div>
        </div>
        <!-- Categories Tabs -->
        <div class="flex flex-wrap gap-2 mb-4">
            <a href="?lang=<?= $lang ?>" class="px-3 py-1 rounded theme-bg theme-text font-semibold">All</a>
            <?php foreach ($categories as $cat): ?>
                <a href="?cat=<?= $cat['id'] ?>&lang=<?= $lang ?>" class="px-3 py-1 rounded theme-bg theme-text">
                    <?= htmlspecialchars($cat['name_'.$lang] ?? $cat['name_en']) ?>
                </a>
            <?php endforeach; ?>
        </div>
        <!-- Admin Login Button -->
        <!-- Notices List -->
        <div class="space-y-4">
            <?php foreach ($notices as $notice):
                if (isset($_GET['cat']) && $_GET['cat'] != $notice['category_id']) continue;
            ?>
            <div class="bg-white rounded shadow p-4 flex flex-col gap-2">
                <div class="flex items-center gap-2">
                    <span class="inline-block w-8 h-8 theme-bg rounded-full flex items-center justify-center">
                        <span class="text-xl">🔔</span>
                    </span>
                    <span class="font-bold text-lg break-words" style="max-width:70vw;">
                        <?= htmlspecialchars($notice['title_'.$lang] ?? $notice['title_en']) ?>
                    </span>
                </div>
                <div class="text-gray-700">
                    <?= nl2br(htmlspecialchars($notice['description_'.$lang] ?? $notice['description_en'])) ?>
                </div>
                <div class="flex flex-wrap gap-4 text-sm text-gray-500">
                    <span><?= t('category', $lang, $translations) ?>: <b><?= htmlspecialchars($notice['name_'.$lang] ?? $notice['name_en']) ?></b></span>
                    <span><?= t('date_published', $lang, $translations) ?>: <?= format_date($notice['published_at'], $lang) ?></span>
                    <span><?= t('expiry_date', $lang, $translations) ?>: <?= format_date($notice['expiry_at'], $lang) ?></span>
                    <span><?= t('admin', $lang, $translations) ?>: <?= htmlspecialchars($notice['admin_name']) ?></span>
                </div>
                <button onclick="speakNotice(`<?= htmlspecialchars(($notice['title_'.$lang] ?? $notice['title_en']) . '. ' . ($notice['description_'.$lang] ?? $notice['description_en'])) ?>`, '<?= $lang ?>')" class="mt-2 px-3 py-1 theme-btn rounded flex items-center gap-2">
                    <span>🔊</span> <span><?= t('listen', $lang, $translations) ?? 'Listen' ?></span>
                </button>
            </div>
            <?php endforeach; ?>
            <?php if (empty($notices)): ?>
                <div class="text-center text-gray-400 py-8">No notices found.</div>
            <?php endif; ?>
        </div>
                
    </div>
</body>
</html> 