<nav class="fixed right-0 top-0 h-screen w-64 bg-[#111111] border-l border-[#1f1f1f] flex flex-col z-30">
    <!-- Logo -->
    <div class="p-6 border-b border-[#1f1f1f]">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-[#F4C400] rounded-lg flex items-center justify-center">
                <span class="text-[#111111] text-xl font-bold">د</span>
            </div>
            <div>
                <h1 class="text-white font-bold text-lg">دايم</h1>
                <p class="text-white/60 text-xs">نظام الإدارة</p>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php $userRole = $_SESSION['user_role'] ?? ''; ?>
            <?php $activePage = $activePage ?? ''; ?>

            <a href="<?= url('/') ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $activePage === 'home' ? 'bg-[#F4C400] text-[#111111]' : 'text-white hover:bg-[#1f1f1f]' ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2"/></svg>
                <span class="font-medium">الرئيسية</span>
            </a>

            <?php if ($userRole === 'admin'): ?>
                <a href="<?= url('/employees') ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $activePage === 'employees' ? 'bg-[#F4C400] text-[#111111]' : 'text-white hover:bg-[#1f1f1f]' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="font-medium">الموظفين</span>
                </a>
            <?php endif; ?>

            <a href="<?= url('/departments') ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $activePage === 'departments' ? 'bg-[#F4C400] text-[#111111]' : 'text-white hover:bg-[#1f1f1f]' ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span class="font-medium">الأقسام</span>
            </a>

            <a href="<?= url('/tasks') ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $activePage === 'tasks' ? 'bg-[#F4C400] text-[#111111]' : 'text-white hover:bg-[#1f1f1f]' ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                <span class="font-medium">المهام</span>
            </a>

            <a href="<?= url('/tickets') ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $activePage === 'tickets' ? 'bg-[#F4C400] text-[#111111]' : 'text-white hover:bg-[#1f1f1f]' ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                <span class="font-medium">التذاكر</span>
            </a>

            <a href="<?= url('/salaries') ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $activePage === 'salaries' ? 'bg-[#F4C400] text-[#111111]' : 'text-white hover:bg-[#1f1f1f]' ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="font-medium">المرتبات</span>
            </a>

            <a href="<?= url('/files') ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $activePage === 'files' ? 'bg-[#F4C400] text-[#111111]' : 'text-white hover:bg-[#1f1f1f]' ?>">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                <span class="font-medium">الملفات</span>
            </a>

            <?php if ($userRole === 'admin'): ?>
                <a href="<?= url('/logs') ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?= $activePage === 'logs' ? 'bg-[#F4C400] text-[#111111]' : 'text-white hover:bg-[#1f1f1f]' ?>">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span class="font-medium">السجلات</span>
                </a>
            <?php endif; ?>
        <?php else: ?>
            <a href="<?= url('/auth/login') ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all text-white hover:bg-[#1f1f1f]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                <span class="font-medium">تسجيل الدخول</span>
            </a>
        <?php endif; ?>
    </nav>

    <!-- User Info -->
    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="p-4 border-t border-[#1f1f1f]">
            <div class="flex items-center gap-3 px-4 py-3">
                <div class="w-10 h-10 bg-[#F4C400] rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-[#111111] font-bold"><?= mb_substr(htmlspecialchars($_SESSION['user_name'] ?? '؟', ENT_QUOTES, 'UTF-8'), 0, 1) ?></span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm font-medium truncate"><?= htmlspecialchars($_SESSION['user_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="text-white/60 text-xs"><?= $_SESSION['user_role'] === 'admin' ? 'مدير النظام' : 'موظف' ?></p>
                </div>
                <a href="<?= url('/auth/logout') ?>" class="text-white/60 hover:text-red-400 transition-colors flex-shrink-0" title="تسجيل الخروج">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </a>
            </div>
        </div>
    <?php endif; ?>
</nav>