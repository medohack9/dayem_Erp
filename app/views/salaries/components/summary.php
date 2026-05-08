<?php if ($adminView && $summary): ?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl p-5 border border-[#e5e5e5]">
        <div class="text-sm text-[#666666] mb-1">إجمالي المرتبات</div>
        <div class="text-2xl font-bold text-[#111111]"><?= number_format($summary['total_payroll'], 2) ?> ج.م</div>
        <div class="text-xs text-[#999999] mt-1"><?= $summary['total_count'] ?> سجل</div>
    </div>
    <div class="bg-white rounded-xl p-5 border border-green-200">
        <div class="text-sm text-green-700 mb-1">المدفوع</div>
        <div class="text-2xl font-bold text-green-700"><?= number_format($summary['paid_amount'], 2) ?> ج.م</div>
        <div class="text-xs text-green-600 mt-1"><?= $summary['paid_count'] ?> سجل مدفوع</div>
    </div>
    <div class="bg-white rounded-xl p-5 border border-yellow-200">
        <div class="text-sm text-yellow-700 mb-1">غير المدفوع</div>
        <div class="text-2xl font-bold text-yellow-700"><?= number_format($summary['unpaid_amount'], 2) ?> ج.م</div>
        <div class="text-xs text-yellow-600 mt-1"><?= $summary['unpaid_count'] ?> سجل غير مدفوع</div>
    </div>
</div>
<?php endif; ?>