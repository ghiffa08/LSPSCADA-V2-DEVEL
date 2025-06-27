<?php

// Test cache methods in CodeIgniter 4
require_once 'vendor/autoload.php';

// Load CodeIgniter environment
$app = \Config\Services::codeigniter();

try {
    $cache = \Config\Services::cache();
    echo "Cache class: " . get_class($cache) . "\n";
    echo "Available methods:\n";
    $methods = get_class_methods($cache);
    foreach ($methods as $method) {
        echo "- $method\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
