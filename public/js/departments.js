var baseUrl = window.baseUrl || '';

function hideAllErrors() {
    var ids = ['name-error', 'manager_id-error', 'default_salary-error', 'form-error', 'form-success'];
    ids.forEach(function(id) {
        var el = document.getElementById(id);
        if (el) { el.classList.add('hidden'); el.textContent = ''; }
    });
}

function showError(id, msg) {
    var el = document.getElementById(id);
    if (el) { el.textContent = msg; el.classList.remove('hidden'); }
}

// === CREATE DEPARTMENT ===
var createForm = document.getElementById('create-department-form');
if (createForm) {
    createForm.addEventListener('submit', function(e) {
        e.preventDefault();
        hideAllErrors();

        var name = document.getElementById('name').value.trim();
        var managerId = document.getElementById('manager_id').value;
        var defaultSalary = document.getElementById('default_salary').value;
        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        var hasError = false;

        if (!name) { showError('name-error', 'اسم القسم مطلوب'); hasError = true; }
        if (hasError) return;

        var submitBtn = document.getElementById('submit-btn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'جاري الإنشاء...';

        var data = { name: name, _csrf_token: csrfToken };
        if (managerId) data.manager_id = parseInt(managerId);
        if (defaultSalary) data.default_salary = parseFloat(defaultSalary);

        fetch(baseUrl + '/departments', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
            body: JSON.stringify(data)
        })
        .then(function(res) { return res.json().then(function(d) { return { status: res.status, data: d }; }); })
        .then(function(result) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'إنشاء القسم';

            if (result.status === 422 && result.data.errors) {
                Object.keys(result.data.errors).forEach(function(field) {
                    showError(field + '-error', result.data.errors[field]);
                });
                return;
            }
            if (result.data.success) {
                var successEl = document.getElementById('form-success');
                if (successEl) { successEl.textContent = result.data.message; successEl.classList.remove('hidden'); }
                setTimeout(function() { window.location.href = baseUrl + '/departments'; }, 1000);
            } else {
                showError('form-error', result.data.message || 'حدث خطأ');
            }
        })
        .catch(function() {
            submitBtn.disabled = false;
            submitBtn.textContent = 'إنشاء القسم';
            showError('form-error', 'حدث خطأ في الاتصال');
        });
    });
}

// === EDIT DEPARTMENT ===
var editForm = document.getElementById('edit-department-form');
if (editForm) {
    editForm.addEventListener('submit', function(e) {
        e.preventDefault();
        hideAllErrors();

        var deptId = document.querySelector('input[name="department_id"]').value;
        var name = document.getElementById('name').value.trim();
        var managerId = document.getElementById('manager_id').value;
        var defaultSalary = document.getElementById('default_salary').value;
        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        var hasError = false;

        if (!name) { showError('name-error', 'اسم القسم مطلوب'); hasError = true; }
        if (hasError) return;

        var submitBtn = document.getElementById('submit-btn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'جاري التحديث...';

        var data = { name: name, _csrf_token: csrfToken };
        if (managerId) data.manager_id = parseInt(managerId);
        if (defaultSalary) data.default_salary = parseFloat(defaultSalary);

        fetch(baseUrl + '/departments/' + deptId, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
            body: JSON.stringify(data)
        })
        .then(function(res) { return res.json().then(function(d) { return { status: res.status, data: d }; }); })
        .then(function(result) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'تحديث القسم';

            if (result.status === 422 && result.data.errors) {
                Object.keys(result.data.errors).forEach(function(field) {
                    showError(field + '-error', result.data.errors[field]);
                });
                return;
            }
            if (result.data.success) {
                var successEl = document.getElementById('form-success');
                if (successEl) { successEl.textContent = result.data.message; successEl.classList.remove('hidden'); }
                setTimeout(function() { window.location.href = baseUrl + '/departments'; }, 1000);
            } else {
                showError('form-error', result.data.message || 'حدث خطأ');
            }
        })
        .catch(function() {
            submitBtn.disabled = false;
            submitBtn.textContent = 'تحديث القسم';
            showError('form-error', 'حدث خطأ في الاتصال');
        });
    });
}

