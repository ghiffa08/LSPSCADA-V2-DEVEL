<?php

use App\Models\AsesiModel;

if (!function_exists('asesi_data')) {
    /**
     * Fetches the asesi data for the currently logged-in user.
     *
     * This function is designed to be efficient. It uses a static variable
     * to cache the database result, ensuring the query is only run once
     * per request, even if the function is called multiple times.
     *
     * @return array|null Returns the asesi data as an array, or null if not found or not logged in.
     */
    function asesi_data(): ?array
    {
        // Static variable to hold the asesi data once fetched.
        static $asesi = null;

        // Only query the database if we haven't fetched the data yet in this request.
        if ($asesi === null) {
            // Check if a user is logged in using Myth/Auth's helper.
            if (logged_in()) {
                $asesiModel = new AsesiModel();
                // Find the asesi record linked to the current user's ID.
                $data = $asesiModel->where('id_user', user_id())->first();
                // If data is found, assign it to our static variable.
                // Otherwise, explicitly set it to an empty array to prevent re-querying.
                $asesi = $data ? $data : []; 
            } else {
                // If not logged in, set to an empty array to avoid errors.
                $asesi = [];
            }
        }
        
        // Return the asesi data (either from cache or the fresh query result).
        // Return null if the array is empty.
        return !empty($asesi) ? $asesi : null;
    }
}
