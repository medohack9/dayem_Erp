<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'تسجيل الدخول - Dayem ERP') ?></title>
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Cairo', sans-serif; }</style>
</head>
<body class="min-h-screen flex" dir="rtl">

    <!-- Right Side - Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-white">
        <div class="w-full max-w-md">

            <!-- Logo -->
            <div class="mb-12">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-12 h-12 bg-[#F4C400] rounded-lg flex items-center justify-center">
                        <span class="text-[#111111] text-2xl font-bold">د</span>
                    </div>
                    <h1 class="text-3xl font-bold text-[#111111]">دايم | Dayem</h1>
                </div>
                <p class="text-[#666666] text-lg mr-[60px]">دايم... دايمًا حواليك</p>
            </div>

            <!-- Welcome Message -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-[#111111] mb-2">أهلاً بيك</h2>
                <p class="text-[#666666]">سجل دخول علشان تدخل على نظام الإدارة</p>
            </div>

            <!-- Flash Messages -->
            <?php require ROOT_PATH . '/app/views/auth/session-message.php'; ?>

            <!-- Form Content -->
            <?= $content ?>

            <p class="text-center text-[#666666] text-sm mt-6">
                نسيت كلمة المرور؟
                <a href="#" class="text-[#111111] hover:underline font-medium">استرجع الآن</a>
            </p>
        </div>
    </div>

    <!-- Left Side - Hero -->
    <div class="hidden lg:flex lg:w-1/2 bg-[#111111] items-center justify-center p-12 relative overflow-hidden">
        <!-- Decorative Background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-20 w-64 h-64 bg-[#F4C400] rounded-full opacity-20 blur-3xl"></div>
            <div class="absolute bottom-20 right-20 w-80 h-80 bg-[#F4C400] rounded-full opacity-10 blur-3xl"></div>
        </div>

        <!-- Content -->
        <div class="relative text-center">
            <div class="mb-8">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-[#F4C400] rounded-2xl mb-6">
                    <span class="text-[#111111] text-5xl font-bold">د</span>
                </div>
                <h2 class="text-5xl font-bold text-white mb-4">دايم</h2>
                <p class="text-[#F4C400] text-2xl font-medium mb-8">دايمًا حواليك</p>
            </div>

            <div class="max-w-md mx-auto">
                <p class="text-white/80 text-lg leading-relaxed">
                    نظام إدارة متكامل لإدارة الموظفين والمهام والمرتبات بكل سهولة وسرعة
                </p>
            </div>
        </div>
    </div>

    <script src="<?= asset('/js/app.js') ?>"></script>
    <script src="<?= asset('/js/login.js') ?>"></script>
    <script>window.baseUrl = '<?= $GLOBALS['app_config']['base_path'] ?? '' ?>';</script>
</body>
</html>