<header class="bg-white border-b border-[#e5e5e5] px-8 py-4 flex items-center justify-between">
    <h2 class="text-lg font-bold text-[#111111]"><?= htmlspecialchars($pageTitle ?? 'دايم', ENT_QUOTES, 'UTF-8') ?></h2>
    <div class="flex items-center gap-4">
        <?php if (isset($_SESSION['user_id'])): ?>
            <span class="text-sm text-[#666666]">مرحبا، <?= htmlspecialchars($_SESSION['user_name'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
        <?php endif; ?>
    </div>
</header>