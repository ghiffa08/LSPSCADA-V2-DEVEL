<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;

class ProcessObservasiReport extends BaseCommand
{
    protected $group = 'reports';
    protected $name = 'observasi:process-report';

    public function run(array $params)
    {
        $asesorId = $params[0] ?? null;

        if (!$asesorId) {
            CLI::error('Asesor ID required');
            return;
        }

        // Process heavy report generation
        $service = new \App\Services\ObservasiService();
        $report = $service->generateDetailedReport($asesorId);

        // Store in cache for quick retrieval
        cache()->save("detailed_report_{$asesorId}", $report, 3600);

        CLI::write("Report generated for asesor {$asesorId}");
    }
}
