document.addEventListener('DOMContentLoaded', function() {
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('[name="_csrf_token"]')?.value || '';
    const BASE = window.baseUrl || '';

    // ========== UPLOAD FILE FORM ==========
    const uploadForm = document.getElementById('upload-file-form');
    if (uploadForm) {
        const fileInput = document.getElementById('file');
        const fileLabel = document.getElementById('file-label');
        const dropZone = document.getElementById('file-drop-zone');
        const progressDiv = document.getElementById('upload-progress');
        const progressBar = document.getElementById('progress-bar');
        const progressPercent = document.getElementById('progress-percent');

        if (fileInput) {
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const file = this.files[0];
                    const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                    fileLabel.textContent = file.name + ' (' + sizeMB + ' MB)';
                    
                    dropZone.classList.remove('border-[#e5e5e5]');
                    dropZone.classList.add('border-[#F4C400]', 'bg-yellow-50');
                }
            });
        }

        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();

            document.querySelectorAll('[id$="-error"]').forEach(function(el) {
                el.classList.add('hidden');
                el.textContent = '';
            });

            const file = fileInput.files[0];
            if (!file) {
                const errEl = document.getElementById('file-error');
                if (errEl) {
                    errEl.textContent = 'الملف مطلوب';
                    errEl.classList.remove('hidden');
                }
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                const errEl = document.getElementById('file-error');
                if (errEl) {
                    errEl.textContent = 'حجم الملف يتجاوز 5 ميجابايت';
                    errEl.classList.remove('hidden');
                }
                return;
            }

            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 
                'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/zip', 'application/x-rar-compressed'];
            
            if (!allowedTypes.includes(file.type)) {
                const errEl = document.getElementById('file-error');
                if (errEl) {
                    errEl.textContent = 'نوع الملف غير مدعوم';
                    errEl.classList.remove('hidden');
                }
                return;
            }

            const formData = new FormData();
            formData.append('_csrf_token', CSRF_TOKEN);
            formData.append('name', document.getElementById('name').value.trim());
            formData.append('priority', document.getElementById('priority').value);
            formData.append('notes', document.getElementById('notes').value.trim());
            formData.append('file', file);

            const submitBtn = document.getElementById('submit-btn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'جاري الرفع...';

            progressDiv.classList.remove('hidden');

            const xhr = new XMLHttpRequest();
            
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    progressBar.style.width = percent + '%';
                    progressPercent.textContent = percent + '%';
                }
            });

            xhr.addEventListener('load', function() {
                let data;
                try {
                    data = JSON.parse(xhr.responseText);
                } catch (ex) {
                    alert('حدث خطأ في الاستجابة');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'رفع الملف';
                    progressDiv.classList.add('hidden');
                    return;
                }

                if (data.success) {
                    window.location.href = BASE + '/files';
                } else {
                    if (data.errors) {
                        Object.keys(data.errors).forEach(function(key) {
                            const el = document.getElementById(key + '-error');
                            if (el) {
                                el.textContent = data.errors[key];
                                el.classList.remove('hidden');
                            }
                        });
                    } else {
                        alert(data.message || 'حدث خطأ');
                    }
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'رفع الملف';
                    progressDiv.classList.add('hidden');
                }
            });

            xhr.addEventListener('error', function() {
                alert('حدث خطأ في الاتصال');
                submitBtn.disabled = false;
                submitBtn.textContent = 'رفع الملف';
                progressDiv.classList.add('hidden');
            });

            xhr.open('POST', BASE + '/files');
            xhr.send(formData);
        });
    }

    // ========== EDIT FILE FORM ==========
    const editForm = document.getElementById('edit-file-form');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();

            document.querySelectorAll('[id$="-error"]').forEach(function(el) {
                el.classList.add('hidden');
                el.textContent = '';
            });

            const fileId = document.querySelector('[name="file_id"]').value;

            const data = {
                _csrf_token: CSRF_TOKEN,
                name: document.getElementById('name').value.trim(),
                priority: document.getElementById('priority').value,
                notes: document.getElementById('notes').value.trim()
            };

            const submitBtn = document.getElementById('submit-btn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'جاري الحفظ...';

            fetch(BASE + '/files/' + fileId, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    showToast('تم تحديث الملف بنجاح', 'success');
                    setTimeout(function() {
                        window.location.href = BASE + '/files';
                    }, 1000);
                } else {
                    if (data.errors) {
                        Object.keys(data.errors).forEach(function(key) {
                            const el = document.getElementById(key + '-error');
                            if (el) {
                                el.textContent = data.errors[key];
                                el.classList.remove('hidden');
                            }
                        });
                    } else {
                        alert(data.message || 'حدث خطأ');
                    }
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'حفظ التغييرات';
                }
            })
            .catch(function() {
                alert('حدث خطأ في الاتصال');
                submitBtn.disabled = false;
                submitBtn.textContent = 'حفظ التغييرات';
            });
        });
    }

    // ========== FILE LIST FILTERS ==========
    const searchInput = document.getElementById('search');
    const priorityFilter = document.getElementById('priority-filter');
    const userFilter = document.getElementById('user-filter');
    const dateFrom = document.getElementById('date-from');
    const dateTo = document.getElementById('date-to');
    const clearFilters = document.getElementById('clear-filters');

    function applyFilters() {
        const params = new URLSearchParams();
        params.set('page', '1');
        
        if (searchInput && searchInput.value.trim()) {
            params.set('search', searchInput.value.trim());
        }
        if (priorityFilter && priorityFilter.value !== 'all') {
            params.set('priority', priorityFilter.value);
        }
        if (userFilter && userFilter.value) {
            params.set('user_id', userFilter.value);
        }
        if (dateFrom && dateFrom.value) {
            params.set('date_from', dateFrom.value);
        }
        if (dateTo && dateTo.value) {
            params.set('date_to', dateTo.value);
        }

        window.location.href = BASE + '/files?' + params.toString();
    }

    if (searchInput) {
        let debounceTimer;
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(applyFilters, 500);
        });
    }

    if (priorityFilter) {
        priorityFilter.addEventListener('change', applyFilters);
    }

    if (userFilter) {
        userFilter.addEventListener('change', applyFilters);
    }

    if (dateFrom) {
        dateFrom.addEventListener('change', applyFilters);
    }

    if (dateTo) {
        dateTo.addEventListener('change', applyFilters);
    }

    if (clearFilters) {
        clearFilters.addEventListener('click', function() {
            window.location.href = BASE + '/files';
        });
    }

    // ========== DELETE FILE ==========
    const deleteModal = document.getElementById('delete-modal');
    const deleteFileName = document.getElementById('delete-file-name');
    const deleteFileId = document.getElementById('delete-file-id');
    const cancelDelete = document.getElementById('cancel-delete');
    const confirmDelete = document.getElementById('confirm-delete');

    document.querySelectorAll('.delete-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const fileId = this.dataset.fileId;
            const fileName = this.dataset.fileName;
            
            deleteFileId.value = fileId;
            deleteFileName.textContent = fileName;
            deleteModal.classList.remove('hidden');
            deleteModal.classList.add('flex');
        });
    });

    if (cancelDelete) {
        cancelDelete.addEventListener('click', function() {
            deleteModal.classList.add('hidden');
            deleteModal.classList.remove('flex');
        });
    }

    if (confirmDelete) {
        confirmDelete.addEventListener('click', function() {
            const fileId = deleteFileId.value;
            
            confirmDelete.disabled = true;
            confirmDelete.textContent = 'جاري الحذف...';

            fetch(BASE + '/files/' + fileId, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ _csrf_token: CSRF_TOKEN })
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    showToast(data.message + (data.storage_freed ? ' (تم تحرير ' + data.storage_freed + ')' : ''), 'success');
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } else {
                    alert(data.message || 'حدث خطأ');
                    confirmDelete.disabled = false;
                    confirmDelete.textContent = 'حذف';
                }
            })
            .catch(function() {
                alert('حدث خطأ في الاتصال');
                confirmDelete.disabled = false;
                confirmDelete.textContent = 'حذف';
            });
        });
    }

    // ========== TOAST HELPER ==========
    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = 'fixed bottom-4 left-4 px-6 py-3 rounded-lg shadow-lg z-50 ' + 
            (type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white');
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(function() {
            toast.remove();
        }, 3000);
    }
});