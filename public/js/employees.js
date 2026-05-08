// Employee CRUD JavaScript

document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || window.csrfToken;

    // Default salary suggestion on create form
    const deptSelect = document.getElementById('department_id');
    const salaryInput = document.getElementById('salary');

    if (deptSelect && salaryInput && !document.getElementById('edit-employee-form')) {
        deptSelect.addEventListener('change', function () {
            const deptId = this.value;
            if (!deptId) return;

            fetch(window.baseUrl + '/api/departments/' + deptId + '/default-salary', {
                headers: { 'Accept': 'application/json' }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.default_salary !== null && data.default_salary > 0) {
                        if (!salaryInput.value) {
                            salaryInput.value = data.default_salary;
                        }
                    }
                })
                .catch(() => { });
        });
    }

    // Create Employee Form
    const createForm = document.getElementById('create-employee-form');
    if (createForm) {
        createForm.addEventListener('submit', function (e) {
            e.preventDefault();
            clearErrors();

            const data = {
                _csrf_token: csrfToken,
                name: document.getElementById('name').value.trim(),
                email: document.getElementById('email').value.trim(),
                phone: document.getElementById('phone').value.trim(),
                national_id: document.getElementById('national_id').value.trim(),
                birth_date: document.getElementById('birth_date').value,
                salary: document.getElementById('salary').value,
                department_id: document.getElementById('department_id').value,
                hire_date: document.getElementById('hire_date').value || null,
                password: document.getElementById('password').value,
                password_confirm: document.getElementById('password_confirm').value,
            };

            fetch(window.baseUrl + '/employees', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(data)
            })
                .then(res => res.json().then(body => ({ status: res.status, body })))
                .then(({ status, body }) => {
                    if (body.success) {
                        window.location.href = window.baseUrl + '/employees';
                    } else if (body.errors) {
                        showErrors(body.errors);
                    } else {
                        showFormError(body.message || 'حدث خطأ غير متوقع');
                    }
                })
                .catch(() => showFormError('حدث خطأ في الاتصال'));
        });
    }

    // Edit Employee Form
    const editForm = document.getElementById('edit-employee-form');
    if (editForm) {
        editForm.addEventListener('submit', function (e) {
            e.preventDefault();
            clearErrors();

            const employeeId = document.querySelector('input[name="employee_id"]').value;
            const data = {
                _csrf_token: csrfToken,
                name: document.getElementById('name').value.trim(),
                email: document.getElementById('email').value.trim(),
                phone: document.getElementById('phone').value.trim(),
                national_id: document.getElementById('national_id').value.trim(),
                birth_date: document.getElementById('birth_date').value,
                salary: document.getElementById('salary').value,
                department_id: document.getElementById('department_id').value,
                hire_date: document.getElementById('hire_date').value || null,
                status: document.getElementById('status').value,
            };

            fetch(window.baseUrl + '/employees/' + employeeId, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(data)
            })
                .then(res => res.json().then(body => ({ status: res.status, body })))
                .then(({ status, body }) => {
                    if (body.success) {
                        showFormSuccess(body.message || 'تم تحديث البيانات بنجاح');
                        setTimeout(() => { window.location.href = window.baseUrl + '/employees'; }, 1500);
                    } else if (body.errors) {
                        showErrors(body.errors);
                    } else {
                        showFormError(body.message || 'حدث خطأ غير متوقع');
                    }
                })
                .catch(() => showFormError('حدث خطأ في الاتصال'));
        });
    }

    // Delete Employee
    const modal = document.getElementById('delete-modal');
    const cancelBtn = document.getElementById('cancel-delete');
    const confirmBtn = document.getElementById('confirm-delete');
    let deleteId = null;

    document.addEventListener('click', function (e) {
        if (e.target.closest('.delete-emp-btn')) {
            const btn = e.target.closest('.delete-emp-btn');
            deleteId = btn.dataset.id;
            const empName = btn.dataset.name;
            document.getElementById('delete-modal-message').textContent = 'هل أنت متأكد من حذف الموظف "' + empName + '"؟ لا يمكن التراجع عن هذا الإجراء.';
            modal.classList.remove('hidden');
        }
    });

    if (cancelBtn) {
        cancelBtn.addEventListener('click', function () {
            modal.classList.add('hidden');
            deleteId = null;
        });
    }

    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            if (!deleteId) return;

            fetch(window.baseUrl + '/employees/' + deleteId, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ _csrf_token: csrfToken })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = window.baseUrl + '/employees';
                    } else {
                        alert(data.message || 'حدث خطأ');
                        modal.classList.add('hidden');
                    }
                })
                .catch(() => {
                    alert('حدث خطأ في الاتصال');
                    modal.classList.add('hidden');
                });
        });
    }

    function showErrors(errors) {
        Object.keys(errors).forEach(function (field) {
            const errorEl = document.getElementById(field + '-error');
            if (errorEl) {
                errorEl.textContent = errors[field];
                errorEl.classList.remove('hidden');
            }
            const input = document.getElementById(field);
            if (input) {
                input.classList.add('border-red-500');
            }
        });
    }

    function clearErrors() {
        document.querySelectorAll('[id$="-error"]').forEach(function (el) {
            el.textContent = '';
            el.classList.add('hidden');
        });
        document.querySelectorAll('.border-red-500').forEach(function (el) {
            el.classList.remove('border-red-500');
        });
        const formError = document.getElementById('form-error');
        if (formError) { formError.classList.add('hidden'); }
        const formSuccess = document.getElementById('form-success');
        if (formSuccess) { formSuccess.classList.add('hidden'); }
    }

    function showFormError(msg) {
        const el = document.getElementById('form-error');
        if (el) { el.textContent = msg; el.classList.remove('hidden'); }
    }

    function showFormSuccess(msg) {
        const el = document.getElementById('form-success');
        if (el) { el.textContent = msg; el.classList.remove('hidden'); }
    }

    // Profile photo upload (edit form)
    const profilePhotoInput = document.getElementById('profile-photo-input');
    if (profilePhotoInput) {
        profilePhotoInput.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) return;
            const employeeId = this.dataset.employeeId;
            const formData = new FormData();
            formData.append('file', file);
            formData.append('user_id', employeeId);
            formData.append('doc_type', 'profile_photo');
            formData.append('_csrf_token', csrfToken);

            const statusEl = document.getElementById('photo-upload-status');
            if (statusEl) { statusEl.textContent = 'جاري الرفع...'; statusEl.classList.remove('hidden'); }

            fetch(window.baseUrl + '/documents/upload', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (statusEl) { statusEl.textContent = 'تم رفع الصورة بنجاح'; statusEl.classList.remove('hidden'); }
                    window.location.reload();
                } else {
                    if (statusEl) { statusEl.textContent = data.message || 'حدث خطأ'; statusEl.classList.remove('hidden'); }
                }
            })
            .catch(() => {
                if (statusEl) { statusEl.textContent = 'حدث خطأ في الاتصال'; statusEl.classList.remove('hidden'); }
            });
        });
    }

    // Document upload
    document.addEventListener('change', function (e) {
        if (!e.target.classList.contains('doc-upload-input')) return;
        const file = e.target.files[0];
        if (!file) return;
        const employeeId = e.target.dataset.employeeId;
        const docType = e.target.dataset.docType;
        const formData = new FormData();
        formData.append('file', file);
        formData.append('user_id', employeeId);
        formData.append('doc_type', docType);
        formData.append('_csrf_token', csrfToken);

        const statusMap = {
            'contract': 'contract-upload-status',
            'national_id': 'national-id-upload-status',
            'other': 'other-upload-status'
        };
        const statusEl = document.getElementById(statusMap[docType] || 'other-upload-status');
        if (statusEl) { statusEl.textContent = 'جاري الرفع...'; statusEl.classList.remove('hidden'); }

        fetch(window.baseUrl + '/documents/upload', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (statusEl) { statusEl.textContent = 'تم رفع المستند بنجاح'; statusEl.classList.remove('hidden'); }
                window.location.reload();
            } else {
                if (statusEl) { statusEl.textContent = data.message || 'حدث خطأ'; statusEl.classList.remove('hidden'); }
            }
        })
        .catch(() => {
            if (statusEl) { statusEl.textContent = 'حدث خطأ في الاتصال'; statusEl.classList.remove('hidden'); }
        });
    });

    // Document delete
    document.addEventListener('click', function (e) {
        if (!e.target.classList.contains('delete-doc-btn')) return;
        const docId = e.target.dataset.docId;
        if (!confirm('هل أنت متأكد من حذف هذا المستند؟')) return;

        fetch(window.baseUrl + '/documents/' + docId, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ _csrf_token: csrfToken, id: docId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'حدث خطأ');
            }
        })
        .catch(() => { alert('حدث خطأ في الاتصال'); });
    });

    // Toggle password section
    const toggleBtn = document.getElementById('toggle-password-section');
    const passwordSection = document.getElementById('password-section');
    const passwordChevron = document.getElementById('password-chevron');
    if (toggleBtn && passwordSection) {
        toggleBtn.addEventListener('click', function () {
            passwordSection.classList.toggle('hidden');
            if (passwordChevron) {
                passwordChevron.style.transform = passwordSection.classList.contains('hidden') ? '' : 'rotate(180deg)';
            }
        });
    }

    // Change password
    const changePasswordBtn = document.getElementById('change-password-btn');
    if (changePasswordBtn) {
        changePasswordBtn.addEventListener('click', function () {
            clearPasswordErrors();
            const employeeId = document.querySelector('input[name="employee_id"]').value;
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const forceChange = document.getElementById('force_change').checked;

            const errors = {};
            if (!newPassword) { errors.new_password = 'كلمة المرور الجديدة مطلوبة'; }
            else if (newPassword.length < 8) { errors.new_password = 'كلمة المرور يجب أن تكون 8 أحرف على الأقل'; }
            if (!confirmPassword) { errors.confirm_password = 'تأكيد كلمة المرور مطلوب'; }
            else if (newPassword !== confirmPassword) { errors.confirm_password = 'كلمة المرور وتأكيدها غير متطابقين'; }

            if (Object.keys(errors).length > 0) {
                showPasswordErrors(errors);
                return;
            }

            changePasswordBtn.disabled = true;
            changePasswordBtn.textContent = 'جاري التغيير...';

            fetch(window.baseUrl + '/employees/' + employeeId + '/password', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({
                    _csrf_token: csrfToken,
                    new_password: newPassword,
                    confirm_password: confirmPassword,
                    force_change: forceChange
                })
            })
            .then(res => res.json().then(body => ({ status: res.status, body })))
            .then(({ status, body }) => {
                changePasswordBtn.disabled = false;
                changePasswordBtn.textContent = 'تغيير كلمة المرور';
                if (body.success) {
                    var successEl = document.getElementById('password-change-success');
                    if (successEl) { successEl.textContent = body.message || 'تم تغيير كلمة المرور بنجاح'; successEl.classList.remove('hidden'); }
                    document.getElementById('new_password').value = '';
                    document.getElementById('confirm_password').value = '';
                    document.getElementById('force_change').checked = true;
                    setTimeout(function () { successEl.classList.add('hidden'); }, 4000);
                } else if (body.errors) {
                    showPasswordErrors(body.errors);
                } else {
                    var errorEl = document.getElementById('password-change-error');
                    if (errorEl) { errorEl.textContent = body.message || 'حدث خطأ'; errorEl.classList.remove('hidden'); }
                }
            })
            .catch(function () {
                changePasswordBtn.disabled = false;
                changePasswordBtn.textContent = 'تغيير كلمة المرور';
                var errorEl = document.getElementById('password-change-error');
                if (errorEl) { errorEl.textContent = 'حدث خطأ في الاتصال'; errorEl.classList.remove('hidden'); }
            });
        });
    }

    function showPasswordErrors(errors) {
        Object.keys(errors).forEach(function (field) {
            var errorEl = document.getElementById(field + '-error');
            if (errorEl) { errorEl.textContent = errors[field]; errorEl.classList.remove('hidden'); }
            var input = document.getElementById(field);
            if (input) { input.classList.add('border-red-500'); }
        });
    }

    function clearPasswordErrors() {
        ['new_password', 'confirm_password'].forEach(function (field) {
            var errorEl = document.getElementById(field + '-error');
            if (errorEl) { errorEl.textContent = ''; errorEl.classList.add('hidden'); }
            var input = document.getElementById(field);
            if (input) { input.classList.remove('border-red-500'); }
        });
        var errorEl = document.getElementById('password-change-error');
        if (errorEl) { errorEl.classList.add('hidden'); }
        var successEl = document.getElementById('password-change-success');
        if (successEl) { successEl.classList.add('hidden'); }
    }
});