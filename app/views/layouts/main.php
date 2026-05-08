<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Dayem ERP') ?></title>
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Cairo', sans-serif; }</style>
    <link rel="stylesheet" href="<?= asset('/css/custom.css') ?>">
    <?php if ($activePage === 'home'): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <?php endif; ?>
</head>
<body class="bg-[#fafafa] min-h-screen">
    <?php require ROOT_PATH . '/app/views/components/sidebar.php'; ?>
    <div class="mr-64 flex flex-col min-h-screen">
        <?php require ROOT_PATH . '/app/views/components/header.php'; ?>
        <main class="flex-1"><?= $content ?></main>
    </div>
    <script>window.csrfToken = document.querySelector('meta[name="csrf-token"]').content; window.baseUrl = '<?= $GLOBALS['app_config']['base_path'] ?? '' ?>';</script>
    <script src="<?= asset('/js/app.js') ?>"></script>
    <?php if (!empty($pageScripts)): ?>
        <?php foreach ($pageScripts as $script): ?>
            <script src="<?= asset($script) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>