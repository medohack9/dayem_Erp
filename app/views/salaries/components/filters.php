<?php if ($adminView): ?>
<div class="bg-white rounded-xl p-6 border border-[#e5e5e5] mb-6">
    <form id="salary-filter-form" method="GET" action="<?= url('/salaries') ?>" class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <div>
            <label class="block text-[#111111] mb-1 text-sm">بحث</label>
            <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="بحث بالاسم أو الكود..."
                class="w-full px-3 py-2 border border-[#e5e5e5] rounded-lg text-sm focus:ring-2 focus:ring-[#F4C400] focus:border-transparent">
        </div>
        <div>
            <label class="block text-[#111111] mb-1 text-sm">الشهر</label>
            <select name="month" class="w-full px-3 py-2 border border-[#e5e5e5] rounded-lg text-sm focus:ring-2 focus:ring-[#F4C400] focus:border-transparent">
                <option value="">الكل</option>
                <?php for ($i = 1; $i <= 12; $i++): ?>
                <option value="<?= $i ?>" <?= ($selectedMonth ?? null) == $i ? 'selected' : '' ?>><?= $months[$i] ?? $i ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div>
            <label class="block text-[#111111] mb-1 text-sm">السنة</label>
            <input type="number" name="year" value="<?= $selectedYear ?? '' ?>" min="2020" max="2100" placeholder="السنة"
                class="w-full px-3 py-2 border border-[#e5e5e5] rounded-lg text-sm focus:ring-2 focus:ring-[#F4C400] focus:border-transparent">
        </div>
        <div>
            <label class="block text-[#111111] mb-1 text-sm">القسم</label>
            <select name="department_id" class="w-full px-3 py-2 border border-[#e5e5e5] rounded-lg text-sm focus:ring-2 focus:ring-[#F4C400] focus:border-transparent">
                <option value="">الكل</option>
                <?php foreach ($departments as $dept): ?>
                <option value="<?= $dept['id'] ?>" <?= ($selectedDepartment ?? null) == $dept['id'] ? 'selected' : '' ?>><?= htmlspecialchars($dept['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-[#111111] mb-1 text-sm">الحالة</label>
            <select name="status" class="w-full px-3 py-2 border border-[#e5e5e5] rounded-lg text-sm focus:ring-2 focus:ring-[#F4C400] focus:border-transparent">
                <option value="all" <?= ($selectedStatus ?? 'all') === 'all' ? 'selected' : '' ?>>الكل</option>
                <option value="unpaid" <?= ($selectedStatus ?? '') === 'unpaid' ? 'selected' : '' ?>>غير مدفوع</option>
                <option value="paid" <?= ($selectedStatus ?? '') === 'paid' ? 'selected' : '' ?>>مدفوع</option>
            </select>
        </div>
    </form>
    <div class="flex gap-3 mt-4">
        <button type="button" id="btn-generate" class="bg-[#F4C400] text-[#111111] px-4 py-2 rounded-lg font-bold hover:bg-[#e0b300] transition-colors text-sm">
            + توليد المرتبات
        </button>
        <button type="button" id="btn-recalculate" class="bg-white text-[#111111] px-4 py-2 rounded-lg font-bold border border-[#e5e5e5] hover:bg-[#f9f9f9] transition-colors text-sm">
            ↻ إعادة حساب غير المدفوع
        </button>
    </div>
</div>
<?php endif; ?>