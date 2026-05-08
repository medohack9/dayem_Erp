<div class="p-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[#111111] mb-2">تعديل المهمة</h1>
                <p class="text-[#666666]">تعديل بيانات المهمة <?= htmlspecialchars($task['task_code'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <a href="<?= url('/tasks') ?>" class="text-[#666666] hover:text-[#111111] transition-colors">← العودة للمهام</a>
        </div>

        <form id="edit-task-form">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="task_id" value="<?= $task['id'] ?>">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-xl p-6 border border-[#e5e5e5]">
                        <h3 class="text-lg font-bold text-[#111111] mb-6">بيانات المهمة</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-[#111111] mb-2" for="title">عنوان المهمة <span class="text-red-500">*</span></label>
                                <input type="text" id="title" name="title" dir="rtl"
                                    class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]"
                                    placeholder="ادخل عنوان المهمة" required maxlength="200"
                                    value="<?= htmlspecialchars($task['title'], ENT_QUOTES, 'UTF-8') ?>">
                                <div id="title-error" class="text-red-600 text-sm mt-1 hidden"></div>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-[#111111] mb-2" for="description">وصف المهمة</label>
                                <textarea id="description" name="description" dir="rtl" rows="3"
                                    class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]"
                                    placeholder="ادخل وصف المهمة (اختياري)" maxlength="2000"><?= htmlspecialchars($task['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                                <div id="description-error" class="text-red-600 text-sm mt-1 hidden"></div>
                            </div>
                            <div>
                                <label class="block text-[#111111] mb-2" for="priority">الأولية <span class="text-red-500">*</span></label>
                                <select id="priority" name="priority" dir="rtl"
                                    class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]">
                                    <option value="عاجل" <?= $task['priority'] === 'عاجل' ? 'selected' : '' ?>>عاجل</option>
                                    <option value="متوسط" <?= $task['priority'] === 'متوسط' ? 'selected' : '' ?>>متوسط</option>
                                    <option value="منخفض" <?= $task['priority'] === 'منخفض' ? 'selected' : '' ?>>منخفض</option>
                                </select>
                                <div id="priority-error" class="text-red-600 text-sm mt-1 hidden"></div>
                            </div>
                            <div>
                                <label class="block text-[#111111] mb-2" for="status">الحالة</label>
                                <select id="status" name="status" dir="rtl"
                                    class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]">
                                    <option value="جديد" <?= $task['status'] === 'جديد' ? 'selected' : '' ?>>جديد</option>
                                    <option value="قيد التنفيذ" <?= $task['status'] === 'قيد التنفيذ' ? 'selected' : '' ?>>قيد التنفيذ</option>
                                    <option value="مكتمل" <?= $task['status'] === 'مكتمل' ? 'selected' : '' ?>>مكتمل</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[#111111] mb-2" for="due_date">تاريخ التسليم</label>
                                <input type="date" id="due_date" name="due_date"
                                    class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]"
                                    value="<?= htmlspecialchars($task['due_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <?php if ($task['completed_at']): ?>
                                <div>
                                    <label class="block text-[#111111] mb-2">تاريخ الإنجاز</label>
                                    <p class="text-[#111111] py-3"><?= date('Y/m/d H:i', strtotime($task['completed_at'])) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl p-6 border border-[#e5e5e5]">
                        <h3 class="text-lg font-bold text-[#111111] mb-6">التعيين</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[#111111] mb-2" for="assigned_to">الموظف المكلف <span class="text-red-500">*</span></label>
                                <select id="assigned_to" name="assigned_to" dir="rtl"
                                    class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]"
                                    required>
                                    <option value="">اختر الموظف</option>
                                    <?php foreach ($employees as $emp): ?>
                                        <option value="<?= $emp['id'] ?>" <?= ($task['assigned_to'] == $emp['id']) ? 'selected' : '' ?>><?= htmlspecialchars($emp['name'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($emp['employee_code'] ?? '', ENT_QUOTES, 'UTF-8') ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                                <div id="assigned_to-error" class="text-red-600 text-sm mt-1 hidden"></div>
                            </div>
                            <div>
                                <label class="block text-[#111111] mb-2" for="department_id">القسم</label>
                                <select id="department_id" name="department_id" dir="rtl"
                                    class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]">
                                    <option value="">اختر القسم (اختياري)</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?= $dept['id'] ?>" <?= ($task['department_id'] == $dept['id']) ? 'selected' : '' ?>><?= htmlspecialchars($dept['name'], ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div id="department_id-error" class="text-red-600 text-sm mt-1 hidden"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white rounded-xl p-6 border border-[#e5e5e5]">
                        <?php
                            $statusMap = ['جديد' => 'bg-blue-100 text-blue-800', 'قيد التنفيذ' => 'bg-yellow-100 text-yellow-800', 'مكتمل' => 'bg-green-100 text-green-800'];
                            $priorityMap = ['عاجل' => 'العاجلة', 'متوسط' => 'متوسطة', 'منخفض' => 'منخفضة'];
                        ?>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-[#666666] text-sm">الحالة</span>
                                <span class="px-3 py-1 rounded-full text-xs font-medium <?= $statusMap[$task['status']] ?? 'bg-gray-100 text-gray-800' ?>"><?= htmlspecialchars($task['status'], ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-[#666666] text-sm">الأولية</span>
                                <span class="text-[#111111] font-medium text-sm"><?= $priorityMap[$task['priority']] ?? $task['priority'] ?></span>
                            </div>
                            <?php if ($task['due_date']): ?>
                                <div class="flex justify-between items-center">
                                    <span class="text-[#666666] text-sm">تاريخ التسليم</span>
                                    <span class="text-[#111111] text-sm" dir="ltr"><?= htmlspecialchars($task['due_date'], ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="flex justify-between items-center">
                                <span class="text-[#666666] text-sm">الموظف</span>
                                <span class="text-[#111111] font-medium text-sm"><?= htmlspecialchars($task['employee_name'] ?? '—', ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <?php if ($task['department_name']): ?>
                                <div class="flex justify-between items-center">
                                    <span class="text-[#666666] text-sm">القسم</span>
                                    <span class="text-[#111111] text-sm"><?= htmlspecialchars($task['department_name'], ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="flex justify-between items-center">
                                <span class="text-[#666666] text-sm">كود المهمة</span>
                                <span class="text-[#111111] text-sm" dir="ltr"><?= htmlspecialchars($task['task_code'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl p-6 border border-[#e5e5e5]">
                        <div id="form-error" class="text-red-600 text-sm mb-3 hidden"></div>
                        <div id="form-success" class="text-green-600 text-sm mb-3 hidden"></div>
                        <button type="submit" id="submit-btn" class="w-full bg-[#F4C400] text-[#111111] py-3 px-6 rounded-lg font-bold hover:bg-[#e5b600] transition-colors mb-3">
                            تحديث المهمة
                        </button>
                        <a href="<?= url('/tasks') ?>" class="block w-full text-center bg-white text-[#111111] py-3 px-6 rounded-lg font-bold border border-[#e5e5e5] hover:bg-[#fafafa] transition-colors">
                            إلغاء
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>