<?php

function renderTable(array $headers, array $rows, array $options = []): string
{
    $emptyMessage = $options['emptyMessage'] ?? 'لا توجد بيانات';

    $html = '<div class="overflow-x-auto">';
    $html .= '<table class="w-full bg-white rounded-lg shadow">';
    $html .= '<thead class="bg-[#F4C400]"><tr>';

    foreach ($headers as $header) {
        $html .= '<th class="px-4 py-3 text-[#111111] font-bold text-right">' . htmlspecialchars($header, ENT_QUOTES, 'UTF-8') . '</th>';
    }

    $html .= '</tr></thead>';
    $html .= '<tbody>';

    if (empty($rows)) {
        $colspan = count($headers);
        $html .= '<tr><td colspan="' . $colspan . '" class="px-4 py-8 text-center text-gray-500">' . htmlspecialchars($emptyMessage, ENT_QUOTES, 'UTF-8') . '</td></tr>';
    } else {
        $rowIndex = 0;
        foreach ($rows as $row) {
            $bgClass = ($rowIndex % 2 === 0) ? 'bg-white' : 'bg-gray-50';
            $html .= '<tr class="' . $bgClass . '">';
            foreach ($row as $cell) {
                $html .= '<td class="px-4 py-3 border-t text-right">' . $cell . '</td>';
            }
            $html .= '</tr>';
            $rowIndex++;
        }
    }

    $html .= '</tbody></table></div>';

    return $html;
}