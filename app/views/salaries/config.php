<div class="p-8">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[#111111] mb-2">إعدادات المرتبات</h1>
                <p class="text-[#666666]">إدارة إعدادات المرتبات للموظفين</p>
            </div>
            <a href="<?= url('/salaries') ?>" class="bg-white text-[#111111] px-4 py-3 rounded-lg font-bold border border-[#e5e5e5] hover:bg-[#f9f9f9] transition-colors">
                ← العودة للمرتبات
            </a>
        </div>

        <?php if (isset($employee) && $employee): ?>
        <!-- Single Employee Config View -->
        <div class="bg-white rounded-xl p-6 border border-[#e5e5e5] mb-6">
            <h2 class="text-xl font-bold text-[#111111] mb-4">إعدادات مرتب: <?= htmlspecialchars($employee['name']) ?></h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 text-sm">
                <div>
                    <span class="text-[#666666]">كود الموظف:</span>
                    <span class="text-[#111111] font-medium"><?= htmlspecialchars($employee['employee_code'] ?? '') ?></span>
                </div>
                <div>
                    <span class="text-[#666666]">القسم:</span>
                    <span class="text-[#111111] font-medium"><?= htmlspecialchars($department['name'] ?? 'بدون قسم') ?></span>
                </div>
            </div>

            <div class="bg-[#f9f9f9] rounded-lg p-4 mb-6">
                <div class="text-sm text-[#666666] mb-1">الراتب الفعلي الحالي</div>
                <div class="text-xl font-bold text-[#111111]">
                    <?= number_format($effectiveSalary['basic_salary'] + $effectiveSalary['allowances'] - $effectiveSalary['deductions'], 2) ?> ج.م
                </div>
                <div class="text-xs text-[#999999] mt-1">
                    المصدر:
                    <?php if ($effectiveSalary['source'] === 'config'): ?>
                    إعدادات مخصصة
                    <?php elseif ($effectiveSalary['source'] === 'department'): ?>
                    القسم الافتراضي
                    <?php else: ?>
                    الراتب الأساسي
                    <?php endif; ?>
                </div>
            </div>

            <form id="config-form" class="space-y-4">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="user_id" value="<?= $employee['id'] ?>">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-[#111111] mb-1 text-sm font-bold">الراتب الأساسي (ج.م)</label>
                        <input type="number" name="basic_salary" id="basic_salary" value="<?= $config ? number_format($config['basic_salary'], 2, '.', '') : '0' ?>" step="0.01" min="0"
                            class="w-full px-3 py-2 border border-[#e5e5e5] rounded-lg text-sm focus:ring-2 focus:ring-[#F4C400] focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-[#111111] mb-1 text-sm font-bold">البدلات (ج.م)</label>
                        <input type="number" name="allowances" id="allowances" value="<?= $config ? number_format($config['allowances'], 2, '.', '') : '0' ?>" step="0.01" min="0"
                            class="w-full px-3 py-2 border border-[#e5e5e5] rounded-lg text-sm focus:ring-2 focus:ring-[#F4C400] focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-[#111111] mb-1 text-sm font-bold">الخصومات (ج.م)</label>
                        <input type="number" name="deductions" id="deductions" value="<?= $config ? number_format($config['deductions'], 2, '.', '') : '0' ?>" step="0.01" min="0"
                            class="w-full px-3 py-2 border border-[#e5e5e5] rounded-lg text-sm focus:ring-2 focus:ring-[#F4C400] focus:border-transparent">
                    </div>
                </div>
                <div>
                    <label class="block text-[#111111] mb-1 text-sm font-bold">ملاحظات</label>
                    <textarea name="notes" id="notes" rows="3"
                        class="w-full px-3 py-2 border border-[#e5e5e5] rounded-lg text-sm focus:ring-2 focus:ring-[#F4C400] focus:border-transparent"><?= htmlspecialchars($config['notes'] ?? '') ?></textarea>
                </div>
                <div id="config-error" class="text-red-500 text-sm hidden"></div>
                <div id="config-success" class="text-green-600 text-sm hidden"></div>
                <button type="submit" id="config-submit-btn" class="bg-[#F4C400] text-[#111111] px-6 py-2 rounded-lg font-bold hover:bg-[#e0b300] transition-colors">
                    حفظ الإعدادات
                </button>
            </form>
        </div>
        <?php else: ?>
        <!-- Employee List with Config -->
        <?php if (empty($employees)): ?>
        <div class="bg-white rounded-xl p-12 border border-[#e5e5e5] text-center">
            <p class="text-[#666666] mt-4 text-lg">لا يوجد موظفين نشطين</p>
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
                        <th class="px-4 py-3 text-right text-sm font-bold text-[#111111]">المصدر</th>
                        <th class="px-4 py-3 text-right text-sm font-bold text-[#111111]">إجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e5e5e5]">
                    <?php foreach ($employees as $emp): ?>
                    <?php
                        $cfg = $configMap[$emp['id']] ?? null;
                        $effective = $effectiveSalaries[$emp['id']] ?? ['basic_salary' => 0, 'allowances' => 0, 'deductions' => 0, 'source' => 'none'];
                        $sourceLabel = $effective['source'] === 'config' ? 'مخصص' : ($effective['source'] === 'department' ? 'القسم' : 'الراتب الأساسي');
                    ?>
                    <tr class="hover:bg-[#f9f9f9] transition-colors">
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-[#111111]"><?= htmlspecialchars($emp['name']) ?></div>
                            <div class="text-xs text-[#666666]"><?= htmlspecialchars($emp['employee_code'] ?? '') ?></div>
                        </td>
                        <td class="px-4 py-3 text-sm text-[#666666]"><?= htmlspecialchars($emp['department_name'] ?? 'بدون قسم') ?></td>
                        <td class="px-4 py-3 text-sm text-[#111111]"><?= $cfg ? number_format($cfg['basic_salary'], 2) : number_format($effective['basic_salary'], 2) ?> ج.م</td>
                        <td class="px-4 py-3 text-sm text-green-600"><?= $cfg ? number_format($cfg['allowances'], 2) : number_format($effective['allowances'], 2) ?> ج.م</td>
                        <td class="px-4 py-3 text-sm text-red-600"><?= $cfg ? number_format($cfg['deductions'], 2) : number_format($effective['deductions'], 2) ?> ج.م</td>
                        <td class="px-4 py-3 text-sm font-bold text-[#111111]"><?= number_format($effective['basic_salary'] + $effective['allowances'] - $effective['deductions'], 2) ?> ج.م</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold <?= $effective['source'] === 'config' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' ?>">
                                <?= $sourceLabel ?>
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <a href="<?= url('/salaries/config/' . $emp['id']) ?>" class="text-[#F4C400] hover:text-[#e0b300] text-sm font-bold">تعديل</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>