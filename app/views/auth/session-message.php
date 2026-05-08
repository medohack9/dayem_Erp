<?php if (isset($_SESSION['flash_message'])): ?>
    <?php
        $flashMessage = $_SESSION['flash_message'];
        $flashType = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        $bgClass = match ($flashType) {
            'success' => 'bg-green-100 text-green-800 border-green-400',
            'error' => 'bg-red-100 text-red-800 border-red-400',
            'warning' => 'bg-yellow-100 text-yellow-800 border-yellow-400',
            default => 'bg-blue-100 text-blue-800 border-blue-400',
        };
    ?>
    <div class="mb-4 p-3 rounded border <?= $bgClass ?>">
        <?= htmlspecialchars($flashMessage, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>