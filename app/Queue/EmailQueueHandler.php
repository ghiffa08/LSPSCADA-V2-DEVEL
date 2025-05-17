<?php

namespace App\Queue;

use CodeIgniter\I18n\Time;

/**
 * Email Queue Handler
 * 
 * Handles asynchronous email sending tasks
 */
class EmailQueueHandler extends BaseHandler
{
    /**
     * Process an email sending job
     *
     * @param array $data Job data containing email parameters
     * @return bool
     */
    public function sendAsesmenEmail(array $data): bool
    {
        try {
            log_message('info', 'Processing email queue for Asesmen notification: ' . json_encode($data));

            $to = $data['to'] ?? null;
            $name = $data['name'] ?? 'Pendaftar';
            $id = $data['id'] ?? '';

            if (empty($to)) {
                log_message('error', 'Email queue processing failed: Missing recipient email');
                return false;
            }

            $subject = 'Pendaftaran Uji Kompetensi Keahlian';

            // Load HTML view email content
            $message = view('email/email_message', [
                'name'    => $name,
                'id'      => $id,
                'sent_at' => Time::now()->toDateTimeString()
            ]);

            // Gunakan helper yang kamu buat
            $result = sendEmail($to, $subject, $message);

            if (!$result) {
                log_message('error', 'Email queue job failed for recipient: ' . $to);
                return false;
            }

            log_message('info', 'Email queue job successfully processed for: ' . $to);
            return true;
        } catch (\Exception $e) {
            $this->failed($data, $e);
            return false;
        }
    }

    /**
     * Handle failed email jobs
     *
     * @param array $data
     * @param \Exception $e
     * @return void
     */
    public function failed(array $data, \Exception $e): void
    {
        log_message('critical', 'Email queue job failed with exception: ' . $e->getMessage());

        $logData = [
            'timestamp' => Time::now()->toDateTimeString(),
            'to'        => $data['to'] ?? 'unknown',
            'error'     => $e->getMessage(),
            'trace'     => $e->getTraceAsString()
        ];

        log_message('error', 'Failed email job details: ' . json_encode($logData));
    }
}
