<?php

/**
 * Get the URL for a signature image
 * 
 * @param string $filename The signature filename
 * @return string The URL to the signature image
 */
function get_signature_url($filename)
{
    if (empty($filename)) {
        return base_url('assets/images/no-signature.png'); // Default placeholder
    }

    // Create a method in a controller that will read from writable directory
    return site_url('api/signature-show/' . $filename);
}
