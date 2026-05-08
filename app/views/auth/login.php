<form id="login-form" method="POST" action="<?= url('/auth/login') ?>">
    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8') ?>">

    <div class="space-y-6">
        <?php if (!empty($flashMessage)): ?>
            <div class="p-3 rounded-lg text-sm <?= $flashType === 'success' ? 'bg-green-100 text-green-800 border border-green-300' : ($flashType === 'error' ? 'bg-red-100 text-red-800 border border-red-300' : 'bg-yellow-100 text-yellow-800 border border-yellow-300') ?>">
                <?= htmlspecialchars($flashMessage, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <div>
            <label class="block text-[#111111] mb-2" for="identifier">البريد الإلكتروني أو رقم الموبايل</label>
            <input type="text" id="identifier" name="identifier" dir="ltr"
                class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]"
                placeholder="ادخل البريد الإلكتروني أو رقم الموبايل"
                value="<?= htmlspecialchars($identifier ?? '', ENT_QUOTES, 'UTF-8') ?>"
                required>
            <div id="identifier-error" class="text-red-600 text-sm mt-1 hidden"></div>
        </div>

        <div>
            <label class="block text-[#111111] mb-2" for="password">كلمة المرور</label>
            <input type="password" id="password" name="password" dir="ltr"
                class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]"
                placeholder="ادخل كلمة المرور"
                required>
            <div id="password-error" class="text-red-600 text-sm mt-1 hidden"></div>
        </div>

        <div id="login-error" class="text-red-600 text-sm text-center mb-3 hidden"></div>

        <button type="submit" id="login-btn" class="w-full bg-[#F4C400] text-[#111111] py-3 px-6 rounded-lg font-bold hover:bg-[#e5b600] transition-colors">
            تسجيل الدخول
        </button>
    </div>
</form>