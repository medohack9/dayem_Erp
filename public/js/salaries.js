document.addEventListener('DOMContentLoaded', function() {
    var CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]') !== null ? document.querySelector('meta[name="csrf-token"]').content : '';
    var BASE = window.baseUrl || '';

    // ========== GENERATE SALARIES ==========
    var btnGenerate = document.getElementById('btn-generate');
    var btnRecalculate = document.getElementById('btn-recalculate');
    var generateModal = document.getElementById('generate-modal');
    var modalTitle = document.getElementById('modal-title');
    var modalSubmitBtn = document.getElementById('modal-submit-btn');
    var modalCancelBtn = document.getElementById('modal-cancel-btn');
    var modalError = document.getElementById('modal-error');

    var currentAction = 'generate';

    if (btnGenerate) {
        btnGenerate.addEventListener('click', function() {
            currentAction = 'generate';
            if (modalTitle) modalTitle.textContent = 'توليد المرتبات';
            if (modalSubmitBtn) modalSubmitBtn.textContent = 'توليد';
            if (generateModal) generateModal.classList.remove('hidden');
            if (modalError) { modalError.classList.add('hidden'); modalError.textContent = ''; }
        });
    }

    if (btnRecalculate) {
        btnRecalculate.addEventListener('click', function() {
            currentAction = 'recalculate';
            if (modalTitle) modalTitle.textContent = 'إعادة حساب غير المدفوع';
            if (modalSubmitBtn) modalSubmitBtn.textContent = 'إعادة حساب';
            if (generateModal) generateModal.classList.remove('hidden');
            if (modalError) { modalError.classList.add('hidden'); modalError.textContent = ''; }
        });
    }

    if (modalCancelBtn) {
        modalCancelBtn.addEventListener('click', function() {
            if (generateModal) generateModal.classList.add('hidden');
        });
    }

    if (generateModal) {
        generateModal.addEventListener('click', function(e) {
            if (e.target === generateModal) {
                generateModal.classList.add('hidden');
            }
        });
    }

    if (modalSubmitBtn) {
        modalSubmitBtn.addEventListener('click', function() {
            var month = document.getElementById('generate-month').value;
            var year = document.getElementById('generate-year').value;

            if (!month || !year) {
                if (modalError) { modalError.textContent = 'يرجى اختيار الشهر والسنة'; modalError.classList.remove('hidden'); }
                return;
            }

            var url = currentAction === 'generate' ? BASE + '/salaries/generate' : BASE + '/salaries/recalculate';

            modalSubmitBtn.disabled = true;
            modalSubmitBtn.textContent = 'جاري التنفيذ...';
            if (modalError) { modalError.classList.add('hidden'); }

            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ _csrf_token: CSRF_TOKEN, month: parseInt(month), year: parseInt(year) })
            })
            .then(function(res) {
                var contentType = res.headers.get('content-type') || '';
                if (res.status === 403) {
                    throw new Error('غير مصرح بهذا الإجراء');
                }
                return res.json();
            })
            .then(function(data) {
                if (data.success) {
                    window.location.reload();
                } else {
                    if (modalError) { modalError.textContent = data.message || 'حدث خطأ'; modalError.classList.remove('hidden'); }
                    modalSubmitBtn.disabled = false;
                    modalSubmitBtn.textContent = currentAction === 'generate' ? 'توليد' : 'إعادة حساب';
                }
            })
            .catch(function(err) {
                if (modalError) { modalError.textContent = err.message || 'حدث خطأ في الاتصال'; modalError.classList.remove('hidden'); }
                modalSubmitBtn.disabled = false;
                modalSubmitBtn.textContent = currentAction === 'generate' ? 'توليد' : 'إعادة حساب';
            });
        });
    }

    // ========== PAY SALARY ==========
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('pay-btn')) {
            var salaryId = e.target.dataset.id;
            if (!salaryId) return;
            if (!confirm('هل أنت متأكد من تأكيد دفع هذا المرتب؟')) return;

            e.target.disabled = true;
            e.target.textContent = 'جاري التأكيد...';

            fetch(BASE + '/salaries/pay/' + salaryId, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ _csrf_token: CSRF_TOKEN })
            })
            .then(function(res) {
                return res.json();
            })
            .then(function(data) {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'حدث خطأ');
                    e.target.disabled = false;
                    e.target.textContent = 'تأكيد الدفع';
                }
            })
            .catch(function() {
                alert('حدث خطأ في الاتصال');
                e.target.disabled = false;
                e.target.textContent = 'تأكيد الدفع';
            });
        }
    });

    // ========== AUTO-SUBMIT FILTERS ==========
    var filterForm = document.getElementById('salary-filter-form');
    if (filterForm) {
        var selects = filterForm.querySelectorAll('select');
        selects.forEach(function(sel) {
            sel.addEventListener('change', function() {
                filterForm.submit();
            });
        });
    }

    // ========== CONFIG FORM ==========
    var configForm = document.getElementById('config-form');
    if (configForm) {
        configForm.addEventListener('submit', function(e) {
            e.preventDefault();

            var errorEl = document.getElementById('config-error');
            var successEl = document.getElementById('config-success');
            var submitBtn = document.getElementById('config-submit-btn');
            var userId = configForm.querySelector('[name="user_id"]').value;

            if (errorEl) { errorEl.classList.add('hidden'); }
            if (successEl) { successEl.classList.add('hidden'); }

            var data = {
                _csrf_token: CSRF_TOKEN,
                basic_salary: parseFloat(document.getElementById('basic_salary').value) || 0,
                allowances: parseFloat(document.getElementById('allowances').value) || 0,
                deductions: parseFloat(document.getElementById('deductions').value) || 0,
                notes: document.getElementById('notes').value.trim()
            };

            submitBtn.disabled = true;
            submitBtn.textContent = 'جاري الحفظ...';

            fetch(BASE + '/salaries/config/' + userId, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(function(res) {
                return res.json();
            })
            .then(function(data) {
                if (data.success) {
                    if (successEl) { successEl.textContent = data.message || 'تم الحفظ بنجاح'; successEl.classList.remove('hidden'); }
                } else {
                    if (errorEl) { errorEl.textContent = data.message || 'حدث خطأ'; errorEl.classList.remove('hidden'); }
                }
                submitBtn.disabled = false;
                submitBtn.textContent = 'حفظ الإعدادات';
            })
            .catch(function() {
                if (errorEl) { errorEl.textContent = 'حدث خطأ في الاتصال'; errorEl.classList.remove('hidden'); }
                submitBtn.disabled = false;
                submitBtn.textContent = 'حفظ الإعدادات';
            });
        });
    }
});