<div class="p-8">
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-[#111111] mb-2">سجلات النظام</h1>
                <p class="text-[#666666]">تتبع جميع أنشطة النظام</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="<?= url('/logs/export?' . http_build_query(array_filter($filters ?? []))) ?>"
                    class="bg-white border border-[#e5e5e5] text-[#111111] px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                    تصدير CSV
                </a>
                <a href="<?= url('/logs/export/today') ?>"
                    class="bg-[#F4C400] text-[#111111] px-4 py-2 rounded-lg font-bold hover:bg-[#e0b300] transition-colors">
                    تصدير اليوم
                </a>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-[#e5e5e5] mb-6">
            <div class="p-4 border-b border-[#e5e5e5]">
                <div class="flex flex-wrap items-center gap-4">
                    <div>
                        <select id="filter-user" name="user_id"
                            class="px-4 py-2 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]">
                            <option value="">كل المستخدمين</option>
                            <?php foreach ($users as $user): ?>
                            <option value="<?= $user['id'] ?>" <?= ($filters['user_id'] ?? null) === (int)$user['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($user['name']) ?>
                                <?php if ($user['status'] !== 'active'): ?>
                                (<?= $user['status'] === 'suspended' ? 'موقوف' : 'منتهي' ?>)
                                <?php endif; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <select id="filter-action" name="action"
                            class="px-4 py-2 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]">
                            <option value="">كل الإجراءات</option>
                            <?php foreach ($actions as $act): ?>
                            <option value="<?= htmlspecialchars($act) ?>" <?= ($filters['action'] ?? null) === $act ? 'selected' : '' ?>>
                                <?= htmlspecialchars($act) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <select id="filter-entity-type" name="entity_type"
                            class="px-4 py-2 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]">
                            <option value="">كل الأنواع</option>
                            <?php foreach ($entityTypes as $et): ?>
                            <option value="<?= htmlspecialchars($et) ?>" <?= ($filters['entity_type'] ?? null) === $et ? 'selected' : '' ?>>
                                <?= htmlspecialchars($et) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <input type="number" id="filter-entity-id" name="entity_id"
                            value="<?= htmlspecialchars($filters['entity_id'] ?? '') ?>"
                            placeholder="رقم السجل"
                            class="w-32 px-4 py-2 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]">
                    </div>
                    <div>
                        <input type="date" id="filter-date-from" name="date_from"
                            value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>"
                            placeholder="من تاريخ"
                            class="px-4 py-2 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]">
                    </div>
                    <div>
                        <input type="date" id="filter-date-to" name="date_to"
                            value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>"
                            placeholder="إلى تاريخ"
                            class="px-4 py-2 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]">
                    </div>
                    <button type="button" id="clear-filters"
                        class="px-4 py-2 text-[#666666] hover:text-[#111111] transition-colors">
                        مسح الفلاتر
                    </button>
                </div>
            </div>

            <?php if ($total > 0): ?>
            <div class="p-4 bg-gray-50 border-b border-[#e5e5e5]">
                <span class="text-sm text-[#666666]">
                    إجمالي: <span class="font-bold text-[#111111]"><?= number_format($total) ?></span> سجل
                </span>
            </div>
            <?php endif; ?>

            <?php if (empty($logs)): ?>
            <div class="p-8 text-center">
                <svg class="mx-auto h-12 w-12 text-[#666666] mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-[#666666]">لا توجد سجلات مطابقة</p>
            </div>
            <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-right px-4 py-3 text-sm font-semibold text-[#111111]">المستخدم</th>
                            <th class="text-right px-4 py-3 text-sm font-semibold text-[#111111]">الإجراء</th>
                            <th class="text-right px-4 py-3 text-sm font-semibold text-[#111111]">النوع</th>
                            <th class="text-right px-4 py-3 text-sm font-semibold text-[#111111]">الرقم</th>
                            <th class="text-right px-4 py-3 text-sm font-semibold text-[#111111]">الوقت</th>
                            <th class="text-right px-4 py-3 text-sm font-semibold text-[#111111]">IP</th>
                            <th class="text-right px-4 py-3 text-sm font-semibold text-[#111111]">تفاصيل</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#e5e5e5]">
                        <?php foreach ($logs as $log): ?>
                        <tr class="hover:bg-gray-50 cursor-pointer log-row" data-log-id="<?= $log['id'] ?>">
                            <td class="px-4 py-3">
                                <span class="text-[#111111]">
                                    <?= htmlspecialchars($log['user_name'] ?? 'مستخدم محذوف') ?>
                                </span>
                                <?php if (!$log['user_name']): ?>
                                <span class="text-xs text-red-500">(محذوف)</span>
                                <?php elseif ($log['user_status'] && $log['user_status'] !== 'active'): ?>
                                <span class="text-xs text-yellow-600">(<?= $log['user_status'] === 'suspended' ? 'موقوف' : 'منتهي' ?>)</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php
                                $actionColors = [
                                    'create' => 'bg-green-100 text-green-800',
                                    'update' => 'bg-blue-100 text-blue-800',
                                    'delete' => 'bg-red-100 text-red-800',
                                    'upload' => 'bg-purple-100 text-purple-800',
                                    'download' => 'bg-indigo-100 text-indigo-800',
                                    'reply' => 'bg-cyan-100 text-cyan-800',
                                    'status_change' => 'bg-yellow-100 text-yellow-800',
                                    'edit' => 'bg-blue-100 text-blue-800',
                                ];
                                $actionColor = $actionColors[$log['action']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="px-2 py-1 text-xs rounded-full <?= $actionColor ?>"><?= htmlspecialchars($log['action']) ?></span>
                            </td>
                            <td class="px-4 py-3 text-[#111111]"><?= htmlspecialchars($log['entity_type']) ?></td>
                            <td class="px-4 py-3 text-[#111111]"><?= $log['entity_id'] ?></td>
                            <td class="px-4 py-3 text-[#666666] text-sm"><?= date('Y/m/d H:i', strtotime($log['created_at'])) ?></td>
                            <td class="px-4 py-3 text-[#666666] text-sm font-mono"><?= htmlspecialchars($log['ip_address'] ?? '-') ?></td>
                            <td class="px-4 py-3">
                                <button type="button" class="view-detail-btn text-[#F4C400] hover:text-[#e0b300] transition-colors" data-log-id="<?= $log['id'] ?>">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            <?php if ($totalPages > 1): ?>
            <div class="p-4 border-t border-[#e5e5e5] flex items-center justify-between">
                <div class="text-sm text-[#666666]">
                    عرض <?= (($page - 1) * $perPage) + 1 ?> - <?= min($page * $perPage, $total) ?> من <?= number_format($total) ?> سجل
                </div>
                <div class="flex items-center gap-2">
                    <?php if ($page > 1): ?>
                    <a href="<?= url('/logs?' . http_build_query(array_merge($_GET, ['page' => $page - 1]))) ?>"
                        class="px-3 py-1 border border-[#e5e5e5] rounded-lg hover:bg-gray-50 transition-colors">السابق</a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <?php if ($i === $page): ?>
                    <span class="px-3 py-1 bg-[#F4C400] text-[#111111] rounded-lg font-bold"><?= $i ?></span>
                    <?php else: ?>
                    <a href="<?= url('/logs?' . http_build_query(array_merge($_GET, ['page' => $i]))) ?>"
                        class="px-3 py-1 border border-[#e5e5e5] rounded-lg hover:bg-gray-50 transition-colors"><?= $i ?></a>
                    <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                    <a href="<?= url('/logs?' . http_build_query(array_merge($_GET, ['page' => $page + 1]))) ?>"
                        class="px-3 py-1 border border-[#e5e5e5] rounded-lg hover:bg-gray-50 transition-colors">التالي</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="detail-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-xl p-6 max-w-2xl mx-4 max-h-[80vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-[#111111]">تفاصيل السجل</h3>
            <button type="button" id="close-modal" class="text-[#666666] hover:text-[#111111]">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div id="modal-content">
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-sm text-[#666666]">المستخدم:</span>
                        <p id="modal-user" class="font-medium text-[#111111]"></p>
                    </div>
                    <div>
                        <span class="text-sm text-[#666666]">الإجراء:</span>
                        <p id="modal-action" class="font-medium text-[#111111]"></p>
                    </div>
                    <div>
                        <span class="text-sm text-[#666666]">النوع:</span>
                        <p id="modal-entity-type" class="font-medium text-[#111111]"></p>
                    </div>
                    <div>
                        <span class="text-sm text-[#666666]">الرقم:</span>
                        <p id="modal-entity-id" class="font-medium text-[#111111]"></p>
                    </div>
                    <div>
                        <span class="text-sm text-[#666666]">IP:</span>
                        <p id="modal-ip" class="font-medium text-[#111111] font-mono"></p>
                    </div>
                    <div>
                        <span class="text-sm text-[#666666]">الوقت:</span>
                        <p id="modal-time" class="font-medium text-[#111111]"></p>
                    </div>
                </div>
                <div class="border-t border-[#e5e5e5] pt-4">
                    <span class="text-sm text-[#666666] block mb-2">التفاصيل:</span>
                    <div id="modal-details" class="bg-gray-50 rounded-lg p-4">
                        <p class="text-[#666666]">لا توجد تفاصيل إضافية</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>