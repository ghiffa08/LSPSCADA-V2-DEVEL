<?php

if (!function_exists('auto_capitalize')) {
    function auto_capitalize($string)
    {
        $string = str_replace('_', ' ', $string); // ubah snake_case ke space
        return ucwords(strtolower($string)); // kapitalisasi awal kata
    }
}
