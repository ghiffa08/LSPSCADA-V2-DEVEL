<?php

namespace App\Services;

use Config\Services;

class EmailService
{
    /**
     * Send email using a view template
     *
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $templatePath Path to view template
     * @param array $data Data to pass to template
     * @return bool Success status
     */
    public function sendEmail(string $to, string $subject, string $templatePath, array $data = []): bool
    {
        $message = view($templatePath, $data);

        $email = Services::email();
        $email->setTo($to);
        $email->setFrom('lspp1smkn2kuningan@gmail.com', 'LSP - P1 SMK NEGERI 2 KUNINGAN');
        $email->setSubject($subject);
        $email->setMessage($message);
        $email->setMailType('html');

        if ($email->send()) {
            log_message('info', "✅ Email berhasil dikirim ke {$to} dengan subjek '{$subject}'.");
            return true;
        } else {
            log_message('error', "❌ Gagal mengirim email ke {$to} dengan subjek '{$subject}'.");
            log_message('debug', $email->printDebugger(['headers', 'subject', 'body']));
            return false;
        }
    }
}
