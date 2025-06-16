<?php
// Simulate going to /dashboard after OAuth login
// This will help debug what happens in DashboardRouterController

// Start session
session_start();

echo "<h1>OAuth Dashboard Redirect Test</h1>\n";

// Set session data as if OAuth login just completed
$_SESSION['logged_in'] = 36;
$_SESSION['user_email'] = '20240810005@uniku.ac.id';
$_SESSION['roles'] = ['Asesi'];

echo "<p>Session set to simulate OAuth login:</p>\n";
echo "<pre>";
print_r($_SESSION);
echo "</pre>\n";

echo "<p><a href='/dashboard'>Go to Dashboard</a> (this should trigger the issue)</p>\n";
echo "<p><a href='/asesi/dashboard'>Go to Asesi Dashboard</a> (direct route)</p>\n";

echo "<h2>Debug Info:</h2>\n";
echo "<p>Current URL: " . $_SERVER['REQUEST_URI'] . "</p>\n";
echo "<p>Timestamp: " . date('Y-m-d H:i:s') . "</p>\n";
