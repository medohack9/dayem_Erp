<div class="p-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[#111111] mb-2">رفع ملف جديد</h1>
                <p class="text-[#666666]">املأ البيانات لرفع ملف جديد</p>
            </div>
            <a href="<?= url('/files') ?>" class="text-[#666666] hover:text-[#111111] transition-colors">← العودة للملفات</a>
        </div>

        <?php if (!$storageInfo['unlimited']): ?>
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-[#666666]">مساحة التخزين المستخدمة:</span>
                    <span class="font-bold text-[#111111] mr-2"><?= htmlspecialchars($storageInfo['storage_used_formatted']) ?> / <?= htmlspecialchars($storageInfo['storage_quota_formatted']) ?></span>
                </div>
                <div class="w-32 bg-gray-200 rounded-full h-2.5">
                    <div class="bg-[#F4C400] h-2.5 rounded-full" style="width: <?= min($storageInfo['percentage'] ?? 0, 100) ?>%"></div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <form id="upload-file-form" enctype="multipart/form-data">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-xl p-6 border border-[#e5e5e5]">
                        <h3 class="text-lg font-bold text-[#111111] mb-6">بيانات الملف</h3>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-[#111111] mb-2" for="name">اسم الملف <span class="text-red-500">*</span></label>
                                <input type="text" id="name" name="name" dir="rtl"
                                    class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]"
                                    placeholder="ادخل اسم الملف" required maxlength="200">
                                <div id="name-error" class="text-red-600 text-sm mt-1 hidden"></div>
                            </div>
                            <div>
                                <label class="block text-[#111111] mb-2" for="priority">الأولوية <span class="text-red-500">*</span></label>
                                <select id="priority" name="priority" dir="rtl"
                                    class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]" required>
                                    <option value="متوسطة" selected>متوسطة</option>
                                    <option value="منخفضة">منخفضة</option>
                                    <option value="عالية">عالية</option>
                                </select>
                                <div id="priority-error" class="text-red-600 text-sm mt-1 hidden"></div>
                            </div>
                            <div>
                                <label class="block text-[#111111] mb-2" for="notes">ملاحظات</label>
                                <textarea id="notes" name="notes" dir="rtl" rows="3"
                                    class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]"
                                    placeholder="ملاحظات اختيارية (حد أقصى 1000 حرف)" maxlength="1000"></textarea>
                                <div id="notes-error" class="text-red-600 text-sm mt-1 hidden"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white rounded-xl p-6 border border-[#e5e5e5]">
                        <h3 class="text-lg font-bold text-[#111111] mb-6">الملف</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block w-full cursor-pointer">
                                    <div id="file-drop-zone" class="flex items-center justify-center px-4 py-8 border-2 border-dashed border-[#e5e5e5] rounded-lg hover:border-[#F4C400] transition-colors">
                                        <div class="text-center">
                                            <svg class="mx-auto h-12 w-12 text-[#666666]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                            </svg>
                                            <p class="text-sm text-[#666666] mt-2" id="file-label">اضغط لاختيار ملف</p>
                                        </div>
                                    </div>
                                    <input type="file" id="file" name="file"
                                        class="hidden" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.zip,.rar" required>
                                </label>
                                <p class="text-xs text-[#666666] mt-2">الحد الأقصى: 5 ميجابايت</p>
                                <p class="text-xs text-[#666666]">الأنواع المسموحة: JPG, PNG, GIF, PDF, DOC, DOCX, XLS, XLSX, ZIP, RAR</p>
                                <div id="file-error" class="text-red-600 text-sm mt-1 hidden"></div>
                            </div>
                        </div>
                    </div>

                    <div id="upload-progress" class="hidden bg-white rounded-xl p-6 border border-[#e5e5e5]">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-[#666666]">جاري الرفع...</span>
                            <span id="progress-percent" class="text-sm font-bold text-[#111111]">0%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div id="progress-bar" class="bg-[#F4C400] h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                    </div>

                    <button type="submit" id="submit-btn"
                        class="w-full bg-[#F4C400] text-[#111111] py-3 px-4 rounded-lg font-bold hover:bg-[#e0b300] transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        رفع الملف
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>