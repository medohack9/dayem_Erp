<div class="p-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[#111111] mb-2">تعديل بيانات الموظف</h1>
                <p class="text-[#666666]">تعديل بيانات <?= htmlspecialchars($employee['name'], ENT_QUOTES, 'UTF-8') ?></p>
            </div>
            <a href="<?= url('/employees') ?>" class="text-[#666666] hover:text-[#111111] transition-colors">← العودة للموظفين</a>
        </div>

        <form id="edit-employee-form">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="employee_id" value="<?= $employee['id'] ?>">

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
                                    placeholder="ادخل الاسم الكامل" required maxlength="100"
                                    value="<?= htmlspecialchars($employee['name'], ENT_QUOTES, 'UTF-8') ?>">
                                <div id="name-error" class="text-red-600 text-sm mt-1 hidden"></div>
                            </div>
                            <div>
                                <label class="block text-[#111111] mb-2" for="email">البريد الإلكتروني <span class="text-red-500">*</span></label>
                                <input type="email" id="email" name="email" dir="ltr"
                                    class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]"
                                    placeholder="example@dayem.com" required
                                    value="<?= htmlspecialchars($employee['email'], ENT_QUOTES, 'UTF-8') ?>">
                                <div id="email-error" class="text-red-600 text-sm mt-1 hidden"></div>
                            </div>
                            <div>
                                <label class="block text-[#111111] mb-2" for="phone">رقم الموبايل <span class="text-red-500">*</span></label>
                                <input type="tel" id="phone" name="phone" dir="ltr"
                                    class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]"
                                    placeholder="01012345678" required
                                    value="<?= htmlspecialchars($employee['phone'], ENT_QUOTES, 'UTF-8') ?>">
                                <div id="phone-error" class="text-red-600 text-sm mt-1 hidden"></div>
                            </div>
                            <div>
                                <label class="block text-[#111111] mb-2" for="national_id">الرقم القومي <span class="text-red-500">*</span></label>
                                <input type="text" id="national_id" name="national_id" dir="ltr" maxlength="14"
                                    class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]"
                                    placeholder="29901011234567" required
                                    value="<?= htmlspecialchars($employee['national_id'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                <div id="national_id-error" class="text-red-600 text-sm mt-1 hidden"></div>
                            </div>
                            <div>
                                <label class="block text-[#111111] mb-2" for="birth_date">تاريخ الميلاد <span class="text-red-500">*</span></label>
                                <input type="date" id="birth_date" name="birth_date"
                                    class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]"
                                    required
                                    value="<?= htmlspecialchars($employee['birth_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
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
                                    placeholder="15000" required
                                    value="<?= $employee['salary'] !== null ? htmlspecialchars($employee['salary'], ENT_QUOTES, 'UTF-8') : '' ?>">
                                <div id="salary-error" class="text-red-600 text-sm mt-1 hidden"></div>
                            </div>
                            <div>
                                <label class="block text-[#111111] mb-2" for="department_id">القسم <span class="text-red-500">*</span></label>
                                <select id="department_id" name="department_id" dir="rtl"
                                    class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]"
                                    required>
                                    <option value="">اختر القسم</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?= $dept['id'] ?>" <?= ($employee['department_id'] == $dept['id']) ? 'selected' : '' ?>><?= htmlspecialchars($dept['name'], ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div id="department_id-error" class="text-red-600 text-sm mt-1 hidden"></div>
                            </div>
                            <div>
                                <label class="block text-[#111111] mb-2" for="hire_date">تاريخ التعيين</label>
                                <input type="date" id="hire_date" name="hire_date"
                                    class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]"
                                    value="<?= htmlspecialchars($employee['hire_date'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div>
                                <label class="block text-[#111111] mb-2" for="status">الحالة</label>
                                <select id="status" name="status" dir="rtl"
                                    class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]">
                                    <option value="active" <?= $employee['status'] === 'active' ? 'selected' : '' ?>>نشط</option>
                                    <option value="suspended" <?= $employee['status'] === 'suspended' ? 'selected' : '' ?>>موقوف</option>
                                    <option value="terminated" <?= $employee['status'] === 'terminated' ? 'selected' : '' ?>>منتهي الخدمة</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Documents -->
                    <div class="bg-white rounded-xl p-6 border border-[#e5e5e5]">
                        <h3 class="text-lg font-bold text-[#111111] mb-6">المستندات</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-[#111111] mb-2">عقد العمل</label>
                                <?php
                                $contractDoc = null;
                                foreach ($documents as $doc) {
                                    if ($doc['doc_type'] === 'contract') { $contractDoc = $doc; break; }
                                }
                                ?>
                                <div id="contract-upload-area">
                                    <?php if ($contractDoc): ?>
                                        <div class="flex items-center gap-3 p-3 bg-[#fafafa] rounded-lg border border-[#e5e5e5]" id="contract-file-row">
                                            <svg class="w-5 h-5 text-[#666666] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <a href="<?= url('/documents/' . $contractDoc['id'] . '/' . $contractDoc['stored_name']) ?>" target="_blank" class="text-[#111111] hover:underline flex-1 truncate"><?= htmlspecialchars($contractDoc['original_name'], ENT_QUOTES, 'UTF-8') ?></a>
                                            <button type="button" class="text-red-500 hover:text-red-700 text-sm delete-doc-btn" data-doc-id="<?= $contractDoc['id'] ?>">حذف</button>
                                        </div>
                                    <?php else: ?>
                                        <label class="cursor-pointer block border-2 border-dashed border-[#e5e5e5] rounded-lg p-4 text-center hover:border-[#F4C400] transition-colors">
                                            <svg class="w-6 h-6 text-[#666666] mx-auto mb-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                            <p class="text-[#666666] text-sm">اضغط لرفع عقد العمل</p>
                                            <p class="text-xs text-[#666666]">PDF, DOC, DOCX</p>
                                            <input type="file" class="hidden doc-upload-input" data-employee-id="<?= $employee['id'] ?>" data-doc-type="contract" accept=".pdf,.doc,.docx">
                                        </label>
                                    <?php endif; ?>
                                </div>
                                <div id="contract-upload-status" class="text-sm mt-1 hidden"></div>
                            </div>

                            <div>
                                <label class="block text-[#111111] mb-2">صورة الرقم القومي</label>
                                <?php
                                $nationalIdDoc = null;
                                foreach ($documents as $doc) {
                                    if ($doc['doc_type'] === 'national_id') { $nationalIdDoc = $doc; break; }
                                }
                                ?>
                                <div id="national-id-upload-area">
                                    <?php if ($nationalIdDoc): ?>
                                        <div class="flex items-center gap-3 p-3 bg-[#fafafa] rounded-lg border border-[#e5e5e5]" id="national-id-file-row">
                                            <svg class="w-5 h-5 text-[#666666] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <a href="<?= url('/documents/' . $nationalIdDoc['id'] . '/' . $nationalIdDoc['stored_name']) ?>" target="_blank" class="text-[#111111] hover:underline flex-1 truncate"><?= htmlspecialchars($nationalIdDoc['original_name'], ENT_QUOTES, 'UTF-8') ?></a>
                                            <button type="button" class="text-red-500 hover:text-red-700 text-sm delete-doc-btn" data-doc-id="<?= $nationalIdDoc['id'] ?>">حذف</button>
                                        </div>
                                    <?php else: ?>
                                        <label class="cursor-pointer block border-2 border-dashed border-[#e5e5e5] rounded-lg p-4 text-center hover:border-[#F4C400] transition-colors">
                                            <svg class="w-6 h-6 text-[#666666] mx-auto mb-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                            <p class="text-[#666666] text-sm">اضغط لرفع صورة الرقم القومي</p>
                                            <p class="text-xs text-[#666666]">JPG, PNG, PDF</p>
                                            <input type="file" class="hidden doc-upload-input" data-employee-id="<?= $employee['id'] ?>" data-doc-type="national_id" accept=".jpg,.jpeg,.png,.pdf">
                                        </label>
                                    <?php endif; ?>
                                </div>
                                <div id="national-id-upload-status" class="text-sm mt-1 hidden"></div>
                            </div>

                            <div>
                                <label class="block text-[#111111] mb-2">مستندات إضافية</label>
                                <?php
                                $otherDocs = array_filter($documents, fn($d) => $d['doc_type'] === 'other');
                                ?>
                                <div id="other-docs-list" class="space-y-2 mb-3">
                                    <?php foreach ($otherDocs as $doc): ?>
                                        <div class="flex items-center gap-3 p-3 bg-[#fafafa] rounded-lg border border-[#e5e5e5] other-doc-row" data-doc-id="<?= $doc['id'] ?>">
                                            <svg class="w-5 h-5 text-[#666666] flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <a href="<?= url('/documents/' . $doc['id'] . '/' . $doc['stored_name']) ?>" target="_blank" class="text-[#111111] hover:underline flex-1 truncate"><?= htmlspecialchars($doc['original_name'], ENT_QUOTES, 'UTF-8') ?></a>
                                            <button type="button" class="text-red-500 hover:text-red-700 text-sm delete-doc-btn" data-doc-id="<?= $doc['id'] ?>">حذف</button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <label class="cursor-pointer block border-2 border-dashed border-[#e5e5e5] rounded-lg p-4 text-center hover:border-[#F4C400] transition-colors">
                                    <svg class="w-6 h-6 text-[#666666] mx-auto mb-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                    <p class="text-[#666666] text-sm">اضغط لرفع مستندات إضافية</p>
                                    <p class="text-xs text-[#666666]">PDF, DOC, DOCX, JPG, PNG</p>
                                    <input type="file" class="hidden doc-upload-input" data-employee-id="<?= $employee['id'] ?>" data-doc-type="other" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                </label>
                                <div id="other-upload-status" class="text-sm mt-1 hidden"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <div class="bg-white rounded-xl p-6 border border-[#e5e5e5] text-center">
                        <h3 class="text-lg font-bold text-[#111111] mb-4">صورة شخصية</h3>
                        <?php
                        $profilePhoto = null;
                        foreach ($documents as $doc) {
                            if ($doc['doc_type'] === 'profile_photo') {
                                $profilePhoto = $doc;
                                break;
                            }
                        }
                        ?>
                        <div id="profile-photo-container" class="mb-3">
                            <?php if ($profilePhoto): ?>
                                <img src="<?= url('/documents/' . $profilePhoto['id'] . '/' . $profilePhoto['stored_name']) ?>" alt="صورة شخصية" class="w-24 h-24 rounded-full mx-auto object-cover border-2 border-[#F4C400]">
                            <?php else: ?>
                                <div class="w-24 h-24 bg-[#F4C400] rounded-full mx-auto flex items-center justify-center">
                                    <span class="text-[#111111] text-2xl font-bold"><?= mb_substr($employee['name'], 0, 1) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <label class="cursor-pointer inline-block mt-2 px-4 py-2 text-sm bg-[#fafafa] border border-[#e5e5e5] rounded-lg hover:bg-[#f0f0f0] transition-colors">
                            <span>تغيير الصورة</span>
                            <input type="file" id="profile-photo-input" accept="image/jpeg,image/png,image/webp" class="hidden" data-employee-id="<?= $employee['id'] ?>">
                        </label>
                        <div id="photo-upload-status" class="text-sm mt-2 hidden"></div>
                        <p class="text-[#111111] font-bold mt-3"><?= htmlspecialchars($employee['name'], ENT_QUOTES, 'UTF-8') ?></p>
                        <p class="text-[#666666] text-sm"><?= htmlspecialchars($employee['employee_code'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                    </div>

                    <div class="bg-white rounded-xl p-6 border border-[#e5e5e5]">
                        <div id="form-error" class="text-red-600 text-sm mb-3 hidden"></div>
                        <div id="form-success" class="text-green-600 text-sm mb-3 hidden"></div>
                        <button type="submit" id="submit-btn" class="w-full bg-[#F4C400] text-[#111111] py-3 px-6 rounded-lg font-bold hover:bg-[#e5b600] transition-colors mb-3">
                            تحديث البيانات
                        </button>
                        <a href="<?= url('/employees') ?>" class="block w-full text-center bg-white text-[#111111] py-3 px-6 rounded-lg font-bold border border-[#e5e5e5] hover:bg-[#fafafa] transition-colors">
                            إلغاء
                        </a>
                    </div>

                    <!-- Reset Password -->
                    <div class="bg-white rounded-xl p-6 border border-[#e5e5e5]">
                        <button type="button" id="toggle-password-section" class="w-full flex items-center justify-between text-[#111111]">
                            <h3 class="text-lg font-bold">تغيير كلمة المرور</h3>
                            <svg id="password-chevron" class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div id="password-section" class="hidden mt-4 space-y-3">
                            <div>
                                <label class="block text-[#111111] mb-2 text-sm" for="new_password">كلمة المرور الجديدة</label>
                                <input type="password" id="new_password" class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]" placeholder="8 أحرف على الأقل" minlength="8">
                                <div id="new_password-error" class="text-red-600 text-sm mt-1 hidden"></div>
                            </div>
                            <div>
                                <label class="block text-[#111111] mb-2 text-sm" for="confirm_password">تأكيد كلمة المرور</label>
                                <input type="password" id="confirm_password" class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]" placeholder="أعد إدخال كلمة المرور" minlength="8">
                                <div id="confirm_password-error" class="text-red-600 text-sm mt-1 hidden"></div>
                            </div>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" id="force_change" checked class="w-4 h-4 text-[#F4C400] border-[#e5e5e5] rounded focus:ring-[#F4C400]">
                                <span class="text-sm text-[#111111]">إلزام الموظف بتغييرها عند الدخول</span>
                            </label>
                            <div id="password-change-error" class="text-red-600 text-sm hidden"></div>
                            <div id="password-change-success" class="text-green-600 text-sm hidden"></div>
                            <button type="button" id="change-password-btn" class="w-full bg-[#111111] text-white py-2.5 px-4 rounded-lg font-bold hover:bg-[#333333] transition-colors text-sm">
                                تغيير كلمة المرور
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>