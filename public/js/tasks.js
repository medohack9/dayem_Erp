document.addEventListener('DOMContentLoaded', function () {
    var csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || window.csrfToken;

    // Create Task Form
    var createForm = document.getElementById('create-task-form');
    if (createForm) {
        createForm.addEventListener('submit', function (e) {
            e.preventDefault();
            clearErrors();

            var data = {
                _csrf_token: csrfToken,
                title: document.getElementById('title').value.trim(),
                description: document.getElementById('description').value.trim(),
                priority: document.getElementById('priority').value,
                due_date: document.getElementById('due_date').value || null,
                assigned_to: document.getElementById('assigned_to').value,
                department_id: document.getElementById('department_id').value || null,
            };

            fetch(window.baseUrl + '/tasks', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(function (res) { return res.json().then(function (body) { return { status: res.status, body: body }; }); })
            .then(function (result) {
                if (result.body.success) {
                    window.location.href = window.baseUrl + '/tasks';
                } else if (result.body.errors) {
                    showErrors(result.body.errors);
                } else {
                    showFormError(result.body.message || 'حدث خطأ غير متوقع');
                }
            })
            .catch(function () { showFormError('حدث خطأ في الاتصال'); });
        });
    }

    // Edit Task Form
    var editForm = document.getElementById('edit-task-form');
    if (editForm) {
        editForm.addEventListener('submit', function (e) {
            e.preventDefault();
            clearErrors();

            var taskId = document.querySelector('input[name="task_id"]').value;
            var data = {
                _csrf_token: csrfToken,
                title: document.getElementById('title').value.trim(),
                description: document.getElementById('description').value.trim(),
                priority: document.getElementById('priority').value,
                status: document.getElementById('status').value,
                due_date: document.getElementById('due_date').value || null,
                assigned_to: document.getElementById('assigned_to').value,
                department_id: document.getElementById('department_id').value || null,
            };

            fetch(window.baseUrl + '/tasks/' + taskId, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(function (res) { return res.json().then(function (body) { return { status: res.status, body: body }; }); })
            .then(function (result) {
                if (result.body.success) {
                    showFormSuccess(result.body.message || 'تم تحديث المهمة بنجاح');
                    setTimeout(function () { window.location.href = window.baseUrl + '/tasks'; }, 1500);
                } else if (result.body.errors) {
                    showErrors(result.body.errors);
                } else {
                    showFormError(result.body.message || 'حدث خطأ غير متوقع');
                }
            })
            .catch(function () { showFormError('حدث خطأ في الاتصال'); });
        });
    }

    // Status change inline dropdowns
    document.addEventListener('change', function (e) {
        if (!e.target.classList.contains('status-select')) return;
        var taskId = e.target.dataset.taskId;
        var newStatus = e.target.value;

        fetch(window.baseUrl + '/tasks/' + taskId + '/status', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ _csrf_token: csrfToken, status: newStatus })
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'حدث خطأ');
                window.location.reload();
            }
        })
        .catch(function () {
            alert('حدث خطأ في الاتصال');
            window.location.reload();
        });
    });

    // Delete Task
    var modal = document.getElementById('delete-modal');
    var cancelBtn = document.getElementById('cancel-delete');
    var confirmBtn = document.getElementById('confirm-delete');
    var deleteId = null;

    document.addEventListener('click', function (e) {
        if (e.target.closest('.delete-task-btn')) {
            var btn = e.target.closest('.delete-task-btn');
            deleteId = btn.dataset.id;
            var taskTitle = btn.dataset.title;
            document.getElementById('delete-modal-message').textContent = 'هل أنت متأكد من حذف المهمة "' + taskTitle + '"؟';
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

            fetch(window.baseUrl + '/tasks/' + deleteId, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ _csrf_token: csrfToken })
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    window.location.href = window.baseUrl + '/tasks';
                } else {
                    alert(data.message || 'حدث خطأ');
                    modal.classList.add('hidden');
                }
            })
            .catch(function () {
                alert('حدث خطأ في الاتصال');
                modal.classList.add('hidden');
            });
        });
    }

    function showErrors(errors) {
        Object.keys(errors).forEach(function (field) {
            var errorEl = document.getElementById(field + '-error');
            if (errorEl) {
                errorEl.textContent = errors[field];
                errorEl.classList.remove('hidden');
            }
            var input = document.getElementById(field);
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
        var formError = document.getElementById('form-error');
        if (formError) { formError.classList.add('hidden'); }
        var formSuccess = document.getElementById('form-success');
        if (formSuccess) { formSuccess.classList.add('hidden'); }
    }

    function showFormError(msg) {
        var el = document.getElementById('form-error');
        if (el) { el.textContent = msg; el.classList.remove('hidden'); }
    }

    function showFormSuccess(msg) {
        var el = document.getElementById('form-success');
        if (el) { el.textContent = msg; el.classList.remove('hidden'); }
    }
});