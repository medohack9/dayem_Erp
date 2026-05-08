<?php if ($isAdmin): ?>
<div class="p-6">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-[#111111]">لوحة التحكم</h1>
        <p class="text-gray-500 mt-1">نظرة عامة على حالة النظام</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <?php 
        $icon = 'users';
        $label = 'الموظفين النشطين';
        $value = $kpiStats['employees'];
        $link = '/employees';
        $showTooltip = false;
        include ROOT_PATH . '/app/views/components/kpi-card.php';
        ?>
        
        <?php 
        $icon = 'tasks';
        $label = 'إجمالي المهام';
        $value = $kpiStats['tasks'];
        $link = '/tasks';
        $showTooltip = true;
        $tooltipData = $taskBreakdown;
        include ROOT_PATH . '/app/views/components/kpi-card.php';
        ?>
        
        <?php 
        $icon = 'ticket';
        $label = 'التذاكر المفتوحة';
        $value = $kpiStats['tickets'];
        $link = '/tickets';
        $showTooltip = false;
        include ROOT_PATH . '/app/views/components/kpi-card.php';
        ?>
        
        <?php 
        $icon = 'money';
        $label = 'رواتب هذا الشهر';
        $value = number_format($kpiStats['salaries'], 0, '.', ',') . ' جنيه';
        $link = '/salaries';
        $showTooltip = false;
        include ROOT_PATH . '/app/views/components/kpi-card.php';
        ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-bold text-[#111111] mb-4">إحصائيات المهام</h2>
            <div class="h-64">
                <canvas id="tasksChart"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-bold text-[#111111] mb-4">إحصائيات التذاكر</h2>
            <div class="h-64">
                <canvas id="ticketsChart"></canvas>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="p-6">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-[#111111]">لوحة التحكم الشخصية</h1>
        <p class="text-gray-500 mt-1">نظرة عامة على نشاطك في النظام</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <?php 
        $icon = 'tasks';
        $label = 'مهامي';
        $value = $employeeStats['tasks'];
        $link = '/tasks';
        $showTooltip = false;
        include ROOT_PATH . '/app/views/components/kpi-card.php';
        ?>
        
        <?php 
        $icon = 'ticket';
        $label = 'تذاكري';
        $value = $employeeStats['tickets'];
        $link = '/tickets';
        $showTooltip = false;
        include ROOT_PATH . '/app/views/components/kpi-card.php';
        ?>
        
        <?php 
        $icon = 'file';
        $label = 'ملفاتي';
        $value = $employeeStats['files'];
        $link = '/files';
        $showTooltip = false;
        include ROOT_PATH . '/app/views/components/kpi-card.php';
        ?>
        
        <?php 
        $icon = 'storage';
        $label = 'المساحة المستخدمة';
        $value = $employeeStats['storageUsedFormatted'];
        $link = '/files';
        $showTooltip = false;
        include ROOT_PATH . '/app/views/components/kpi-card.php';
        ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-bold text-[#111111] mb-4">إحصائيات مهامي</h2>
            <div class="h-64">
                <canvas id="tasksChart"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-bold text-[#111111] mb-4">إحصائيات تذاكري</h2>
            <div class="h-64">
                <canvas id="ticketsChart"></canvas>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>