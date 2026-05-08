<?php
$icon = $icon ?? 'chart-bar';
$label = $label ?? '';
$value = $value ?? '0';
$link = $link ?? '#';
$showTooltip = $showTooltip ?? false;
$tooltipData = $tooltipData ?? [];
?>

<a href="<?= url($link) ?>" class="block bg-white rounded-xl shadow-sm hover:shadow-md transition-all duration-200 hover:scale-[1.02] cursor-pointer border-r-4 border-[#F4C400]">
    <div class="p-5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-[#F4C400]/10 rounded-lg flex items-center justify-center">
                    <?php if ($icon === 'users'): ?>
                        <svg class="w-6 h-6 text-[#F4C400]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    <?php elseif ($icon === 'tasks'): ?>
                        <svg class="w-6 h-6 text-[#F4C400]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    <?php elseif ($icon === 'ticket'): ?>
                        <svg class="w-6 h-6 text-[#F4C400]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                        </svg>
                    <?php elseif ($icon === 'money'): ?>
                        <svg class="w-6 h-6 text-[#F4C400]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    <?php elseif ($icon === 'file'): ?>
                        <svg class="w-6 h-6 text-[#F4C400]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A.997.997 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    <?php elseif ($icon === 'storage'): ?>
                        <svg class="w-6 h-6 text-[#F4C400]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                        </svg>
                    <?php else: ?>
                        <svg class="w-6 h-6 text-[#F4C400]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    <?php endif; ?>
                </div>
                <div>
                    <p class="text-sm text-gray-500 mb-1"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="text-2xl font-bold text-[#111111]"><?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </div>
        <?php if ($showTooltip && !empty($tooltipData)): ?>
        <div class="mt-3 pt-3 border-t border-gray-100">
            <div class="flex flex-wrap gap-2">
                <?php foreach ($tooltipData as $status => $count): ?>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                    <?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?>: <span class="font-bold mr-1"><?= $count ?></span>
                </span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</a>