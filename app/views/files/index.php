<div class="p-8">
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[#111111] mb-2">الملفات</h1>
                <p class="text-[#666666]">إدارة الملفات الخاصة بك</p>
            </div>
            <div class="flex items-center gap-4">
                <?php if (!$storageInfo['unlimited']): ?>
                <div class="bg-white rounded-lg px-4 py-2 border border-[#e5e5e5]">
                    <span class="text-[#666666] text-sm">التخزين:</span>
                    <span class="font-bold text-[#111111] mr-1"><?= htmlspecialchars($storageInfo['storage_used_formatted']) ?> / <?= htmlspecialchars($storageInfo['storage_quota_formatted']) ?></span>
                    <div class="w-24 bg-gray-200 rounded-full h-1.5 inline-block mr-2">
                        <div class="bg-[#F4C400] h-1.5 rounded-full" style="width: <?= min($storageInfo['percentage'] ?? 0, 100) ?>%"></div>
                    </div>
                </div>
                <?php endif; ?>
                <a href="<?= url('/files/create') ?>" class="bg-[#F4C400] text-[#111111] px-4 py-2 rounded-lg font-bold hover:bg-[#e0b300] transition-colors">
                    + رفع ملف جديد
                </a>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-[#e5e5e5] mb-6">
            <div class="p-4 border-b border-[#e5e5e5]">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>"
                            placeholder="بحث باسم الملف..."
                            class="w-full px-4 py-2 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]">
                    </div>
                    <div>
                        <select id="priority-filter" name="priority"
                            class="px-4 py-2 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]">
                            <option value="all" <?= $priorityFilter === 'all' ? 'selected' : '' ?>>كل الأولويات</option>
                            <option value="عالية" <?= $priorityFilter === 'عالية' ? 'selected' : '' ?>>عالية</option>
                            <option value="متوسطة" <?= $priorityFilter === 'متوسطة' ? 'selected' : '' ?>>متوسطة</option>
                            <option value="منخفضة" <?= $priorityFilter === 'منخفضة' ? 'selected' : '' ?>>منخفضة</option>
                        </select>
                    </div>
                    <?php if ($adminView): ?>
                    <div>
                        <select id="user-filter" name="user_id"
                            class="px-4 py-2 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]">
                            <option value="">كل الموظفين</option>
                            <?php foreach ($employees as $emp): ?>
                            <option value="<?= $emp['id'] ?>" <?= $filterUserId === (int)$emp['id'] ? 'selected' : '' ?>><?= htmlspecialchars($emp['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div>
                        <input type="date" id="date-from" name="date_from" value="<?= htmlspecialchars($dateFrom ?? '') ?>"
                            placeholder="من تاريخ"
                            class="px-4 py-2 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]">
                    </div>
                    <div>
                        <input type="date" id="date-to" name="date_to" value="<?= htmlspecialchars($dateTo ?? '') ?>"
                            placeholder="إلى تاريخ"
                            class="px-4 py-2 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]">
                    </div>
                    <button type="button" id="clear-filters"
                        class="px-4 py-2 text-[#666666] hover:text-[#111111] transition-colors">
                        مسح الفلاتر
                    </button>
                </div>
            </div>

            <?php if (empty($files)): ?>
            <div class="p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-[#666666] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-[#666666]">لا توجد ملفات</p>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-right px-4 py-3 text-sm font-semibold text-[#111111]">اسم الملف</th>
                            <?php if ($adminView): ?>
                            <th class="text-right px-4 py-3 text-sm font-semibold text-[#111111]">المالك</th>
                            <?php endif; ?>
                            <th class="text-right px-4 py-3 text-sm font-semibold text-[#111111]">الأولوية</th>
                            <th class="text-right px-4 py-3 text-sm font-semibold text-[#111111]">الحجم</th>
                            <th class="text-right px-4 py-3 text-sm font-semibold text-[#111111]">التاريخ</th>
                            <th class="text-right px-4 py-3 text-sm font-semibold text-[#111111]">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#e5e5e5]">
                        <?php foreach ($files as $file): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <?php
                                    $ext = strtolower(pathinfo($file['original_name'] ?? '', PATHINFO_EXTENSION));
                                    $iconClass = 'text-blue-500';
                                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) $iconClass = 'text-green-500';
                                    elseif ($ext === 'pdf') $iconClass = 'text-red-500';
                                    elseif (in_array($ext, ['doc', 'docx'])) $iconClass = 'text-blue-600';
                                    elseif (in_array($ext, ['xls', 'xlsx'])) $iconClass = 'text-green-600';
                                    ?>
                                    <svg class="h-8 w-8 <?= $iconClass ?>" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zm-1 2l5 5h-5V4zM8 17v-2h8v2H8zm0-4v-2h8v2H8z"/>
                                    </svg>
                                    <div>
                                        <p class="font-medium text-[#111111]"><?= htmlspecialchars($file['name']) ?></p>
                                        <p class="text-xs text-[#666666]"><?= htmlspecialchars($file['original_name'] ?? '') ?></p>
                                    </div>
                                </div>
                            </td>
                            <?php if ($adminView): ?>
                            <td class="px-4 py-3 text-[#111111]"><?= htmlspecialchars($file['uploader_name'] ?? 'غير معروف') ?></td>
                            <?php endif; ?>
                            <td class="px-4 py-3">
                                <?php
                                $priorityColors = [
                                    'عالية' => 'bg-red-100 text-red-800',
                                    'متوسطة' => 'bg-yellow-100 text-yellow-800',
                                    'منخفضة' => 'bg-green-100 text-green-800',
                                ];
                                $priorityColor = $priorityColors[$file['priority']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="px-2 py-1 text-xs rounded-full <?= $priorityColor ?>"><?= htmlspecialchars($file['priority']) ?></span>
                            </td>
                            <td class="px-4 py-3 text-[#666666]">
                                <?php
                                $size = (int)$file['file_size'];
                                $units = ['B', 'KB', 'MB', 'GB'];
                                $pow = $size > 0 ? floor(log($size) / log(1024)) : 0;
                                $pow = min($pow, count($units) - 1);
                                echo round($size / pow(1024, $pow), 1) . ' ' . $units[$pow];
                                ?>
                            </td>
                            <td class="px-4 py-3 text-[#666666]"><?= date('Y/m/d', strtotime($file['created_at'])) ?></td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <a href="<?= url('/files/' . $file['id'] . '/download') ?>"
                                        class="p-2 text-[#666666] hover:text-[#111111] hover:bg-gray-100 rounded-lg transition-colors" title="تحميل">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                    </a>
                                    <a href="<?= url('/files/' . $file['id'] . '/edit') ?>"
                                        class="p-2 text-[#666666] hover:text-[#111111] hover:bg-gray-100 rounded-lg transition-colors" title="تعديل">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <button type="button" data-file-id="<?= $file['id'] ?>" data-file-name="<?= htmlspecialchars($file['name']) ?>"
                                        class="delete-btn p-2 text-[#666666] hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="حذف">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <?php if ($totalPages > 1): ?>
            <div class="p-4 border-t border-[#e5e5e5] flex items-center justify-between">
                <div class="text-sm text-[#666666]">
                    عرض <?= (($page - 1) * $perPage) + 1 ?> - <?= min($page * $perPage, $total) ?> من <?= $total ?> ملف
                </div>
                <div class="flex items-center gap-2">
                    <?php if ($page > 1): ?>
                    <a href="<?= url('/files?' . http_build_query(array_merge($_GET, ['page' => $page - 1]))) ?>"
                        class="px-3 py-1 border border-[#e5e5e5] rounded-lg hover:bg-gray-50 transition-colors">السابق</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <?php if ($i === $page): ?>
                    <span class="px-3 py-1 bg-[#F4C400] text-[#111111] rounded-lg font-bold"><?= $i ?></span>
                    <?php else: ?>
                    <a href="<?= url('/files?' . http_build_query(array_merge($_GET, ['page' => $i]))) ?>"
                        class="px-3 py-1 border border-[#e5e5e5] rounded-lg hover:bg-gray-50 transition-colors"><?= $i ?></a>
                    <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                    <a href="<?= url('/files?' . http_build_query(array_merge($_GET, ['page' => $page + 1]))) ?>"
                        class="px-3 py-1 border border-[#e5e5e5] rounded-lg hover:bg-gray-50 transition-colors">التالي</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="delete-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 max-w-md mx-4">
        <h3 class="text-xl font-bold text-[#111111] mb-4">تأكيد الحذف</h3>
        <p class="text-[#666666] mb-6">هل أنت متأكد من حذف هذا الملف؟</p>
        <p id="delete-file-name" class="font-bold text-[#111111] mb-6"></p>
        <input type="hidden" id="delete-file-id">
        <div class="flex items-center justify-end gap-3">
            <button type="button" id="cancel-delete" class="px-4 py-2 border border-[#e5e5e5] rounded-lg hover:bg-gray-50 transition-colors">إلغاء</button>
            <button type="button" id="confirm-delete" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">حذف</button>
        </div>
    </div>
</div>