<div class="p-8">
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[#111111] mb-2">تعديل القسم</h1>
                <p class="text-[#666666]">تعديل بيانات القسم</p>
            </div>
            <a href="<?= url('/departments') ?>" class="text-[#666666] hover:text-[#111111] transition-colors">← العودة للأقسام</a>
        </div>

        <div class="bg-white rounded-xl border border-[#e5e5e5] p-8">
            <form id="edit-department-form">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="department_id" value="<?= $department['id'] ?>">

                <div class="space-y-6">
                    <div>
                        <label class="block text-[#111111] mb-2" for="name">اسم القسم <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" dir="rtl"
                            class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]"
                            placeholder="مثال: الموارد البشرية" required maxlength="100"
                            value="<?= htmlspecialchars($department['name'], ENT_QUOTES, 'UTF-8') ?>">
                        <div id="name-error" class="text-red-600 text-sm mt-1 hidden"></div>
                    </div>

                    <div>
                        <label class="block text-[#111111] mb-2" for="manager_id">المدير</label>
                        <select id="manager_id" name="manager_id" dir="rtl"
                            class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]">
                            <option value="">— اختر المدير —</option>
                            <?php foreach ($availableManagers as $manager): ?>
                                <option value="<?= $manager['id'] ?>" <?= ($department['manager_id'] == $manager['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($manager['name'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="manager_id-error" class="text-red-600 text-sm mt-1 hidden"></div>
                    </div>

                    <div>
                        <label class="block text-[#111111] mb-2" for="default_salary">المرتب الافتراضي (ج.م)</label>
                        <input type="number" id="default_salary" name="default_salary" dir="ltr" step="0.01" min="0"
                            class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]"
                            placeholder="مثال: 5000"
                            value="<?= $department['default_salary'] !== null ? htmlspecialchars($department['default_salary'], ENT_QUOTES, 'UTF-8') : '' ?>">
                        <div id="default_salary-error" class="text-red-600 text-sm mt-1 hidden"></div>
                    </div>

                    <div id="form-error" class="text-red-600 text-sm text-center hidden"></div>
                    <div id="form-success" class="text-green-600 text-sm text-center hidden"></div>

                    <button type="submit" id="submit-btn" class="w-full bg-[#F4C400] text-[#111111] py-3 px-6 rounded-lg font-bold hover:bg-[#e5b600] transition-colors">
                        تحديث القسم
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>