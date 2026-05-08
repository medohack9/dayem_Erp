document.addEventListener('DOMContentLoaded', function() {
    const BASE = window.baseUrl || '';

    // ========== FILTER HANDLERS ==========
    const filterUser = document.getElementById('filter-user');
    const filterAction = document.getElementById('filter-action');
    const filterEntityType = document.getElementById('filter-entity-type');
    const filterEntityId = document.getElementById('filter-entity-id');
    const filterDateFrom = document.getElementById('filter-date-from');
    const filterDateTo = document.getElementById('filter-date-to');
    const clearFilters = document.getElementById('clear-filters');

    function applyFilters() {
        const params = new URLSearchParams();
        params.set('page', '1');

        if (filterUser && filterUser.value) {
            params.set('user_id', filterUser.value);
        }
        if (filterAction && filterAction.value) {
            params.set('action', filterAction.value);
        }
        if (filterEntityType && filterEntityType.value) {
            params.set('entity_type', filterEntityType.value);
        }
        if (filterEntityId && filterEntityId.value) {
            params.set('entity_id', filterEntityId.value);
        }
        if (filterDateFrom && filterDateFrom.value) {
            params.set('date_from', filterDateFrom.value);
        }
        if (filterDateTo && filterDateTo.value) {
            params.set('date_to', filterDateTo.value);
        }

        window.location.href = BASE + '/logs?' + params.toString();
    }

    if (filterUser) {
        filterUser.addEventListener('change', applyFilters);
    }

    if (filterAction) {
        filterAction.addEventListener('change', applyFilters);
    }

    if (filterEntityType) {
        filterEntityType.addEventListener('change', applyFilters);
    }

    if (filterEntityId) {
        filterEntityId.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                applyFilters();
            }
        });
        filterEntityId.addEventListener('change', applyFilters);
    }

    if (filterDateFrom) {
        filterDateFrom.addEventListener('change', applyFilters);
    }

    if (filterDateTo) {
        filterDateTo.addEventListener('change', applyFilters);
    }

    if (clearFilters) {
        clearFilters.addEventListener('click', function() {
            window.location.href = BASE + '/logs';
        });
    }

    // ========== DETAIL MODAL ==========
    const detailModal = document.getElementById('detail-modal');
    const closeModal = document.getElementById('close-modal');
    const modalUser = document.getElementById('modal-user');
    const modalAction = document.getElementById('modal-action');
    const modalEntityType = document.getElementById('modal-entity-type');
    const modalEntityId = document.getElementById('modal-entity-id');
    const modalIp = document.getElementById('modal-ip');
    const modalTime = document.getElementById('modal-time');
    const modalDetails = document.getElementById('modal-details');

    function showDetailModal(logId) {
        fetch(BASE + '/logs/' + logId)
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    const log = data.log;

                    let userName = log.user_name || 'مستخدم محذوف';
                    if (!log.user_name) {
                        userName += ' <span class="text-red-500 text-xs">(محذوف)</span>';
                    } else if (log.user_status && log.user_status !== 'active') {
                        userName += ' <span class="text-yellow-600 text-xs">(' + (log.user_status === 'suspended' ? 'موقوف' : 'منتهي') + ')</span>';
                    }
                    modalUser.innerHTML = userName;

                    modalAction.textContent = log.action;
                    modalEntityType.textContent = log.entity_type;
                    modalEntityId.textContent = log.entity_id;
                    modalIp.textContent = log.ip_address || '-';
                    modalTime.textContent = new Date(log.created_at).toLocaleString('ar-EG');

                    if (log.details_parsed) {
                        let detailsHtml = '<table class="w-full text-sm">';
                        detailsHtml += '<tbody>';
                        for (const [key, value] of Object.entries(log.details_parsed)) {
                            let displayValue = value;
                            if (typeof value === 'object') {
                                displayValue = '<pre class="text-xs bg-white p-2 rounded overflow-x-auto">' + JSON.stringify(value, null, 2) + '</pre>';
                            }
                            detailsHtml += '<tr class="border-b border-gray-200">';
                            detailsHtml += '<td class="py-2 px-3 text-[#666666] w-1/3">' + key + '</td>';
                            detailsHtml += '<td class="py-2 px-3 text-[#111111]">' + displayValue + '</td>';
                            detailsHtml += '</tr>';
                        }
                        detailsHtml += '</tbody></table>';
                        modalDetails.innerHTML = detailsHtml;
                    } else {
                        modalDetails.innerHTML = '<p class="text-[#666666]">لا توجد تفاصيل إضافية</p>';
                    }

                    detailModal.classList.remove('hidden');
                    detailModal.classList.add('flex');
                } else {
                    alert(data.message || 'حدث خطأ');
                }
            })
            .catch(function() {
                alert('حدث خطأ في الاتصال');
            });
    }

    function hideDetailModal() {
        detailModal.classList.add('hidden');
        detailModal.classList.remove('flex');
    }

    document.querySelectorAll('.view-detail-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const logId = this.dataset.logId;
            showDetailModal(logId);
        });
    });

    document.querySelectorAll('.log-row').forEach(function(row) {
        row.addEventListener('click', function() {
            const logId = this.dataset.logId;
            showDetailModal(logId);
        });
    });

    if (closeModal) {
        closeModal.addEventListener('click', hideDetailModal);
    }

    if (detailModal) {
        detailModal.addEventListener('click', function(e) {
            if (e.target === detailModal) {
                hideDetailModal();
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !detailModal.classList.contains('hidden')) {
            hideDetailModal();
        }
    });
});