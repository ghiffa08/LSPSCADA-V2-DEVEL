<?php

// Test script to check asesor-skema relationship

// Load the environment
require '../vendor/autoload.php';
require '../app/Config/Paths.php';

// Import necessary classes
use Config\Services;
use App\Models\AsesorModel;
use App\Models\SkemaModel;

// Helper function to print results nicely
function printResult($label, $data)
{
    echo "<h3>{$label}</h3>";
    echo "<pre>";
    print_r($data);
    echo "</pre>";
    echo "<hr>";
}

// Initialize models
$asesorModel = new AsesorModel();
$skemaModel = new SkemaModel();

// 1. Get a list of all active skemas
$activeSkemas = $skemaModel->getActiveSchemes();
printResult("Active Skemas", $activeSkemas);

// 2. Get a list of all asesors
$asesors = $asesorModel->findAll();
printResult("All Asesors", $asesors);

// 3. Check asesor-skema assignments
if (count($asesors) > 0) {
    $asesor = $asesors[0]; // Take the first asesor for testing
    $id_asesor = $asesor['id_asesor'];

    // Get skema assignments for this asesor
    $db = \Config\Database::connect();
    $asesor_skemas = $db->table('asesor_skema')
        ->where('id_asesor', $id_asesor)
        ->get()
        ->getResultArray();

    printResult("Skema assignments for Asesor ID: {$id_asesor}", $asesor_skemas);

    // If there are no assignments, let's add one for testing
    if (empty($asesor_skemas) && count($activeSkemas) > 0) {
        $skema_ids = [$activeSkemas[0]['id_skema']]; // Use the first active skema

        echo "<h3>Testing updateAsesorSkema with skema_ids: " . json_encode($skema_ids) . "</h3>";

        try {
            $result = $asesorModel->updateAsesorSkema($id_asesor, $skema_ids);
            echo "Result: " . ($result ? 'Success' : 'Failed');

            // Check again after update
            $asesor_skemas = $db->table('asesor_skema')
                ->where('id_asesor', $id_asesor)
                ->get()
                ->getResultArray();

            printResult("Skema assignments after update", $asesor_skemas);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}

// 4. Test user creation path with skema_ids
echo "<h2>User Creation Path Test</h2>";

// Mock data that simulates form submission
$mockFormData = [
    'username' => 'test_asesor_' . time(),
    'email' => 'test_' . time() . '@example.com',
    'nama_lengkap' => 'Test Asesor ' . time(),
    'password' => 'password123',
    'nomor_registrasi' => 'ASR-TEST-' . time(),
    'skema_ids' => []
];

// Add the first two active skemas
if (count($activeSkemas) > 0) {
    $mockFormData['skema_ids'][] = $activeSkemas[0]['id_skema'];

    if (count($activeSkemas) > 1) {
        $mockFormData['skema_ids'][] = $activeSkemas[1]['id_skema'];
    }
}

printResult("Mock Form Data", $mockFormData);

// Call the UserManagementModel method directly
$userManagementModel = new \App\Models\UserManagementModel();
$result = $userManagementModel->createAsesorUser($mockFormData);

printResult("User Creation Result", $result);

// If successful, check the skema assignments
if ($result['success']) {
    $asesor_id = $result['asesor_id'];

    $asesor_skemas = $db->table('asesor_skema')
        ->where('id_asesor', $asesor_id)
        ->get()
        ->getResultArray();

    printResult("New Asesor's Skema Assignments", $asesor_skemas);
}

// Show debug logs
$logger = Services::logger();
echo "<h2>Debug Log</h2>";
$logFile = WRITEPATH . 'logs/log-' . date('Y-m-d') . '.log';
if (file_exists($logFile)) {
    echo "<pre>" . htmlspecialchars(file_get_contents($logFile)) . "</pre>";
} else {
    echo "<p>No log file found for today.</p>";
}
