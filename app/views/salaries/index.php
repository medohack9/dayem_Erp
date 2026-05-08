<div class="p-8">
    <div class="max-w-7xl mx-auto">
        <?php if ($adminView): ?>
        <!-- ADMIN VIEW -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[#111111] mb-2">المرتبات</h1>
                <p class="text-[#666666]">إدارة سجلات المرتبات الشهرية</p>
            </div>
            <div class="flex gap-3">
                <a href="<?= url('/salaries/config') ?>" class="bg-white text-[#111111] px-4 py-3 rounded-lg font-bold border border-[#e5e5e5] hover:bg-[#f9f9f9] transition-colors">
                    ⚙ إعدادات المرتبات
                </a>
            </div>
        </div>

        <?php include ROOT_PATH . '/app/views/salaries/components/summary.php'; ?>
        <?php include ROOT_PATH . '/app/views/salaries/components/filters.php'; ?>

        <!-- Generate / Recalculate Modal -->
        <div id="generate-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
            <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4">
                <h3 class="text-xl font-bold text-[#111111] mb-4" id="modal-title">توليد المرتبات</h3>
                <form id="generate-form">
                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <div class="mb-4">
                        <label class="block text-[#111111] mb-1 text-sm font-bold">الشهر</label>
                        <select name="month" id="generate-month" class="w-full px-3 py-2 border border-[#e5e5e5] rounded-lg text-sm focus:ring-2 focus:ring-[#F4C400] focus:border-transparent">
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?= $i ?>" <?= $i === ($selectedMonth ?? (int)date('n')) ? 'selected' : '' ?>><?= $months[$i] ?? $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-[#111111] mb-1 text-sm font-bold">السنة</label>
                        <input type="number" name="year" id="generate-year" value="<?= $selectedYear ?? (int)date('Y') ?>" min="2000" max="2100" class="w-full px-3 py-2 border border-[#e5e5e5] rounded-lg text-sm focus:ring-2 focus:ring-[#F4C400] focus:border-transparent">
                    </div>
                    <div class="flex gap-3">
                        <button type="button" id="modal-submit-btn" class="bg-[#F4C400] text-[#111111] px-6 py-2 rounded-lg font-bold hover:bg-[#e0b300] transition-colors">توليد</button>
                        <button type="button" id="modal-cancel-btn" class="bg-white text-[#111111] px-6 py-2 rounded-lg font-bold border border-[#e5e5e5] hover:bg-[#f9f9f9] transition-colors">إلغاء</button>
                    </div>
                    <p id="modal-error" class="text-red-500 text-sm mt-3 hidden"></p>
                </form>
            </div>
        </div>

        <!-- Salary Records Table -->
        <?php if (empty($salaries)): ?>
        <div class="bg-white rounded-xl p-12 border border-[#e5e5e5] text-center">
            <svg class="mx-auto h-16 w-16 text-[#666666]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-[#666666] mt-4 text-lg">لا توجد سجلات مرتبات</p>
            <p class="text-[#999999] mt-2 text-sm">قم بتوليد المرتبات للشهر المطلوب</p>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-xl border border-[#e5e5e5] overflow-hidden">
            <table class="w-full">
                <thead class="bg-[#f9f9f9]">
                    <tr>
                        <th class="px-4 py-3 text-right text-sm font-bold text-[#111111]">الموظف</th>
                        <th class="px-4 py-3 text-right text-sm font-bold text-[#111111]">القسم</th>
                        <th class="px-4 py-3 text-right text-sm font-bold text-[#111111]">الراتب الأساسي</th>
                        <th class="px-4 py-3 text-right text-sm font-bold text-[#111111]">البدلات</th>
                        <th class="px-4 py-3 text-right text-sm font-bold text-[#111111]">الخصومات</th>
                        <th class="px-4 py-3 text-right text-sm font-bold text-[#111111]">الصافي</th>
                        <th class="px-4 py-3 text-right text-sm font-bold text-[#111111]">الحالة</th>
                        <th class="px-4 py-3 text-right text-sm font-bold text-[#111111]">إجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e5e5e5]">
                    <?php foreach ($salaries as $salary): ?>
                    <tr class="hover:bg-[#f9f9f9] transition-colors">
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-[#111111]"><?= htmlspecialchars($salary['employee_name']) ?></div>
                            <div class="text-xs text-[#666666]"><?= htmlspecialchars($salary['employee_code'] ?? '') ?></div>
                        </td>
                        <td class="px-4 py-3 text-sm text-[#666666]"><?= htmlspecialchars($salary['department_name'] ?? 'بدون قسم') ?></td>
                        <td class="px-4 py-3 text-sm text-[#111111]"><?= number_format($salary['basic_salary'], 2) ?> ج.م</td>
                        <td class="px-4 py-3 text-sm text-green-600"><?= number_format($salary['allowances'], 2) ?> ج.م</td>
                        <td class="px-4 py-3 text-sm text-red-600"><?= number_format($salary['deductions'], 2) ?> ج.م</td>
                        <td class="px-4 py-3 text-sm font-bold text-[#111111]"><?= number_format($salary['net_amount'], 2) ?> ج.م</td>
                        <td class="px-4 py-3">
                            <?php if ($salary['status'] === 'paid'): ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                مدفوع
                            </span>
                            <div class="text-xs text-[#666666] mt-1"><?= $salary['paid_by_name'] ?? '' ?></div>
                            <div class="text-xs text-[#999999]"><?= $salary['paid_at'] ?? '' ?></div>
                            <?php else: ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">
                                غير مدفوع
                            </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                            <?php if ($salary['status'] === 'unpaid'): ?>
                            <button class="pay-btn bg-[#F4C400] text-[#111111] px-3 py-1.5 rounded text-sm font-bold hover:bg-[#e0b300] transition-colors" data-id="<?= $salary['id'] ?>">
                                تأكيد الدفع
                            </button>
                            <?php else: ?>
                            <span class="text-xs text-[#999999]">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="flex items-center justify-center gap-2 mt-6">
            <?php if ($page > 1): ?>
            <?php
                $prevParams = array_filter([
                    'page' => $page - 1,
                    'month' => $selectedMonth,
                    'year' => $selectedYear,
                    'department_id' => $selectedDepartment,
                    'status' => $selectedStatus,
                    'search' => $search ?: null,
                ], fn($v) => $v !== null && $v !== '');
                $prevUrl = url('/salaries') . '?' . http_build_query($prevParams);
            ?>
            <a href="<?= $prevUrl ?>" class="px-4 py-2 bg-white border border-[#e5e5e5] rounded-lg text-sm text-[#111111] hover:bg-[#f9f9f9]">السابق</a>
            <?php endif; ?>

            <span class="px-4 py-2 text-sm text-[#666666]">صفحة <?= $page ?> من <?= $totalPages ?></span>

            <?php if ($page < $totalPages): ?>
            <?php
                $nextParams = array_filter([
                    'page' => $page + 1,
                    'month' => $selectedMonth,
                    'year' => $selectedYear,
                    'department_id' => $selectedDepartment,
                    'status' => $selectedStatus,
                    'search' => $search ?: null,
                ], fn($v) => $v !== null && $v !== '');
                $nextUrl = url('/salaries') . '?' . http_build_query($nextParams);
            ?>
            <a href="<?= $nextUrl ?>" class="px-4 py-2 bg-white border border-[#e5e5e5] rounded-lg text-sm text-[#111111] hover:bg-[#f9f9f9]">التالي</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <?php else: ?>
        <!-- EMPLOYEE VIEW -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[#111111] mb-2">سجل المرتبات</h1>
                <p class="text-[#666666]">عرض سجل المرتبات الشخصي</p>
            </div>
        </div>

        <?php if (empty($salaries)): ?>
        <div class="bg-white rounded-xl p-12 border border-[#e5e5e5] text-center">
            <svg class="mx-auto h-16 w-16 text-[#666666]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-[#666666] mt-4 text-lg">لا توجد سجلات مرتبات حتى الآن</p>
        </div>
        <?php else: ?>
        <div class="bg-white rounded-xl border border-[#e5e5e5] overflow-hidden">
            <table class="w-full">
                <thead class="bg-[#f9f9f9]">
                    <tr>
                        <th class="px-4 py-3 text-right text-sm font-bold text-[#111111]">الشهر</th>
                        <th class="px-4 py-3 text-right text-sm font-bold text-[#111111]">الراتب الأساسي</th>
                        <th class="px-4 py-3 text-right text-sm font-bold text-[#111111]">البدلات</th>
                        <th class="px-4 py-3 text-right text-sm font-bold text-[#111111]">الخصومات</th>
                        <th class="px-4 py-3 text-right text-sm font-bold text-[#111111]">الصافي</th>
                        <th class="px-4 py-3 text-right text-sm font-bold text-[#111111]">الحالة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e5e5e5]">
                    <?php foreach ($salaries as $salary): ?>
                    <tr class="hover:bg-[#f9f9f9] transition-colors">
                        <td class="px-4 py-3 text-sm text-[#111111]"><?= $months[$salary['month']] ?? $salary['month'] ?>/<?= $salary['year'] ?></td>
                        <td class="px-4 py-3 text-sm text-[#111111]"><?= number_format($salary['basic_salary'], 2) ?> ج.م</td>
                        <td class="px-4 py-3 text-sm text-green-600"><?= number_format($salary['allowances'], 2) ?> ج.م</td>
                        <td class="px-4 py-3 text-sm text-red-600"><?= number_format($salary['deductions'], 2) ?> ج.م</td>
                        <td class="px-4 py-3 text-sm font-bold text-[#111111]"><?= number_format($salary['net_amount'], 2) ?> ج.م</td>
                        <td class="px-4 py-3">
                            <?php if ($salary['status'] === 'paid'): ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                مدفوع
                            </span>
                            <div class="text-xs text-[#999999] mt-1"><?= $salary['paid_at'] ?? '' ?></div>
                            <?php else: ?>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">
                                غير مدفوع
                            </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if (($totalPages ?? 1) > 1): ?>
        <div class="flex items-center justify-center gap-2 mt-6">
            <?php if ($page > 1): ?>
            <a href="<?= url('/salaries/my') ?>?page=<?= $page - 1 ?>" class="px-4 py-2 bg-white border border-[#e5e5e5] rounded-lg text-sm text-[#111111] hover:bg-[#f9f9f9]">السابق</a>
            <?php endif; ?>

            <span class="px-4 py-2 text-sm text-[#666666]">صفحة <?= $page ?> من <?= $totalPages ?></span>

            <?php if ($page < $totalPages): ?>
            <a href="<?= url('/salaries/my') ?>?page=<?= $page + 1 ?>" class="px-4 py-2 bg-white border border-[#e5e5e5] rounded-lg text-sm text-[#111111] hover:bg-[#f9f9f9]">التالي</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>