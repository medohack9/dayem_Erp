<div class="p-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-[#111111] mb-2">الأقسام</h1>
            <p class="text-[#666666]">إدارة أقسام الشركة والمديرين</p>
        </div>
        <?php if (\App\Core\Auth::isAdmin()): ?>
            <a href="<?= url('/departments/create') ?>" class="flex items-center gap-2 bg-[#F4C400] text-[#111111] px-6 py-3 rounded-lg font-bold hover:bg-[#e5b600] transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                إضافة قسم جديد
            </a>
        <?php endif; ?>
    </div>

    <!-- Search -->
    <div class="mb-6">
        <input type="text" id="department-search" placeholder="البحث في الأقسام..." value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
            class="w-full max-w-md px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]" dir="rtl">
    </div>

    <?php if (empty($departments)): ?>
        <div class="bg-white rounded-xl p-12 border border-[#e5e5e5] text-center">
            <div class="text-[#666666] text-lg">لا توجد أقسام</div>
        </div>
    <?php else: ?>
        <!-- Departments Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="departments-grid">
            <?php foreach ($departments as $dept): ?>
                <?php
                    $managerName = $dept['manager_name'] ?? null;
                    $managerInitial = $managerName ? mb_substr($managerName, 0, 1) : '?';
                    $salary = $dept['default_salary'] !== null ? number_format((float)$dept['default_salary'], 0) . ' ج.م' : '—';
                    $empCount = $dept['employee_count'] ?? 0;
                ?>
                <div class="bg-white rounded-xl p-6 border border-[#e5e5e5] hover:shadow-lg transition-shadow">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-xl font-bold text-[#111111] mb-1"><?= htmlspecialchars($dept['name'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p class="text-[#666666] text-sm"><?= $empCount ?> موظف</p>
                        </div>
                        <?php if (\App\Core\Auth::isAdmin()): ?>
                            <div class="flex items-center gap-1">
                                <a href="<?= url('/departments/' . $dept['id'] . '/edit') ?>" class="p-2 hover:bg-[#fafafa] rounded-lg transition-colors" title="تعديل">
                                    <svg class="w-4 h-4 text-[#666666]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </a>
                                <button type="button" class="p-2 hover:bg-[#fafafa] rounded-lg transition-colors delete-dept-btn" data-id="<?= $dept['id'] ?>" data-name="<?= htmlspecialchars($dept['name'], ENT_QUOTES, 'UTF-8') ?>" data-count="<?= $empCount ?>" title="حذف">
                                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="space-y-3 pt-4 border-t border-[#e5e5e5]">
                        <div>
                            <p class="text-xs text-[#666666] mb-1">مدير القسم</p>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-[#F4C400] rounded-full flex items-center justify-center">
                                    <span class="text-[#111111] text-xs font-bold"><?= $managerInitial ?></span>
                                </div>
                                <p class="text-[#111111] font-medium"><?= $managerName ? htmlspecialchars($managerName, ENT_QUOTES, 'UTF-8') : '—' ?></p>
                            </div>
                        </div>

                        <div>
                            <p class="text-xs text-[#666666] mb-1">المرتب الافتراضي</p>
                            <p class="text-[#111111] font-bold" dir="ltr"><?= $salary ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($totalPages > 1): ?>
        <div class="flex justify-center mt-8 gap-2">
            <?php if ($page > 1): ?>
                <a href="<?= url('/departments?page=' . ($page - 1) . ($search ? '&search=' . urlencode($search) : '')) ?>" class="px-4 py-2 rounded-lg bg-white border border-[#e5e5e5] text-[#111111] hover:bg-[#fafafa] transition-colors">السابق</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i === $page): ?>
                    <span class="px-4 py-2 rounded-lg bg-[#F4C400] text-[#111111] font-bold"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= url('/departments?page=' . $i . ($search ? '&search=' . urlencode($search) : '')) ?>" class="px-4 py-2 rounded-lg bg-white border border-[#e5e5e5] text-[#111111] hover:bg-[#fafafa] transition-colors"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="<?= url('/departments?page=' . ($page + 1) . ($search ? '&search=' . urlencode($search) : '')) ?>" class="px-4 py-2 rounded-lg bg-white border border-[#e5e5e5] text-[#111111] hover:bg-[#fafafa] transition-colors">التالي</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Delete Modal -->
<div id="delete-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold text-[#111111] mb-4">حذف القسم</h3>
        <p id="delete-modal-message" class="text-[#666666] mb-4"></p>
        <div id="reassign-section" class="mb-4 hidden">
            <label class="block text-xs text-[#666666] mb-1" for="reassign-dept">إعادة تعيين الموظفين إلى:</label>
            <select id="reassign-dept" class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]" dir="rtl">
                <option value="">اختر القسم</option>
            </select>
            <div id="reassign-error" class="text-red-600 text-sm mt-1 hidden"></div>
        </div>
        <div class="flex gap-3 justify-end">
            <button type="button" id="cancel-delete" class="px-6 py-2 rounded-lg border border-[#e5e5e5] text-[#111111] hover:bg-[#fafafa] transition-colors font-medium">إلغاء</button>
            <button type="button" id="confirm-delete" class="px-6 py-2 rounded-lg bg-red-600 text-white font-bold hover:bg-red-700 transition-colors">حذف</button>
        </div>
    </div>
</div>