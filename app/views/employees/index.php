<div class="p-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-[#111111] mb-2">الموظفين</h1>
            <p class="text-[#666666]">إدارة وعرض كل موظفين الشركة</p>
        </div>
        <?php if (\App\Core\Auth::isAdmin()): ?>
            <a href="<?= url('/employees/create') ?>" class="flex items-center gap-2 bg-[#F4C400] text-[#111111] px-6 py-3 rounded-lg font-bold hover:bg-[#e5b600] transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
                إضافة موظف جديد
            </a>
        <?php endif; ?>
    </div>

    <!-- Search & Filters -->
    <div class="bg-white rounded-xl p-4 mb-6 border border-[#e5e5e5]">
        <form method="GET" action="<?= url('/employees') ?>" class="flex flex-wrap gap-4 items-center">
            <div class="flex-1 min-w-[200px] relative">
                <svg class="absolute right-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-[#666666]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.35-4.35"></path></svg>
                <input type="text" name="search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="ابحث عن موظف بالاسم أو الكود..."
                    class="w-full pr-12 pl-4 py-3 bg-[#fafafa] border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent" dir="rtl">
            </div>
            <select name="department_id" class="px-4 py-3 bg-[#fafafa] border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]">
                <option value="">كل الأقسام</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?= $dept['id'] ?>" <?= $departmentId == $dept['id'] ? 'selected' : '' ?>><?= htmlspecialchars($dept['name'], ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
            <select name="status" class="px-4 py-3 bg-[#fafafa] border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]">
                <option value="active" <?= $statusFilter === 'active' ? 'selected' : '' ?>>نشط</option>
                <option value="suspended" <?= $statusFilter === 'suspended' ? 'selected' : '' ?>>موقوف</option>
                <option value="terminated" <?= $statusFilter === 'terminated' ? 'selected' : '' ?>>منتهي الخدمة</option>
                <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>الكل</option>
            </select>
            <button type="submit" class="px-6 py-3 bg-[#F4C400] text-[#111111] rounded-lg font-bold hover:bg-[#e5b600] transition-colors">بحث</button>
        </form>
    </div>

    <?php if (empty($employees)): ?>
        <div class="bg-white rounded-xl p-12 border border-[#e5e5e5] text-center">
            <svg class="w-16 h-16 text-[#666666] mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.635-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"></path></svg>
            <div class="text-[#666666] text-lg">لا توجد موظفين</div>
        </div>
    <?php else: ?>
        <!-- Employees Table -->
        <div class="bg-white rounded-xl border border-[#e5e5e5] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-[#fafafa] border-b border-[#e5e5e5]">
                        <tr>
                            <th class="px-6 py-4 text-right text-sm font-bold text-[#111111]">اسم الموظف</th>
                            <th class="px-6 py-4 text-right text-sm font-bold text-[#111111]">كود الموظف</th>
                            <th class="px-6 py-4 text-right text-sm font-bold text-[#111111]">القسم</th>
                            <th class="px-6 py-4 text-right text-sm font-bold text-[#111111]">رقم الموبايل</th>
                            <th class="px-6 py-4 text-right text-sm font-bold text-[#111111]">المرتب</th>
                            <th class="px-6 py-4 text-right text-sm font-bold text-[#111111]">الحالة</th>
                            <?php if (\App\Core\Auth::isAdmin()): ?>
                                <th class="px-6 py-4 text-right text-sm font-bold text-[#111111]">الإجراءات</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#e5e5e5]">
                        <?php foreach ($employees as $emp): ?>
                            <?php
                                $statusMap = ['active' => 'نشط', 'suspended' => 'موقوف', 'terminated' => 'منتهي الخدمة'];
                                $statusClass = ['active' => 'bg-green-100 text-green-800', 'suspended' => 'bg-yellow-100 text-yellow-800', 'terminated' => 'bg-red-100 text-red-800'];
                                $statusLabel = $statusMap[$emp['status']] ?? $emp['status'];
                                $statusColor = $statusClass[$emp['status']] ?? 'bg-gray-100 text-gray-800';
                                $salary = $emp['salary'] !== null ? number_format((float)$emp['salary'], 0) . ' ج.م' : '—';
                            ?>
                            <tr class="hover:bg-[#fafafa] transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <?php if (!empty($emp['photo_id'])): ?>
                                            <img src="<?= url('/documents/' . $emp['photo_id'] . '/' . $emp['photo_stored_name']) ?>" alt="<?= htmlspecialchars($emp['name'], ENT_QUOTES, 'UTF-8') ?>" class="w-10 h-10 rounded-full object-cover border-2 border-[#F4C400]">
                                        <?php else: ?>
                                            <div class="w-10 h-10 bg-[#F4C400] rounded-full flex items-center justify-center">
                                                <span class="text-[#111111] font-bold"><?= mb_substr($emp['name'], 0, 1) ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <span class="font-medium text-[#111111]"><?= htmlspecialchars($emp['name'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-[#666666]"><?= htmlspecialchars($emp['employee_code'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-6 py-4 text-[#666666]"><?= htmlspecialchars($emp['department_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-6 py-4 text-[#666666]" dir="ltr"><?= htmlspecialchars($emp['phone'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-6 py-4 text-[#111111] font-medium" dir="ltr"><?= $salary ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-medium <?= $statusColor ?>"><?= $statusLabel ?></span>
                                </td>
                                <?php if (\App\Core\Auth::isAdmin()): ?>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <a href="<?= url('/employees/' . $emp['id'] . '/edit') ?>" class="p-2 hover:bg-[#fafafa] rounded-lg transition-colors" title="تعديل">
                                                <svg class="w-4 h-4 text-[#666666]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </a>
                                            <button type="button" class="p-2 hover:bg-[#fafafa] rounded-lg transition-colors delete-emp-btn" data-id="<?= $emp['id'] ?>" data-name="<?= htmlspecialchars($emp['name'], ENT_QUOTES, 'UTF-8') ?>" title="حذف">
                                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($totalPages > 1): ?>
        <div class="flex justify-center mt-8 gap-2">
            <?php if ($page > 1): ?>
                <a href="<?= url('/employees?page=' . ($page - 1) . ($search ? '&search=' . urlencode($search) : '') . ($departmentId ? '&department_id=' . $departmentId : '') . '&status=' . $statusFilter) ?>" class="px-4 py-2 rounded-lg bg-white border border-[#e5e5e5] text-[#111111] hover:bg-[#fafafa] transition-colors">السابق</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i === $page): ?>
                    <span class="px-4 py-2 rounded-lg bg-[#F4C400] text-[#111111] font-bold"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= url('/employees?page=' . $i . ($search ? '&search=' . urlencode($search) : '') . ($departmentId ? '&department_id=' . $departmentId : '') . '&status=' . $statusFilter) ?>" class="px-4 py-2 rounded-lg bg-white border border-[#e5e5e5] text-[#111111] hover:bg-[#fafafa] transition-colors"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="<?= url('/employees?page=' . ($page + 1) . ($search ? '&search=' . urlencode($search) : '') . ($departmentId ? '&department_id=' . $departmentId : '') . '&status=' . $statusFilter) ?>" class="px-4 py-2 rounded-lg bg-white border border-[#e5e5e5] text-[#111111] hover:bg-[#fafafa] transition-colors">التالي</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Delete Modal -->
<div id="delete-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold text-[#111111] mb-4">حذف الموظف</h3>
        <p id="delete-modal-message" class="text-[#666666] mb-4"></p>
        <div class="flex gap-3 justify-end">
            <button type="button" id="cancel-delete" class="px-6 py-2 rounded-lg border border-[#e5e5e5] text-[#111111] hover:bg-[#fafafa] transition-colors font-medium">إلغاء</button>
            <button type="button" id="confirm-delete" class="px-6 py-2 rounded-lg bg-red-600 text-white font-bold hover:bg-red-700 transition-colors">حذف</button>
        </div>
    </div>
</div>