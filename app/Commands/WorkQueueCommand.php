<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

/**
 * Command to process queue jobs
 */
class WorkQueueCommand extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Queue';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'queue:work';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Process the next job on the queue';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'queue:work [options]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [
        '--queue'    => 'The queue to process (default: default)',
        '--daemon'   => 'Run the worker in daemon mode (continuously)',
        '--sleep'    => 'Number of seconds to sleep when no job is available (default: 3)',
        '--tries'    => 'Number of times to try a job before logging it as failed (default: 3)',
    ];

    /**
     * Actually execute a command.
     *
     * @param array $params
     * @return void
     */
    public function run(array $params)
    {
        // Get command options
        $queue = $params['queue'] ?? CLI::getOption('queue') ?? 'default';
        $daemon = $params['daemon'] ?? CLI::getOption('daemon') ?? false;
        $sleep = (int)($params['sleep'] ?? CLI::getOption('sleep') ?? 3);
        $tries = (int)($params['tries'] ?? CLI::getOption('tries') ?? 3);

        // Get the queue service
        $queueService = Services::queue();

        CLI::write('Queue worker started', 'green');
        CLI::write("Queue: {$queue}");
        CLI::write("Mode: " . ($daemon ? 'daemon' : 'single job'));

        if ($daemon) {
            CLI::write("Sleep: {$sleep}s");
            CLI::write("Max attempts: {$tries}");
            CLI::write("Press Ctrl+C to stop the worker");
            CLI::newLine();
        }

        // Process jobs
        do {
            CLI::write('Checking for jobs...', 'yellow');

            $processed = $queueService->work($queue, $tries);

            if ($processed) {
                CLI::write('Job processed successfully', 'green');
            } else {
                CLI::write('No job available', 'yellow');

                if ($daemon) {
                    CLI::write("Sleeping for {$sleep} seconds...");
                    sleep($sleep);
                }
            }
        } while ($daemon);

        CLI::write('Worker stopped', 'green');
    }
}
