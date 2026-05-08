<div class="p-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-[#111111] mb-2">المهام</h1>
            <p class="text-[#666666]">إدارة ومتابعة كل المهام</p>
        </div>
        <?php if (\App\Core\Auth::isAdmin()): ?>
            <a href="<?= url('/tasks/create') ?>" class="flex items-center gap-2 bg-[#F4C400] text-[#111111] px-6 py-3 rounded-lg font-bold hover:bg-[#e5b600] transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path></svg>
                إضافة مهمة جديدة
            </a>
        <?php endif; ?>
    </div>

    <div class="bg-white rounded-xl p-4 mb-6 border border-[#e5e5e5]">
        <form method="GET" action="<?= url('/tasks') ?>" class="flex flex-wrap gap-4 items-center">
            <div class="flex-1 min-w-[200px] relative">
                <svg class="absolute right-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-[#666666]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.35-4.35"></path></svg>
                <input type="text" name="search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="ابحث بالعنوان أو كود المهمة..."
                    class="w-full pr-12 pl-4 py-3 bg-[#fafafa] border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent" dir="rtl">
            </div>
            <select name="status" class="px-4 py-3 bg-[#fafafa] border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]">
                <option value="all" <?= $statusFilter === 'all' ? 'selected' : '' ?>>كل الحالات</option>
                <option value="جديد" <?= $statusFilter === 'جديد' ? 'selected' : '' ?>>جديد</option>
                <option value="قيد التنفيذ" <?= $statusFilter === 'قيد التنفيذ' ? 'selected' : '' ?>>قيد التنفيذ</option>
                <option value="مكتمل" <?= $statusFilter === 'مكتمل' ? 'selected' : '' ?>>مكتمل</option>
            </select>
            <select name="priority" class="px-4 py-3 bg-[#fafafa] border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]">
                <option value="">كل الأولويات</option>
                <option value="عاجل" <?= $priorityFilter === 'عاجل' ? 'selected' : '' ?>>عاجل</option>
                <option value="متوسط" <?= $priorityFilter === 'متوسط' ? 'selected' : '' ?>>متوسط</option>
                <option value="منخفض" <?= $priorityFilter === 'منخفض' ? 'selected' : '' ?>>منخفض</option>
            </select>
            <?php if ($adminView): ?>
                <select name="department_id" class="px-4 py-3 bg-[#fafafa] border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]">
                    <option value="">كل الأقسام</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= $dept['id'] ?>" <?= $departmentId == $dept['id'] ? 'selected' : '' ?>><?= htmlspecialchars($dept['name'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="assigned_to" class="px-4 py-3 bg-[#fafafa] border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]">
                    <option value="">كل الموظفين</option>
                    <?php foreach ($employees as $emp): ?>
                        <option value="<?= $emp['id'] ?>" <?= $assignedTo == $emp['id'] ? 'selected' : '' ?>><?= htmlspecialchars($emp['name'], ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
            <button type="submit" class="px-6 py-3 bg-[#F4C400] text-[#111111] rounded-lg font-bold hover:bg-[#e5b600] transition-colors">بحث</button>
        </form>
    </div>

    <?php if (empty($tasks)): ?>
        <div class="bg-white rounded-xl p-12 border border-[#e5e5e5] text-center">
            <svg class="w-16 h-16 text-[#666666] mx-auto mb-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
            <div class="text-[#666666] text-lg">لا توجد مهام</div>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-xl border border-[#e5e5e5] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-[#fafafa] border-b border-[#e5e5e5]">
                        <tr>
                            <th class="px-6 py-4 text-right text-sm font-bold text-[#111111]">المهمة</th>
                            <th class="px-6 py-4 text-right text-sm font-bold text-[#111111]">الكود</th>
                            <?php if ($adminView): ?>
                                <th class="px-6 py-4 text-right text-sm font-bold text-[#111111]">الموظف</th>
                            <?php endif; ?>
                            <th class="px-6 py-4 text-right text-sm font-bold text-[#111111]">القسم</th>
                            <th class="px-6 py-4 text-right text-sm font-bold text-[#111111]">الأولوية</th>
                            <th class="px-6 py-4 text-right text-sm font-bold text-[#111111]">الحالة</th>
                            <th class="px-6 py-4 text-right text-sm font-bold text-[#111111]">تاريخ التسليم</th>
                            <?php if (\App\Core\Auth::isAdmin()): ?>
                                <th class="px-6 py-4 text-right text-sm font-bold text-[#111111]">الإجراءات</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#e5e5e5]">
                        <?php foreach ($tasks as $task):
                            $statusMap = ['جديد' => 'جديد', 'قيد التنفيذ' => 'قيد التنفيذ', 'مكتمل' => 'مكتمل'];
                            $statusClass = ['جديد' => 'bg-blue-100 text-blue-800', 'قيد التنفيذ' => 'bg-yellow-100 text-yellow-800', 'مكتمل' => 'bg-green-100 text-green-800'];
                            $priorityMap = ['عاجل' => 'bg-red-100 text-red-800', 'متوسط' => 'bg-yellow-100 text-yellow-800', 'منخفض' => 'bg-gray-100 text-gray-800'];
                            $statusLabel = $statusMap[$task['status']] ?? $task['status'];
                            $statusColor = $statusClass[$task['status']] ?? 'bg-gray-100 text-gray-800';
                            $priorityColor = $priorityMap[$task['priority']] ?? 'bg-gray-100 text-gray-800';
                            $isOverdue = !empty($task['is_overdue']) && $task['is_overdue'];
                        ?>
                            <tr class="hover:bg-[#fafafa] transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-[#111111]"><?= htmlspecialchars($task['title'], ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php if ($isOverdue): ?>
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">متأخر</span>
                                        <?php endif; ?>
                                        <?php if (!empty($task['employee_status']) && $task['employee_status'] !== 'active'): ?>
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"><?= $task['employee_status'] === 'suspended' ? 'موظف موقوف' : 'موظف منتهي' ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-[#666666]" dir="ltr"><?= htmlspecialchars($task['task_code'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                <?php if ($adminView): ?>
                                    <td class="px-6 py-4 text-[#666666]"><?= htmlspecialchars($task['employee_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <?php endif; ?>
                                <td class="px-6 py-4 text-[#666666]"><?= htmlspecialchars($task['department_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-xs font-medium <?= $priorityColor ?>"><?= htmlspecialchars($task['priority'], ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if (!\App\Core\Auth::isAdmin() && $task['status'] !== 'مكتمل'): ?>
                                        <select class="status-select px-3 py-1 rounded-full text-xs font-medium border-0 cursor-pointer <?= $statusColor ?>" data-task-id="<?= $task['id'] ?>" data-csrf="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                            <?php
                                                $currentIdx = array_search($task['status'], ['جديد', 'قيد التنفيذ', 'مكتمل']);
                                                $allStatuses = ['جديد', 'قيد التنفيذ', 'مكتمل'];
                                                foreach ($allStatuses as $idx => $s):
                                                    if ($idx >= $currentIdx):
                                                        $sel = $s === $task['status'] ? 'selected' : '';
                                            ?>
                                                <option value="<?= $s ?>" <?= $sel ?>><?= $s ?></option>
                                            <?php endif; endforeach; ?>
                                        </select>
                                    <?php else: ?>
                                        <span class="px-3 py-1 rounded-full text-xs font-medium <?= $statusColor ?>"><?= $statusLabel ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 text-[#666666]" dir="ltr"><?= $task['due_date'] ?? '—' ?></td>
                                <?php if (\App\Core\Auth::isAdmin()): ?>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <a href="<?= url('/tasks/' . $task['id'] . '/edit') ?>" class="p-2 hover:bg-[#fafafa] rounded-lg transition-colors" title="تعديل">
                                                <svg class="w-4 h-4 text-[#666666]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </a>
                                            <button type="button" class="p-2 hover:bg-[#fafafa] rounded-lg transition-colors delete-task-btn" data-id="<?= $task['id'] ?>" data-title="<?= htmlspecialchars($task['title'], ENT_QUOTES, 'UTF-8') ?>" title="حذف">
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
                <a href="<?= url('/tasks?page=' . ($page - 1) . ($search ? '&search=' . urlencode($search) : '') . ($statusFilter !== 'all' ? '&status=' . urlencode($statusFilter) : '') . ($priorityFilter ? '&priority=' . urlencode($priorityFilter) : '') . ($departmentId ? '&department_id=' . $departmentId : '') . ($assignedTo ? '&assigned_to=' . $assignedTo : '')) ?>" class="px-4 py-2 rounded-lg bg-white border border-[#e5e5e5] text-[#111111] hover:bg-[#fafafa] transition-colors">السابق</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i === $page): ?>
                    <span class="px-4 py-2 rounded-lg bg-[#F4C400] text-[#111111] font-bold"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= url('/tasks?page=' . $i . ($search ? '&search=' . urlencode($search) : '') . ($statusFilter !== 'all' ? '&status=' . urlencode($statusFilter) : '') . ($priorityFilter ? '&priority=' . urlencode($priorityFilter) : '') . ($departmentId ? '&department_id=' . $departmentId : '') . ($assignedTo ? '&assigned_to=' . $assignedTo : '')) ?>" class="px-4 py-2 rounded-lg bg-white border border-[#e5e5e5] text-[#111111] hover:bg-[#fafafa] transition-colors"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <a href="<?= url('/tasks?page=' . ($page + 1) . ($search ? '&search=' . urlencode($search) : '') . ($statusFilter !== 'all' ? '&status=' . urlencode($statusFilter) : '') . ($priorityFilter ? '&priority=' . urlencode($priorityFilter) : '') . ($departmentId ? '&department_id=' . $departmentId : '') . ($assignedTo ? '&assigned_to=' . $assignedTo : '')) ?>" class="px-4 py-2 rounded-lg bg-white border border-[#e5e5e5] text-[#111111] hover:bg-[#fafafa] transition-colors">التالي</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<div id="delete-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-xl p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold text-[#111111] mb-4">حذف المهمة</h3>
        <p id="delete-modal-message" class="text-[#666666] mb-4"></p>
        <div class="flex gap-3 justify-end">
            <button type="button" id="cancel-delete" class="px-6 py-2 rounded-lg border border-[#e5e5e5] text-[#111111] hover:bg-[#fafafa] transition-colors font-medium">إلغاء</button>
            <button type="button" id="confirm-delete" class="px-6 py-2 rounded-lg bg-red-600 text-white font-bold hover:bg-red-700 transition-colors">حذف</button>
        </div>
    </div>
</div>