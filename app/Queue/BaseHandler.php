<?php

namespace App\Queue;

abstract class BaseHandler
{
    /**
     * Override this in your handler to process a queue job.
     * @param array $data
     * @return bool
     */
    abstract public function sendAsesmenEmail(array $data): bool;

    /**
     * Handle job failure.
     * @param array $data
     * @param \Exception $e
     * @return void
     */
    public function failed(array $data, \Exception $e): void
    {
        // Default empty — override as needed
    }
}
