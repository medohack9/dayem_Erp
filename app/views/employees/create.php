<div class="p-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[#111111] mb-2">إضافة موظف جديد</h1>
                <p class="text-[#666666]">املأ البيانات لإضافة موظف جديد للنظام</p>
            </div>
            <a href="<?= url('/employees') ?>" class="text-[#666666] hover:text-[#111111] transition-colors">← العودة للموظفين</a>
        </div>

        <form id="create-employee-form">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Info -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Personal Information -->
                    <div class="bg-white rounded-xl p-6 border border-[#e5e5e5]">
                        <h3 class="text-lg font-bold text-[#111111] mb-6">البيانات الشخصية</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[#111111] mb-2" for="name">الاسم بالكامل <span class="text-red-500">*</span></label>
                                <input type="text" id="name" name="name" dir="rtl"
                                    class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]"
                                    placeholder="ادخل الاسم الكامل" required maxlength="100">
                                <div id="name-error" class="text-red-600 text-sm mt-1 hidden"></div>
                            </div>
                            <div>
                                <label class="block text-[#111111] mb-2" for="email">البريد الإلكتروني <span class="text-red-500">*</span></label>
                                <input type="email" id="email" name="email" dir="ltr"
                                    class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]"
                                    placeholder="example@dayem.com" required>
                                <div id="email-error" class="text-red-600 text-sm mt-1 hidden"></div>
                            </div>
                            <div>
                                <label class="block text-[#111111] mb-2" for="phone">رقم الموبايل <span class="text-red-500">*</span></label>
                                <input type="tel" id="phone" name="phone" dir="ltr"
                                    class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]"
                                    placeholder="01012345678" required>
                                <div id="phone-error" class="text-red-600 text-sm mt-1 hidden"></div>
                            </div>
                            <div>
                                <label class="block text-[#111111] mb-2" for="national_id">الرقم القومي <span class="text-red-500">*</span></label>
                                <input type="text" id="national_id" name="national_id" dir="ltr" maxlength="14"
                                    class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]"
                                    placeholder="29901011234567" required>
                                <div id="national_id-error" class="text-red-600 text-sm mt-1 hidden"></div>
                            </div>
                            <div>
                                <label class="block text-[#111111] mb-2" for="birth_date">تاريخ الميلاد <span class="text-red-500">*</span></label>
                                <input type="date" id="birth_date" name="birth_date"
                                    class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]"
                                    required>
                                <div id="birth_date-error" class="text-red-600 text-sm mt-1 hidden"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Job Information -->
                    <div class="bg-white rounded-xl p-6 border border-[#e5e5e5]">
                        <h3 class="text-lg font-bold text-[#111111] mb-6">بيانات العمل</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[#111111] mb-2" for="salary">المرتب الشهري (ج.م) <span class="text-red-500">*</span></label>
                                <input type="number" id="salary" name="salary" dir="ltr" step="0.01" min="0"
                                    class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]"
                                    placeholder="15000" required>
                                <p class="text-sm text-[#666666] mt-1">جنيه مصري</p>
                                <div id="salary-error" class="text-red-600 text-sm mt-1 hidden"></div>
                            </div>
                            <div>
                                <label class="block text-[#111111] mb-2" for="department_id">القسم <span class="text-red-500">*</span></label>
                                <select id="department_id" name="department_id" dir="rtl"
                                    class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]"
                                    required>
                                    <option value="">اختر القسم</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name'], ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div id="department_id-error" class="text-red-600 text-sm mt-1 hidden"></div>
                            </div>
                            <div>
                                <label class="block text-[#111111] mb-2" for="hire_date">تاريخ التعيين</label>
                                <input type="date" id="hire_date" name="hire_date"
                                    class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]">
                                <p class="text-sm text-[#666666] mt-1">اتركه فارغاً لاستخدام تاريخ اليوم</p>
                            </div>
                            <div>
                                <label class="block text-[#111111] mb-2" for="password">كلمة المرور <span class="text-red-500">*</span></label>
                                <input type="password" id="password" name="password"
                                    class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]"
                                    placeholder="ادخل كلمة المرور" required minlength="8">
                                <div id="password-error" class="text-red-600 text-sm mt-1 hidden"></div>
                            </div>
                            <div>
                                <label class="block text-[#111111] mb-2" for="password_confirm">تأكيد كلمة المرور <span class="text-red-500">*</span></label>
                                <input type="password" id="password_confirm" name="password_confirm"
                                    class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]"
                                    placeholder="أعد إدخال كلمة المرور" required minlength="8">
                                <p class="text-sm text-[#666666] mt-1">سيُطلب من الموظف تغييرها عند أول دخول</p>
                                <div id="password_confirm-error" class="text-red-600 text-sm mt-1 hidden"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Actions -->
                <div class="space-y-6">
                    <div class="bg-white rounded-xl p-6 border border-[#e5e5e5] text-center">
                        <h3 class="text-lg font-bold text-[#111111] mb-4">صورة شخصية</h3>
                        <div id="create-photo-preview" class="w-24 h-24 bg-[#fafafa] rounded-full mx-auto mb-3 flex items-center justify-center overflow-hidden">
                            <svg class="w-10 h-10 text-[#666666]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"></path></svg>
                        </div>
                        <label class="cursor-pointer inline-block px-4 py-2 text-sm bg-[#fafafa] border border-[#e5e5e5] rounded-lg hover:bg-[#f0f0f0] transition-colors">
                            <span>اختر صورة</span>
                            <input type="file" id="create-photo-input" accept="image/jpeg,image/png,image/webp" class="hidden">
                        </label>
                        <p class="text-xs text-[#666666] mt-2">JPG, PNG - يتم رفعها بعد إنشاء الموظف</p>
                    </div>

                    <div class="bg-white rounded-xl p-6 border border-[#e5e5e5]">
                        <div id="form-error" class="text-red-600 text-sm mb-3 hidden"></div>
                        <div id="form-success" class="text-green-600 text-sm mb-3 hidden"></div>
                        <button type="submit" id="submit-btn" class="w-full bg-[#F4C400] text-[#111111] py-3 px-6 rounded-lg font-bold hover:bg-[#e5b600] transition-colors mb-3">
                            إضافة الموظف
                        </button>
                        <a href="<?= url('/employees') ?>" class="block w-full text-center bg-white text-[#111111] py-3 px-6 rounded-lg font-bold border border-[#e5e5e5] hover:bg-[#fafafa] transition-colors">
                            إلغاء
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>