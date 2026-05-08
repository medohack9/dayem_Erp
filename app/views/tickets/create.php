<div class="p-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[#111111] mb-2">تذكرة جديدة</h1>
                <p class="text-[#666666]">املأ البيانات لإنشاء تذكرة دعم جديدة</p>
            </div>
            <a href="<?= url('/tickets') ?>" class="text-[#666666] hover:text-[#111111] transition-colors">← العودة للتذاكر</a>
        </div>

        <form id="create-ticket-form" enctype="multipart/form-data">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-xl p-6 border border-[#e5e5e5]">
                        <h3 class="text-lg font-bold text-[#111111] mb-6">بيانات التذكرة</h3>
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-[#111111] mb-2" for="title">عنوان التذكرة <span class="text-red-500">*</span></label>
                                <input type="text" id="title" name="title" dir="rtl"
                                    class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]"
                                    placeholder="ادخل عنوان التذكرة" required maxlength="200">
                                <div id="title-error" class="text-red-600 text-sm mt-1 hidden"></div>
                            </div>
                            <div>
                                <label class="block text-[#111111] mb-2" for="description">وصف التذكرة <span class="text-red-500">*</span></label>
                                <textarea id="description" name="description" dir="rtl" rows="5"
                                    class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]"
                                    placeholder="اشرح المشكلة أو الطلب بالتفصيل" maxlength="5000" required></textarea>
                                <div class="flex justify-between">
                                    <div id="description-error" class="text-red-600 text-sm mt-1 hidden"></div>
                                    <span id="description-count" class="text-sm text-[#666666] mt-1">0 / 5000</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[#111111] mb-2" for="category">التصنيف <span class="text-red-500">*</span></label>
                                <select id="category" name="category" dir="rtl"
                                    class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]" required>
                                    <option value="">اختر التصنيف</option>
                                    <option value="تقرير مشكلة">تقرير مشكلة</option>
                                    <option value="طلب صيانة">طلب صيانة</option>
                                    <option value="طلب معلومات">طلب معلومات</option>
                                    <option value="أخرى">أخرى</option>
                                </select>
                                <div id="category-error" class="text-red-600 text-sm mt-1 hidden"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white rounded-xl p-6 border border-[#e5e5e5]">
                        <h3 class="text-lg font-bold text-[#111111] mb-6">المرفقات</h3>
                        <div class="space-y-4">
                            <div id="attachment-list" class="space-y-2"></div>
                            <div>
                                <label class="block w-full cursor-pointer">
                                    <div class="flex items-center justify-center px-4 py-3 border-2 border-dashed border-[#e5e5e5] rounded-lg hover:border-[#F4C400] transition-colors">
                                        <div class="text-center">
                                            <svg class="mx-auto h-8 w-8 text-[#666666]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            <p class="text-sm text-[#666666] mt-1">اضغط لإضافة ملفات</p>
                                        </div>
                                    </div>
                                    <input type="file" id="attachments" name="attachments[]" multiple
                                        class="hidden" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.zip">
                                </label>
                                <p class="text-xs text-[#666666] mt-2">الحد الأقصى: 10 ملفات، 5 ميجابايت لكل ملف</p>
                                <p class="text-xs text-[#666666]">الأنواع المسموحة: JPG, PNG, GIF, PDF, DOC, DOCX, XLS, XLSX, ZIP</p>
                                <div id="attachments-error" class="text-red-600 text-sm mt-1 hidden"></div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" id="submit-btn"
                        class="w-full bg-[#F4C400] text-[#111111] py-3 px-4 rounded-lg font-bold hover:bg-[#e0b300] transition-colors">
                        إنشاء التذكرة
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>