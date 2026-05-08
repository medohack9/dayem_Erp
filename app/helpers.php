<?php

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $basePath = $GLOBALS['app_config']['base_path'] ?? '';
        return $basePath . $path;
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        $basePath = $GLOBALS['app_config']['base_path'] ?? '';
        return $basePath . $path;
    }
}