<?php

namespace App\Events;

class ObservasiEvents
{
    public static function onObservasiSaved($observasiData)
    {
        // Trigger notifications
        Events::trigger('observasi_saved', $observasiData);

        // Update statistics asynchronously
        Events::trigger('update_stats', $observasiData['id_asesor']);

        // Send email notifications if needed
        if ($observasiData['status'] === 'completed') {
            Events::trigger('observasi_completed', $observasiData);
        }
    }
}

// Di ObservasiService.php, tambahkan setelah save berhasil:
Events::trigger('observasi_saved', $result['data']);
