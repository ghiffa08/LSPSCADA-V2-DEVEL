<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\GoogleOAuthService;
use App\Services\Authentication\AuthenticationService;
use LogicException;

class OAuthController extends BaseController
{
    protected GoogleOAuthService $googleService;
    protected AuthenticationService $authService;

    public function __construct()
    {
        $this->googleService = new GoogleOAuthService();
        $this->authService = new AuthenticationService();
    }

    public function index()
    {
        //
    }

    public function google()
    {
        // Redirect user to Google OAuth consent screen
        $authUrl = $this->googleService->getAuthUrl();
        return redirect()->to($authUrl);
    }

    public function proses()
    {
        $code = $this->request->getGet('code');
        if (!$code) {
            return redirect()->to(site_url('login'))->with('error', 'Google login gagal.');
        }

        try {
            $googleUser = $this->googleService->fetchUserFromCode($code);
        } catch (LogicException $e) {
            return redirect()->to(site_url('login'))->with('error', $e->getMessage());
        }        // Prepare OAuth data for AuthenticationService
        $oauthData = [
            'email' => $googleUser['email'],
            'name' => $googleUser['name'] ?? $googleUser['email'],
            'username' => explode('@', $googleUser['email'])[0],
            'id' => $googleUser['sub'],
            'role' => 'asesi' // Default role for Google OAuth users
        ];

        // Log OAuth attempt for debugging
        log_message('info', 'OAuth login attempt for: ' . $oauthData['email']);

        // Use AuthenticationService for OAuth login
        $authResponse = $this->authService->loginWithOAuth($oauthData, 'google');
        if ($authResponse->isSuccess()) {
            // Session sudah di-handle oleh AuthenticationService, jangan regenerate lagi
            $redirectUrl = $authResponse->getRedirectUrl() ?: site_url('asesi/dashboard');
            log_message('info', 'OAuth login successful for: ' . $oauthData['email'] . ', redirecting to: ' . $redirectUrl);
            return redirect()->to($redirectUrl)
                ->with('message', $authResponse->getMessage());
        } else {
            // OAuth login failed
            log_message('error', 'OAuth login failed for: ' . $oauthData['email'] . ', error: ' . $authResponse->getMessage());
            return redirect()->to(site_url('login'))
                ->with('error', 'OAuth login failed: ' . $authResponse->getMessage());
        }
    }
}
