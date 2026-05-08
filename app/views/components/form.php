<?php

function renderInput(string $name, string $label, string $type = 'text', string $value = '', bool $required = false): string
{
    $requiredAttr = $required ? 'required' : '';
    $escapedValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    $escapedLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
    $escapedName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');

    return <<<HTML
<div class="mb-4">
    <label class="block text-sm font-bold text-[#111111] mb-1" for="{$escapedName}">{$escapedLabel}</label>
    <input type="{$type}" id="{$escapedName}" name="{$escapedName}" value="{$escapedValue}" {$requiredAttr} class="w-full border rounded px-3 py-2 focus:outline-none focus:border-[#F4C400]">
</div>
HTML;
}

function renderSelect(string $name, string $label, array $options, string $selected = ''): string
{
    $escapedName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $escapedLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');

    $optionsHtml = '';
    foreach ($options as $value => $label) {
        $escapedValue = htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
        $escapedLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
        $selectedAttr = ((string)$value === $selected) ? 'selected' : '';
        $optionsHtml .= "<option value=\"{$escapedValue}\" {$selectedAttr}>{$escapedLabel}</option>";
    }

    return <<<HTML
<div class="mb-4">
    <label class="block text-sm font-bold text-[#111111] mb-1" for="{$escapedName}">{$escapedLabel}</label>
    <select id="{$escapedName}" name="{$escapedName}" class="w-full border rounded px-3 py-2 focus:outline-none focus:border-[#F4C400]">
        {$optionsHtml}
    </select>
</div>
HTML;
}

function renderTextarea(string $name, string $label, string $value = '', int $rows = 4): string
{
    $escapedName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $escapedLabel = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
    $escapedValue = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

    return <<<HTML
<div class="mb-4">
    <label class="block text-sm font-bold text-[#111111] mb-1" for="{$escapedName}">{$escapedLabel}</label>
    <textarea id="{$escapedName}" name="{$escapedName}" rows="{$rows}" class="w-full border rounded px-3 py-2 focus:outline-none focus:border-[#F4C400]">{$escapedValue}</textarea>
</div>
HTML;
}

function renderCsrfField(): string
{
    $token = $_SESSION['csrf_token'] ?? '';
    $escapedToken = htmlspecialchars($token, ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="_csrf_token" value="' . $escapedToken . '">';
}