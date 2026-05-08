<div class="p-8">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[#111111] mb-2">التذاكر</h1>
                <p class="text-[#666666]"><?= $adminView ? 'إدارة جميع التذاكر' : 'تذاكري' ?></p>
            </div>
            <a href="<?= url('/tickets/create') ?>" class="bg-[#F4C400] text-[#111111] px-6 py-3 rounded-lg font-bold hover:bg-[#e0b300] transition-colors">
                + تذكرة جديدة
            </a>
        </div>

        <div class="bg-white rounded-xl p-6 border border-[#e5e5e5] mb-6">
            <form id="ticket-filter-form" method="GET" action="<?= url('/tickets') ?>" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-[#111111] mb-1 text-sm">بحث</label>
                    <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="بحث بالعنوان أو الكود..."
                        class="w-full px-3 py-2 border border-[#e5e5e5] rounded-lg text-sm focus:ring-2 focus:ring-[#F4C400] focus:border-transparent" dir="rtl">
                </div>
                <div>
                    <label class="block text-[#111111] mb-1 text-sm">الحالة</label>
                    <select name="status" class="w-full px-3 py-2 border border-[#e5e5e5] rounded-lg text-sm focus:ring-2 focus:ring-[#F4C400] focus:border-transparent">
                        <option value="all" <?= ($statusFilter ?? 'all') === 'all' ? 'selected' : '' ?>>الكل</option>
                        <option value="مفتوح" <?= ($statusFilter ?? '') === 'مفتوح' ? 'selected' : '' ?>>مفتوح</option>
                        <option value="قيد المراجعة" <?= ($statusFilter ?? '') === 'قيد المراجعة' ? 'selected' : '' ?>>قيد المراجعة</option>
                        <option value="تم الرد" <?= ($statusFilter ?? '') === 'تم الرد' ? 'selected' : '' ?>>تم الرد</option>
                        <option value="مغلق" <?= ($statusFilter ?? '') === 'مغلق' ? 'selected' : '' ?>>مغلق</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[#111111] mb-1 text-sm">التصنيف</label>
                    <select name="category" class="w-full px-3 py-2 border border-[#e5e5e5] rounded-lg text-sm focus:ring-2 focus:ring-[#F4C400] focus:border-transparent">
                        <option value="all" <?= ($categoryFilter ?? 'all') === 'all' ? 'selected' : '' ?>>الكل</option>
                        <option value="تقرير مشكلة" <?= ($categoryFilter ?? '') === 'تقرير مشكلة' ? 'selected' : '' ?>>تقرير مشكلة</option>
                        <option value="طلب صيانة" <?= ($categoryFilter ?? '') === 'طلب صيانة' ? 'selected' : '' ?>>طلب صيانة</option>
                        <option value="طلب معلومات" <?= ($categoryFilter ?? '') === 'طلب معلومات' ? 'selected' : '' ?>>طلب معلومات</option>
                        <option value="أخرى" <?= ($categoryFilter ?? '') === 'أخرى' ? 'selected' : '' ?>>أخرى</option>
                    </select>
                </div>
                <?php if ($adminView && !empty($employees)): ?>
                <div>
                    <label class="block text-[#111111] mb-1 text-sm">الموظف</label>
                    <select name="employee_id" class="w-full px-3 py-2 border border-[#e5e5e5] rounded-lg text-sm focus:ring-2 focus:ring-[#F4C400] focus:border-transparent">
                        <option value="">الكل</option>
                        <?php foreach ($employees as $emp): ?>
                        <option value="<?= $emp['id'] ?>" <?= (isset($_GET['employee_id']) && (int)$_GET['employee_id'] === (int)$emp['id']) ? 'selected' : '' ?>><?= htmlspecialchars($emp['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </form>
        </div>

        <?php if (empty($tickets)): ?>
        <div class="bg-white rounded-xl p-12 border border-[#e5e5e5] text-center">
            <svg class="mx-auto h-16 w-16 text-[#666666]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-[#666666] mt-4 text-lg">لا توجد تذاكر</p>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-xl border border-[#e5e5e5] overflow-hidden">
            <table class="w-full">
                <thead class="bg-[#f9f9f9]">
                    <tr>
                        <th class="px-4 py-3 text-right text-sm font-bold text-[#111111]">الكود</th>
                        <th class="px-4 py-3 text-right text-sm font-bold text-[#111111]">العنوان</th>
                        <th class="px-4 py-3 text-right text-sm font-bold text-[#111111]">التصنيف</th>
                        <th class="px-4 py-3 text-right text-sm font-bold text-[#111111]">الحالة</th>
                        <?php if ($adminView): ?>
                        <th class="px-4 py-3 text-right text-sm font-bold text-[#111111]">المرسل</th>
                        <?php endif; ?>
                        <th class="px-4 py-3 text-right text-sm font-bold text-[#111111]">التاريخ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e5e5e5]">
                    <?php foreach ($tickets as $ticket): ?>
                    <?php
                        $statusColors = [
                            'مفتوح' => 'bg-blue-100 text-blue-800',
                            'قيد المراجعة' => 'bg-yellow-100 text-yellow-800',
                            'تم الرد' => 'bg-green-100 text-green-800',
                            'مغلق' => 'bg-gray-100 text-gray-800',
                        ];
                        $categoryColors = [
                            'تقرير مشكلة' => 'bg-red-100 text-red-800',
                            'طلب صيانة' => 'bg-orange-100 text-orange-800',
                            'طلب معلومات' => 'bg-blue-100 text-blue-800',
                            'أخرى' => 'bg-gray-100 text-gray-800',
                        ];
                        $statusClass = $statusColors[$ticket['status']] ?? 'bg-gray-100 text-gray-800';
                        $categoryClass = $categoryColors[$ticket['category']] ?? 'bg-gray-100 text-gray-800';
                    ?>
                    <tr class="hover:bg-[#fafafa] cursor-pointer" onclick="window.location.href='<?= url('/tickets/' . $ticket['id']) ?>'">
                        <td class="px-4 py-3 text-sm font-mono text-[#111111]"><?= htmlspecialchars($ticket['ticket_code']) ?></td>
                        <td class="px-4 py-3 text-sm text-[#111111] max-w-xs truncate"><?= htmlspecialchars($ticket['title']) ?></td>
                        <td class="px-4 py-3"><span class="px-2 py-1 rounded text-xs font-bold <?= $categoryClass ?>"><?= htmlspecialchars($ticket['category']) ?></span></td>
                        <td class="px-4 py-3"><span class="px-2 py-1 rounded text-xs font-bold <?= $statusClass ?>"><?= htmlspecialchars($ticket['status']) ?></span></td>
                        <?php if ($adminView): ?>
                        <td class="px-4 py-3 text-sm text-[#666666]"><?= htmlspecialchars($ticket['creator_name'] ?? '') ?></td>
                        <?php endif; ?>
                        <td class="px-4 py-3 text-sm text-[#666666]"><?= date('Y/m/d', strtotime($ticket['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (($totalPages ?? 1) > 1): ?>
        <div class="flex justify-center mt-6 gap-2">
            <?php
                $currentPage = $page ?? 1;
                $total = $totalPages ?? 1;
                $queryParams = array_filter([
                    'search' => $search ?? '',
                    'status' => $statusFilter ?? 'all',
                    'category' => $categoryFilter ?? 'all',
                ]);
                if ($adminView && isset($_GET['employee_id'])) {
                    $queryParams['employee_id'] = $_GET['employee_id'];
                }
                $queryString = http_build_query($queryParams);
            ?>
            <?php if ($currentPage > 1): ?>
            <a href="<?= url('/tickets?' . $queryString . '&page=' . ($currentPage - 1)) ?>" class="px-4 py-2 border border-[#e5e5e5] rounded-lg text-[#111111] hover:bg-[#f9f9f9]">السابق</a>
            <?php endif; ?>

            <span class="px-4 py-2 text-[#666666]">صفحة <?= $currentPage ?> من <?= $total ?></span>

            <?php if ($currentPage < $total): ?>
            <a href="<?= url('/tickets?' . $queryString . '&page=' . ($currentPage + 1)) ?>" class="px-4 py-2 border border-[#e5e5e5] rounded-lg text-[#111111] hover:bg-[#f9f9f9]">التالي</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>