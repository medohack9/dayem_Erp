document.addEventListener('DOMContentLoaded', function() {
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('[name="_csrf_token"]')?.value || '';
    const BASE = window.baseUrl || '';

    // ========== CREATE TICKET ==========
    const createForm = document.getElementById('create-ticket-form');
    if (createForm) {
        const descField = document.getElementById('description');
        const descCount = document.getElementById('description-count');
        if (descField && descCount) {
            descField.addEventListener('input', function() {
                descCount.textContent = this.value.length + ' / 5000';
            });
        }

        const fileInput = document.getElementById('attachments');
        const attachmentList = document.getElementById('attachment-list');
        let selectedFiles = [];

        if (fileInput) {
            fileInput.addEventListener('change', function() {
                const files = Array.from(this.files);
                for (let i = 0; i < files.length; i++) {
                    if (selectedFiles.length >= 10) {
                        alert('الحد الأقصى 10 ملفات');
                        break;
                    }
                    if (files[i].size > 5 * 1024 * 1024) {
                        alert('الملف "' + files[i].name + '" يتجاوز 5 ميجابايت');
                        continue;
                    }
                    selectedFiles.push(files[i]);
                    const item = document.createElement('div');
                    item.className = 'flex items-center justify-between bg-gray-50 p-2 rounded text-sm';
                    const idx = selectedFiles.length - 1;
                    item.innerHTML = '<span class="text-[#111111]">' + files[i].name + ' (' + (files[i].size / 1024).toFixed(1) + ' كيلوبايت)</span><button type="button" class="text-red-500 hover:text-red-700 remove-attach" data-idx="' + idx + '">حذف</button>';
                    attachmentList.appendChild(item);
                }
            });

            attachmentList.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-attach')) {
                    const idx = parseInt(e.target.dataset.idx);
                    selectedFiles.splice(idx, 1);
                    e.target.parentElement.remove();
                }
            });
        }

        createForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Hide previous errors
            document.querySelectorAll('[id$="-error"]').forEach(function(el) {
                el.classList.add('hidden');
                el.textContent = '';
            });

            const formData = new FormData();

            // Add text fields
            formData.append('_csrf_token', CSRF_TOKEN);
            formData.append('title', document.getElementById('title').value.trim());
            formData.append('description', document.getElementById('description').value.trim());
            formData.append('category', document.getElementById('category').value);

            // Add files from selectedFiles array
            for (let i = 0; i < selectedFiles.length; i++) {
                formData.append('attachments[]', selectedFiles[i]);
            }

            const submitBtn = document.getElementById('submit-btn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'جاري الإنشاء...';

            fetch(BASE + '/tickets', {
                method: 'POST',
                body: formData
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    window.location.href = BASE + '/tickets';
                } else {
                    if (data.errors) {
                        Object.keys(data.errors).forEach(function(key) {
                            var el = document.getElementById(key + '-error');
                            if (el) {
                                el.textContent = data.errors[key];
                                el.classList.remove('hidden');
                            }
                        });
                    } else {
                        alert(data.message || 'حدث خطأ');
                    }
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'إنشاء التذكرة';
                }
            })
            .catch(function() {
                alert('حدث خطأ في الاتصال');
                submitBtn.disabled = false;
                submitBtn.textContent = 'إنشاء التذكرة';
            });
        });
    }

    // ========== TICKET LIST - FILTERS ==========
    const filterForm = document.getElementById('ticket-filter-form');
    if (filterForm) {
        const selects = filterForm.querySelectorAll('select');
        selects.forEach(function(sel) {
            sel.addEventListener('change', function() {
                filterForm.submit();
            });
        });
    }

    // ========== TICKET CONVERSATION - SEND MESSAGE ==========
    const messageForm = document.getElementById('message-form');
    if (messageForm) {
        const replyFileInput = document.getElementById('reply-attachments');
        const replyAttachmentList = document.getElementById('reply-attachment-list');
        let replyFiles = [];

        if (replyFileInput) {
            replyFileInput.addEventListener('change', function() {
                const files = Array.from(this.files);
                for (let i = 0; i < files.length; i++) {
                    if (replyFiles.length >= 10) {
                        alert('الحد الأقصى 10 ملفات');
                        break;
                    }
                    if (files[i].size > 5 * 1024 * 1024) {
                        alert('الملف "' + files[i].name + '" يتجاوز 5 ميجابايت');
                        continue;
                    }
                    replyFiles.push(files[i]);
                    const item = document.createElement('div');
                    item.className = 'flex items-center justify-between bg-gray-50 p-2 rounded text-sm';
                    const idx = replyFiles.length - 1;
                    item.innerHTML = '<span class="text-[#111111]">' + files[i].name + '</span><button type="button" class="text-red-500 hover:text-red-700 remove-reply-file" data-index="' + idx + '">حذف</button>';
                    if (replyAttachmentList) {
                        replyAttachmentList.appendChild(item);
                    }
                }
                this.value = '';
            });

            if (replyAttachmentList) {
                replyAttachmentList.addEventListener('click', function(e) {
                    if (e.target.classList.contains('remove-reply-file')) {
                        const idx = parseInt(e.target.dataset.index);
                        replyFiles.splice(idx, 1);
                        e.target.parentElement.remove();
                    }
                });
            }
        }

        messageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const content = document.getElementById('message-content').value.trim();
            const errorEl = document.getElementById('message-error');

            if (!content) {
                if (errorEl) { errorEl.textContent = 'محتوى الرسالة مطلوب'; errorEl.classList.remove('hidden'); }
                return;
            }

            if (content.length > 5000) {
                if (errorEl) { errorEl.textContent = 'محتوى الرسالة يجب أن لا يتجاوز 5000 حرف'; errorEl.classList.remove('hidden'); }
                return;
            }

            if (errorEl) { errorEl.classList.add('hidden'); }

            const formData = new FormData();
            formData.append('_csrf_token', CSRF_TOKEN);
            formData.append('content', content);

            for (let i = 0; i < replyFiles.length; i++) {
                formData.append('attachments[]', replyFiles[i]);
            }

            const sendBtn = document.getElementById('send-message-btn');
            sendBtn.disabled = true;
            sendBtn.textContent = 'جاري الإرسال...';

            const ticketId = window.location.pathname.split('/').filter(Boolean).pop();

            fetch(BASE + '/tickets/' + ticketId + '/messages', {
                method: 'POST',
                body: formData
            })
            .then(function(res) {
                if (res.status === 403) {
                    return res.json().then(function(data) { throw new Error(data.message || 'غير مصرح'); });
                }
                return res.json();
            })
            .then(function(data) {
                if (data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'حدث خطأ');
                    sendBtn.disabled = false;
                    sendBtn.textContent = 'إرسال';
                }
            })
            .catch(function(err) {
                alert(err.message || 'حدث خطأ في الاتصال');
                sendBtn.disabled = false;
                sendBtn.textContent = 'إرسال';
            });
        });
    }

    // ========== STATUS CHANGE (ADMIN) ==========
    const statusSelect = document.getElementById('status-select');
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            const ticketId = this.dataset.ticketId;
            const newStatus = this.value;

            const statusMap = {
                '\u0645\u0641\u062a\u0648\u062d': 'bg-blue-100 text-blue-800',
                '\u0642\u064a\u062f \u0627\u0644\u0645\u0631\u0627\u062c\u0639\u0629': 'bg-yellow-100 text-yellow-800',
                '\u062a\u0645 \u0627\u0644\u0631\u062f': 'bg-green-100 text-green-800',
                '\u0645\u063a\u0644\u0642': 'bg-gray-100 text-gray-800'
            };

            fetch(BASE + '/tickets/' + ticketId + '/status', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ _csrf_token: CSRF_TOKEN, status: newStatus })
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    const badge = document.getElementById('ticket-status-badge');
                    if (badge) {
                        badge.textContent = newStatus;
                        badge.className = 'px-3 py-1 rounded text-xs font-bold ' + (statusMap[newStatus] || 'bg-gray-100 text-gray-800');
                    }
                    if (newStatus === '\u0645\u063a\u0644\u0642') {
                        window.location.reload();
                    }
                } else {
                    alert(data.message || 'حدث خطأ');
                    statusSelect.value = statusSelect.options[0].value;
                }
            })
            .catch(function() {
                alert('حدث خطأ في الاتصال');
                statusSelect.value = statusSelect.options[0].value;
            });
        });
    }

    // ========== DELETE TICKET (ADMIN) ==========
    const deleteBtn = document.getElementById('delete-ticket-btn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            if (!confirm('هل أنت متأكد من حذف هذه التذكرة؟')) return;

            const ticketId = this.dataset.ticketId;
            fetch(BASE + '/tickets/' + ticketId, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ _csrf_token: CSRF_TOKEN })
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    window.location.href = BASE + '/tickets';
                } else {
                    alert(data.message || 'حدث خطأ');
                }
            })
            .catch(function() { alert('حدث خطأ في الاتصال'); });
        });
    }
});