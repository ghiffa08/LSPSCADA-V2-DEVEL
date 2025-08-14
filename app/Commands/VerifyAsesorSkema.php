<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\AsesorModel;
use App\Models\AsesorSkemaModel;
use App\Models\SkemaModel;

class VerifyAsesorSkema extends BaseCommand
{
    protected $group       = 'Asesor';
    protected $name        = 'asesor:verify-skema';
    protected $description = 'Verify and fix asesor to skema relationships';

    public function run(array $params)
    {
        CLI::write('Starting verification of asesor-skema relationships...', 'green');

        $asesorModel = new AsesorModel();
        $asesorSkemaModel = new AsesorSkemaModel();
        $skemaModel = new SkemaModel();

        // Get all asesors
        $asesors = $asesorModel->findAll();
        CLI::write('Found ' . count($asesors) . ' asesors', 'yellow');

        // Get all active skemas
        $skemas = $skemaModel->getActiveSchemes();
        CLI::write('Found ' . count($skemas) . ' active skemas', 'yellow');

        $fixMode = CLI::prompt('Enter fix mode', ['verify', 'fix'], 'verify');

        foreach ($asesors as $asesor) {
            $id_asesor = $asesor['id_asesor'];
            $user_id = $asesor['id_user'];

            // Get current skema assignments
            $currentSkemas = $asesorSkemaModel->getSkemasByAsesor($id_asesor);
            $skemaCount = count($currentSkemas);

            CLI::write("Asesor #{$id_asesor} (User ID: {$user_id}): {$asesor['nomor_registrasi']} - Has {$skemaCount} skema(s)", 'yellow');

            if ($skemaCount === 0) {
                // No assigned skemas, problematic
                CLI::write("  - WARNING: No skemas assigned to this asesor!", 'red');

                if ($fixMode === 'fix' && count($skemas) > 0) {
                    // Assign the first skema
                    $skemaIds = [$skemas[0]['id_skema']];
                    $result = $asesorModel->updateAsesorSkema($id_asesor, $skemaIds);

                    if ($result) {
                        CLI::write("    - FIXED: Assigned skema {$skemas[0]['kode_skema']} ({$skemas[0]['nama_skema']})", 'green');
                    } else {
                        CLI::write("    - ERROR: Failed to assign skema", 'red');
                    }
                }
            } else {
                // Show assigned skemas
                foreach ($currentSkemas as $index => $skema) {
                    $num = $index + 1;
                    CLI::write("  - [{$num}] ID: {$skema['id_skema']} | {$skema['kode_skema']} - {$skema['nama_skema']}", 'green');
                }
            }
        }

        CLI::write('Verification completed!', 'green');
    }
}
