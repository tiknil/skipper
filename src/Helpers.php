<?php

if (!function_exists('slug')) {
    function slug(string $str): string
    {
        return preg_replace('/[^A-Za-z0-9-]+/', '-', strtolower($str));
    }
}

if (!function_exists('path')) {
    function path(string $path): string
    {
        return rtrim(dirname(__FILE__, 2), '/').'/'.ltrim($path, '/');
    }
}
