<?php

function renderButton(string $text, string $type = 'primary', string $attrs = ''): string
{
    $classes = match ($type) {
        'primary' => 'bg-[#F4C400] text-[#111111] font-bold py-2 px-4 rounded hover:bg-yellow-500 transition-colors',
        'secondary' => 'bg-gray-200 text-[#111111] font-bold py-2 px-4 rounded hover:bg-gray-300 transition-colors',
        'danger' => 'bg-red-600 text-white font-bold py-2 px-4 rounded hover:bg-red-700 transition-colors',
        default => 'bg-[#F4C400] text-[#111111] font-bold py-2 px-4 rounded hover:bg-yellow-500 transition-colors',
    };

    return "<button class=\"{$classes}\" {$attrs}>{$text}</button>";
}