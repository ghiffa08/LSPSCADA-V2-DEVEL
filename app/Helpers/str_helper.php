<?php

if (!function_exists('auto_capitalize')) {
    function auto_capitalize($string)
    {
        $string = str_replace('_', ' ', $string); // ubah snake_case ke space
        return ucwords(strtolower($string)); // kapitalisasi awal kata
    }
}

if (!function_exists('format_uppercase_label')) {
    function format_uppercase($string)
    {
        $string = str_replace('_', ' ', $string);
        return strtoupper($string);
    }
}

function getInitials($name)
{
    $words = explode(' ', strtoupper(trim($name)));
    return ($words[0][0] ?? '') . ($words[1][0] ?? '');
}



function getStatusBadge($status)
{
    $badgeColor = '';
    switch ($status) {
        case 'validated':
            $badgeColor = 'success';
            break;
        case 'pending':
            $badgeColor = 'warning';
            break;
        default:
            $badgeColor = 'danger';
    }

    return '<div class="badge badge-' . $badgeColor . '">' . $status . '</div>';
}