// === DELETE DEPARTMENT ===
var deleteModal = document.getElementById('delete-modal');
var deleteBtns = document.querySelectorAll('.delete-dept-btn');
var cancelDeleteBtn = document.getElementById('cancel-delete');
var confirmDeleteBtn = document.getElementById('confirm-delete');
var reassignSection = document.getElementById('reassign-section');
var reassignSelect = document.getElementById('reassign-dept');

var currentDeleteId = null;

deleteBtns.forEach(function(btn) {
    btn.addEventListener('click', function() {
        currentDeleteId = this.dataset.id;
        var deptName = this.dataset.name;
        var empCount = parseInt(this.dataset.count);
        var msgEl = document.getElementById('delete-modal-message');

        if (empCount > 0) {
            msgEl.textContent = 'القسم "' + deptName + '" يحتوي على ' + empCount + ' موظف/موظفين. اختر قسم لإعادة تعيينهم إليه:';
            reassignSection.classList.remove('hidden');
            loadActiveDepartments(currentDeleteId);
        } else {
            msgEl.textContent = 'هل أنت متأكد من حذف القسم "' + deptName + '"؟';
            reassignSection.classList.add('hidden');
        }

        deleteModal.classList.remove('hidden');
    });
});

if (cancelDeleteBtn) {
    cancelDeleteBtn.addEventListener('click', function() {
        deleteModal.classList.add('hidden');
        currentDeleteId = null;
    });
}

if (confirmDeleteBtn) {
    confirmDeleteBtn.addEventListener('click', function() {
        if (!currentDeleteId) return;

        var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        var data = { _csrf_token: csrfToken };
        var empCount = parseInt(document.querySelector('.delete-dept-btn[data-id="' + currentDeleteId + '"]')?.dataset.count || '0');

        if (empCount > 0) {
            var reassignTo = reassignSelect.value;
            if (!reassignTo) {
                showError('reassign-error', 'يجب اختيار قسم لإعادة تعيين الموظفين');
                return;
            }
            data.reassign_to = parseInt(reassignTo);
        }

        confirmDeleteBtn.disabled = true;
        confirmDeleteBtn.textContent = 'جاري الحذف...';

        fetch(baseUrl + '/departments/' + currentDeleteId, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
            body: JSON.stringify(data)
        })
        .then(function(res) { return res.json().then(function(d) { return { status: res.status, data: d }; }); })
        .then(function(result) {
            confirmDeleteBtn.disabled = false;
            confirmDeleteBtn.textContent = 'حذف';

            if (result.data.success) {
                deleteModal.classList.add('hidden');
                window.location.reload();
            } else {
                showError('reassign-error', result.data.message || 'حدث خطأ');
            }
        })
        .catch(function() {
            confirmDeleteBtn.disabled = false;
            confirmDeleteBtn.textContent = 'حذف';
            showError('reassign-error', 'حدث خطأ في الاتصال');
        });
    });
}

function loadActiveDepartments(excludeId) {
    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    fetch(baseUrl + '/api/departments/active', {
        headers: { 'X-CSRF-Token': csrfToken }
    })
    .then(function(res) { return res.json(); })
    .then(function(result) {
        if (result.success) {
            reassignSelect.innerHTML = '<option value="">اختر القسم</option>';
            result.departments.forEach(function(dept) {
                if (dept.id != excludeId) {
                    var opt = document.createElement('option');
                    opt.value = dept.id;
                    opt.textContent = dept.name;
                    reassignSelect.appendChild(opt);
                }
            });
        }
    });
}

// === SEARCH ===
var searchInput = document.getElementById('department-search');
if (searchInput) {
    var searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        var query = this.value.trim();
        searchTimeout = setTimeout(function() {
            window.location.href = baseUrl + '/departments?search=' + encodeURIComponent(query);
        }, 300);
    });
}

function showError(id, msg) {
    var el = document.getElementById(id);
    if (el) { el.textContent = msg; el.classList.remove('hidden'); }
}