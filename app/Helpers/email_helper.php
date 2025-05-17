<?php

use Config\Services;

/**
 * Kirim Email Umum
 * 
 * @param string $to Email tujuan
 * @param string $subject Judul email
 * @param string $message Isi email (HTML)
 * @param array $options Opsi tambahan (optional), contoh: ['fromEmail' => 'xxx', 'fromName' => 'xxx']
 * @return bool True jika berhasil, False jika gagal
 */
function sendEmail($to, $subject, $message, $options = [])
{
    $email = Services::email();

    $fromEmail = $options['fromEmail'] ?? 'lspp1smkn2kuningan@gmail.com';
    $fromName = $options['fromName'] ?? 'LSP - P1 SMK NEGERI 2 KUNINGAN';

    $email->setTo($to);
    $email->setFrom($fromEmail, $fromName);
    $email->setSubject($subject);
    $email->setMessage($message);
    $email->setMailType('html'); // memastikan dikirim dalam format HTML


    if ($email->send()) {
        return true;
    } else {
        log_message('error', print_r($email->printDebugger(['headers']), true));
        return false;
    }
}
