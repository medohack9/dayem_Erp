<div class="p-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[#111111] mb-2">تعديل الملف</h1>
                <p class="text-[#666666]">تعديل بيانات الملف (الملف نفسه لا يمكن تغييره)</p>
            </div>
            <a href="<?= url('/files') ?>" class="text-[#666666] hover:text-[#111111] transition-colors">← العودة للملفات</a>
        </div>

        <form id="edit-file-form">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="file_id" value="<?= $file['id'] ?>">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-xl p-6 border border-[#e5e5e5]">
                        <h3 class="text-lg font-bold text-[#111111] mb-6">بيانات الملف</h3>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-[#111111] mb-2" for="name">اسم الملف <span class="text-red-500">*</span></label>
                                <input type="text" id="name" name="name" dir="rtl" value="<?= htmlspecialchars($file['name']) ?>"
                                    class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]"
                                    placeholder="ادخل اسم الملف" required maxlength="200">
                                <div id="name-error" class="text-red-600 text-sm mt-1 hidden"></div>
                            </div>
                            <div>
                                <label class="block text-[#111111] mb-2" for="priority">الأولوية <span class="text-red-500">*</span></label>
                                <select id="priority" name="priority" dir="rtl"
                                    class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]" required>
                                    <option value="متوسطة" <?= $file['priority'] === 'متوسطة' ? 'selected' : '' ?>>متوسطة</option>
                                    <option value="منخفضة" <?= $file['priority'] === 'منخفضة' ? 'selected' : '' ?>>منخفضة</option>
                                    <option value="عالية" <?= $file['priority'] === 'عالية' ? 'selected' : '' ?>>عالية</option>
                                </select>
                                <div id="priority-error" class="text-red-600 text-sm mt-1 hidden"></div>
                            </div>
                            <div>
                                <label class="block text-[#111111] mb-2" for="notes">ملاحظات</label>
                                <textarea id="notes" name="notes" dir="rtl" rows="3"
                                    class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]"
                                    placeholder="ملاحظات اختيارية (حد أقصى 1000 حرف)" maxlength="1000"><?= htmlspecialchars($file['notes'] ?? '') ?></textarea>
                                <div id="notes-error" class="text-red-600 text-sm mt-1 hidden"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white rounded-xl p-6 border border-[#e5e5e5]">
                        <h3 class="text-lg font-bold text-[#111111] mb-6">معلومات الملف</h3>
                        <div class="space-y-4">
                            <div>
                                <span class="text-sm text-[#666666]">اسم الملف الأصلي:</span>
                                <p class="font-medium text-[#111111]"><?= htmlspecialchars($file['original_name']) ?></p>
                            </div>
                            <div>
                                <span class="text-sm text-[#666666]">حجم الملف:</span>
                                <p class="font-medium text-[#111111]">
                                    <?php
                                    $size = (int)$file['file_size'];
                                    $units = ['B', 'KB', 'MB', 'GB'];
                                    $pow = $size > 0 ? floor(log($size) / log(1024)) : 0;
                                    $pow = min($pow, count($units) - 1);
                                    echo round($size / pow(1024, $pow), 1) . ' ' . $units[$pow];
                                    ?>
                                </p>
                            </div>
                            <div>
                                <span class="text-sm text-[#666666]">تاريخ الرفع:</span>
                                <p class="font-medium text-[#111111]"><?= date('Y/m/d H:i', strtotime($file['created_at'])) ?></p>
                            </div>
                            <div class="pt-4 border-t border-[#e5e5e5]">
                                <p class="text-sm text-[#666666]">
                                    <svg class="inline h-4 w-4 text-yellow-500 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2L1 21h22L12 2zm0 3.83L19.53 19H4.47L12 5.83zM11 16h2v2h-2v-2zm0-6h2v4h-2v-4z"/>
                                    </svg>
                                    ملاحظة: لا يمكن تغيير محتوى الملف نفسه، فقط البيانات الوصفية
                                </p>
                            </div>
                        </div>
                    </div>

                    <button type="submit" id="submit-btn"
                        class="w-full bg-[#F4C400] text-[#111111] py-3 px-4 rounded-lg font-bold hover:bg-[#e0b300] transition-colors">
                        حفظ التغييرات
                    </button>
                    <a href="<?= url('/files') ?>" class="block w-full text-center px-4 py-3 border border-[#e5e5e5] rounded-lg text-[#666666] hover:bg-gray-50 transition-colors">
                        إلغاء
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>