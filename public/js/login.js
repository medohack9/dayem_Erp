document.addEventListener('DOMContentLoaded', function () {
    var baseUrl = window.baseUrl || '';

    var loginForm = document.getElementById('login-form');
    if (!loginForm) return;

    var loginBtn = document.getElementById('login-btn');
    var loginError = document.getElementById('login-error');
    var identifierError = document.getElementById('identifier-error');
    var passwordError = document.getElementById('password-error');

    loginForm.addEventListener('submit', function (e) {
        e.preventDefault();

        var identifier = document.getElementById('identifier').value.trim();
        var password = document.getElementById('password').value;
        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

        loginError.classList.add('hidden');
        loginError.textContent = '';
        identifierError.classList.add('hidden');
        identifierError.textContent = '';
        passwordError.classList.add('hidden');
        passwordError.textContent = '';

        if (!identifier) {
            identifierError.textContent = 'هذا الحقل مطلوب';
            identifierError.classList.remove('hidden');
            return;
        }

        if (!password) {
            passwordError.textContent = 'كلمة المرور مطلوبة';
            passwordError.classList.remove('hidden');
            return;
        }

        loginBtn.disabled = true;
        loginBtn.textContent = 'جاري التحميل...';

        fetch(baseUrl + '/auth/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken,
            },
            body: JSON.stringify({
                identifier: identifier,
                password: password,
                _csrf_token: csrfToken,
            }),
        })
        .then(function (response) {
            return response.json().then(function (data) {
                return { status: response.status, data: data };
            });
        })
        .then(function (result) {
            loginBtn.disabled = false;
            loginBtn.textContent = 'تسجيل الدخول';

            if (result.status === 422 && result.data.errors) {
                if (result.data.errors.identifier) {
                    identifierError.textContent = result.data.errors.identifier;
                    identifierError.classList.remove('hidden');
                }
                if (result.data.errors.password) {
                    passwordError.textContent = result.data.errors.password;
                    passwordError.classList.remove('hidden');
                }
                return;
            }

            if (result.status === 403) {
                loginError.textContent = result.data.message || 'طلب غير مصرح به';
                loginError.classList.remove('hidden');
                return;
            }

            if (result.data.success) {
                window.location.href = result.data.redirect || (baseUrl + '/');
            } else {
                loginError.textContent = result.data.message || 'بيانات الدخول غير صحيحة';
                loginError.classList.remove('hidden');
            }
        })
        .catch(function () {
            loginBtn.disabled = false;
            loginBtn.textContent = 'تسجيل الدخول';
            loginError.textContent = 'حدث خطأ في الاتصال. يرجى المحاولة مرة أخرى.';
            loginError.classList.remove('hidden');
        });
    });
});