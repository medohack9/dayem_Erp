<div class="p-8">
    <div class="max-w-4xl mx-auto">
        <a href="<?= url('/tickets') ?>" class="text-[#666666] hover:text-[#111111] transition-colors mb-6 inline-block">← العودة للتذاكر</a>

        <div class="bg-white rounded-xl border border-[#e5e5e5] mb-6">
            <div class="p-6 border-b border-[#e5e5e5]">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <span class="font-mono text-sm text-[#666666]"><?= htmlspecialchars($ticket['ticket_code']) ?></span>
                            <?php
                                $statusColors = [
                                    'مفتوح' => 'bg-blue-100 text-blue-800',
                                    'قيد المراجعة' => 'bg-yellow-100 text-yellow-800',
                                    'تم الرد' => 'bg-green-100 text-green-800',
                                    'مغلق' => 'bg-gray-100 text-gray-800',
                                ];
                                $statusClass = $statusColors[$ticket['status']] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span id="ticket-status-badge" class="px-3 py-1 rounded text-xs font-bold <?= $statusClass ?>"><?= htmlspecialchars($ticket['status']) ?></span>
                        </div>
                        <h1 class="text-2xl font-bold text-[#111111]"><?= htmlspecialchars($ticket['title']) ?></h1>
                        <div class="flex items-center gap-4 mt-2 text-sm text-[#666666]">
                            <span class="bg-gray-100 px-2 py-1 rounded text-xs"><?= htmlspecialchars($ticket['category']) ?></span>
                            <span>أنشئ بواسطة <?= htmlspecialchars($ticket['creator_name'] ?? '') ?></span>
                            <span><?= date('Y/m/d - H:i', strtotime($ticket['created_at'])) ?></span>
                        </div>
                    </div>
                    <?php if ($adminView): ?>
                    <div class="flex items-center gap-2">
                        <select id="status-select" class="px-3 py-2 border border-[#e5e5e5] rounded-lg text-sm focus:ring-2 focus:ring-[#F4C400] focus:border-transparent"
                            data-ticket-id="<?= $ticket['id'] ?>">
                            <option value="مفتوح" <?= $ticket['status'] === 'مفتوح' ? 'selected' : '' ?>>مفتوح</option>
                            <option value="قيد المراجعة" <?= $ticket['status'] === 'قيد المراجعة' ? 'selected' : '' ?>>قيد المراجعة</option>
                            <option value="تم الرد" <?= $ticket['status'] === 'تم الرد' ? 'selected' : '' ?>>تم الرد</option>
                            <option value="مغلق" <?= $ticket['status'] === 'مغلق' ? 'selected' : '' ?>>مغلق</option>
                        </select>
                        <?php if ($adminView): ?>
                        <button id="delete-ticket-btn" class="px-3 py-2 bg-red-500 text-white rounded-lg text-sm hover:bg-red-600 transition-colors" data-ticket-id="<?= $ticket['id'] ?>">حذف</button>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="p-6 bg-[#f9f9f9] border-b border-[#e5e5e5]">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-full bg-[#F4C400] flex items-center justify-center text-[#111111] font-bold text-sm flex-shrink-0">
                        <?= mb_substr($ticket['creator_name'] ?? 'م', 0, 1) ?>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-bold text-[#111111] text-sm"><?= htmlspecialchars($ticket['creator_name'] ?? '') ?></span>
                            <span class="text-xs bg-gray-200 px-2 py-0.5 rounded">موظف</span>
                            <span class="text-xs text-[#666666]"><?= date('Y/m/d - H:i', strtotime($ticket['created_at'])) ?></span>
                        </div>
                        <div class="text-[#111111] whitespace-pre-wrap"><?= htmlspecialchars($ticket['description']) ?></div>
                        <?php
                            $ticketAttachments = array_filter($attachments, fn($a) => $a['message_id'] === null);
                            if (!empty($ticketAttachments)):
                        ?>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <?php foreach ($ticketAttachments as $att): ?>
                            <a href="<?= url('/tickets/attachment/' . $att['id'] . '/' . $att['stored_name']) ?>" class="inline-flex items-center gap-1 px-3 py-1.5 bg-white border border-[#e5e5e5] rounded-lg text-sm text-[#111111] hover:bg-[#F4C400] transition-colors" target="_blank">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <?= htmlspecialchars($att['original_name']) ?>
                                <span class="text-xs text-[#666666]">(<?= round($att['file_size'] / 1024, 1) ?> كيلوبايت)</span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div id="messages-container" class="space-y-4 mb-6">
            <?php foreach ($messages as $msg): ?>
            <?php
                $isAdmin = $msg['sender_type'] === 'admin';
                $alignClass = $isAdmin ? 'justify-start' : 'justify-start';
                $bubbleClass = $isAdmin ? 'bg-[#F4C400] bg-opacity-20 border border-[#F4C400]' : 'bg-white border border-[#e5e5e5]';
                $nameBadge = $isAdmin ? '<span class="text-xs bg-[#F4C400] text-[#111111] px-2 py-0.5 rounded font-bold">مدير</span>' : '<span class="text-xs bg-gray-200 text-gray-700 px-2 py-0.5 rounded">موظف</span>';
            ?>
            <div class="flex <?= $alignClass ?>">
                <div class="flex items-start gap-3 max-w-[85%]">
                    <div class="w-8 h-8 rounded-full <?= $isAdmin ? 'bg-[#F4C400]' : 'bg-gray-300' ?> flex items-center justify-center text-[#111111] font-bold text-xs flex-shrink-0">
                        <?= mb_substr($msg['sender_name'] ?? 'م', 0, 1) ?>
                    </div>
                    <div class="<?= $bubbleClass ?> rounded-lg p-4">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-bold text-[#111111] text-sm"><?= htmlspecialchars($msg['sender_name'] ?? '') ?></span>
                            <?= $nameBadge ?>
                            <span class="text-xs text-[#666666]"><?= date('Y/m/d - H:i', strtotime($msg['created_at'])) ?></span>
                        </div>
                        <div class="text-[#111111] whitespace-pre-wrap"><?= htmlspecialchars($msg['content']) ?></div>
                        <?php
                            $msgAttachments = $messageAttachments[$msg['id']] ?? [];
                            if (!empty($msgAttachments)):
                        ?>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <?php foreach ($msgAttachments as $att): ?>
                            <a href="<?= url('/tickets/attachment/' . $att['id'] . '/' . $att['stored_name']) ?>" class="inline-flex items-center gap-1 px-3 py-1.5 bg-white border border-[#e5e5e5] rounded-lg text-sm text-[#111111] hover:bg-[#F4C400] transition-colors" target="_blank">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <?= htmlspecialchars($att['original_name']) ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($ticket['status'] !== 'مغلق' || $adminView): ?>
        <div id="message-form-container" class="bg-white rounded-xl border border-[#e5e5e5] p-6">
            <?php if ($ticket['status'] === 'مغلق' && !$adminView): ?>
            <div class="text-center text-[#666666] py-4">
                <p class="text-lg font-bold">هذه التذكرة مغلقة</p>
                <p class="text-sm">لا يمكن إرسال رسائل جديدة على التذكرة المغلقة</p>
            </div>
            <?php else: ?>
            <form id="message-form" enctype="multipart/form-data">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <div class="mb-4">
                    <label class="block text-[#111111] mb-2 font-bold">إرسال رد</label>
                    <textarea id="message-content" name="content" dir="rtl" rows="3"
                        class="w-full px-4 py-3 bg-white border border-[#e5e5e5] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#F4C400] focus:border-transparent text-[#111111]"
                        placeholder="اكتب رسالتك هنا..." maxlength="5000" <?= $ticket['status'] === 'مغلق' ? 'disabled' : '' ?>></textarea>
                    <div id="message-error" class="text-red-600 text-sm mt-1 hidden"></div>
                </div>
                <div class="mb-4">
                    <label class="block text-[#111111] mb-2 text-sm">المرفقات (اختياري)</label>
                    <div id="reply-attachment-list" class="space-y-2 mb-2"></div>
                    <label class="inline-flex items-center gap-2 px-4 py-2 border border-[#e5e5e5] rounded-lg cursor-pointer hover:bg-[#f9f9f9] transition-colors text-sm text-[#666666]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                        إضافة ملفات
                        <input type="file" id="reply-attachments" name="attachments[]" multiple class="hidden" accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.xls,.xlsx,.zip">
                    </label>
                    <p class="text-xs text-[#666666] mt-1">الحد الأقصى: 10 ملفات، 5 ميجابايت لكل ملف</p>
                </div>
                <button type="submit" id="send-message-btn" class="bg-[#F4C400] text-[#111111] px-6 py-2 rounded-lg font-bold hover:bg-[#e0b300] transition-colors" <?= $ticket['status'] === 'مغلق' ? 'disabled' : '' ?>>
                    إرسال
                </button>
            </form>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const messagesContainer = document.getElementById('messages-container');
    if (messagesContainer) {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
});
</script>