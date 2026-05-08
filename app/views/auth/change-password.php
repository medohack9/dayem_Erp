<div class="space-y-6">
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-4">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-yellow-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
            <div>
                <h3 class="text-yellow-800 font-bold">تغيير كلمة المرور مطلوب</h3>
                <p class="text-yellow-700 text-sm mt-1">يجب تغيير كلمة المرور قبل الاستمرار في استخدام النظام</p>
            </div>
        </div>
    </div>

    <form id="change-password-form">
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

        <div class="space-y-4">
            <div>
                <label class="block text-[#111111] mb-2 font-medium" for="current_password">كلمة المرور الحالية <span class="text-red-500">*</span></label>
                <input type="password" id="current_password" name="current_password"
                    class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]"
                    placeholder="ادخل كلمة المرور الحالية" required>
                <div id="current_password-error" class="text-red-600 text-sm mt-1 hidden"></div>
            </div>

            <div>
                <label class="block text-[#111111] mb-2 font-medium" for="new_password">كلمة المرور الجديدة <span class="text-red-500">*</span></label>
                <input type="password" id="new_password" name="new_password"
                    class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]"
                    placeholder="ادخل كلمة المرور الجديدة" required minlength="8">
                <p class="text-sm text-[#666666] mt-1">8 أحرف على الأقل</p>
                <div id="new_password-error" class="text-red-600 text-sm mt-1 hidden"></div>
            </div>

            <div>
                <label class="block text-[#111111] mb-2 font-medium" for="confirm_password">تأكيد كلمة المرور الجديدة <span class="text-red-500">*</span></label>
                <input type="password" id="confirm_password" name="confirm_password"
                    class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]"
                    placeholder="أعد إدخال كلمة المرور الجديدة" required minlength="8">
                <div id="confirm_password-error" class="text-red-600 text-sm mt-1 hidden"></div>
            </div>
        </div>

        <div id="form-error" class="text-red-600 text-sm mt-4 hidden"></div>
        <div id="form-success" class="text-green-600 text-sm mt-4 hidden"></div>

        <button type="submit" id="submit-btn" class="w-full mt-6 bg-[#F4C400] text-[#111111] py-3 px-6 rounded-lg font-bold hover:bg-[#e5b600] transition-colors">
            تغيير كلمة المرور
        </button>
    </form>
</div>

<script>
document.getElementById('change-password-form').addEventListener('submit', function(e) {
    e.preventDefault();
    clearErrors();

    const data = {
        _csrf_token: document.querySelector('meta[name="csrf-token"]').content,
        current_password: document.getElementById('current_password').value,
        new_password: document.getElementById('new_password').value,
        confirm_password: document.getElementById('confirm_password').value,
    };

    fetch(window.baseUrl + '/auth/change-password/post', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(res => res.json().then(body => ({ status: res.status, body })))
    .then(({ status, body }) => {
        if (body.success) {
            showFormSuccess(body.message || 'تم تغيير كلمة المرور بنجاح');
            setTimeout(function() {
                window.location.href = body.redirect || (window.baseUrl + '/');
            }, 1500);
        } else if (body.errors) {
            showErrors(body.errors);
        } else {
            showFormError(body.message || 'حدث خطأ غير متوقع');
        }
    })
    .catch(function() { showFormError('حدث خطأ في الاتصال'); });
});

function showErrors(errors) {
    Object.keys(errors).forEach(function(field) {
        var errorEl = document.getElementById(field + '-error');
        if (errorEl) { errorEl.textContent = errors[field]; errorEl.classList.remove('hidden'); }
        var input = document.getElementById(field);
        if (input) { input.classList.add('border-red-500'); }
    });
}

function clearErrors() {
    document.querySelectorAll('[id$="-error"]').forEach(function(el) { el.textContent = ''; el.classList.add('hidden'); });
    document.querySelectorAll('.border-red-500').forEach(function(el) { el.classList.remove('border-red-500'); });
    var formError = document.getElementById('form-error'); if (formError) { formError.classList.add('hidden'); }
    var formSuccess = document.getElementById('form-success'); if (formSuccess) { formSuccess.classList.add('hidden'); }
}

function showFormError(msg) {
    var el = document.getElementById('form-error'); if (el) { el.textContent = msg; el.classList.remove('hidden'); }
}

function showFormSuccess(msg) {
    var el = document.getElementById('form-success'); if (el) { el.textContent = msg; el.classList.remove('hidden'); }
}
</script>