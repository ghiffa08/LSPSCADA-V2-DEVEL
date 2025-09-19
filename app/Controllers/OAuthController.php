<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\GoogleOAuthService;
use App\Services\Authentication\AuthenticationService;
use App\Models\AsesiModel;
use LogicException;

class OAuthController extends BaseController
{
    protected GoogleOAuthService $googleService;
    protected AuthenticationService $authService;

    public function __construct()
    {
        $this->googleService = new GoogleOAuthService();
        $this->authService = new AuthenticationService();
        $this->asesiModel = new AsesiModel();
        
        // Load auth helper jika belum di-autoload
        helper('auth');
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
        }

        // Prepare OAuth data for AuthenticationService
        $oauthData = [
            'email' => $googleUser['email'],
            'name' => $googleUser['name'] ?? $googleUser['email'],
            'username' => explode('@', $googleUser['email'])[0],
            'id' => $googleUser['sub'],
            'role' => 'asesi' // Default role for Google OAuth users
        ];

        // Use AuthenticationService for OAuth login
        $authResponse = $this->authService->loginWithOAuth($oauthData, 'google');
        
        if ($authResponse->isSuccess()) {
            // Get the user ID from yth/auth library
            $userId = user()->id ?? null;
            
            // Fallback jika auth() belum tersedia
            if (!$userId) {
                $userId = session()->get('logged_in') ? session()->get('user_id') : null;
            }
            
            if ($userId) {
                // Check if asesi data already exists for this user
                $existingAsesi = $this->asesiModel->where('id_user', $userId)->first();
                
                if (!$existingAsesi) {
                    // Create asesi record with available OAuth data
                    $asesiData = [
                        'id_user' => $userId,
                        'kode_asesi' => $this->generateKodeAsesi(),
                        'kebangsaan' => 'WNI', // Default value
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    
                    try {
                        $this->asesiModel->insert($asesiData);
                        log_message('info', 'Asesi record created for OAuth user: ' . $oauthData['email']);
                    } catch (\Exception $e) {
                        log_message('error', 'Failed to create asesi record for OAuth user: ' . $oauthData['email'] . ', error: ' . $e->getMessage());
                    }
                }
            } else {
                log_message('error', 'Unable to get user ID after OAuth login for: ' . $oauthData['email']);
            }
            
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

    /**
     * Generate unique kode_asesi dengan format: LSPP1-2KNG-25-001
     */
    private function generateKodeAsesi(): string
    {
        // LSPP1-2KNG-25-001
        // LSPP1: Lembaga Sertifikasi Profesi Pihak Pertama
        // 2KNG: Kode wilayah/lokasi (2 Kuningan)
        // 25: Tahun (2025)
        // 001: Nomor urut asesi
        
        $lsp = 'LSPP1';
        $wilayah = 'SMKN2KNG';
        $tahun = date('y'); // 2 digit tahun terakhir
        
        // Generate nomor urut berdasarkan pattern yang sama di tahun ini
        $pattern = "{$lsp}-{$wilayah}-{$tahun}-%";
        $existingCount = $this->asesiModel
            ->like('kode_asesi', $pattern, 'both')
            ->countAllResults();
        
        $nomorUrut = str_pad(($existingCount + 1), 3, '0', STR_PAD_LEFT);
        
        // Pastikan kode belum ada (double check)
        do {
            $kodeAsesi = "{$lsp}-{$wilayah}-{$tahun}-{$nomorUrut}";
            $exists = $this->asesiModel->where('kode_asesi', $kodeAsesi)->first();
            
            if ($exists) {
                $nomorUrut = str_pad((intval($nomorUrut) + 1), 3, '0', STR_PAD_LEFT);
            }
        } while ($exists);
        
        return $kodeAsesi;
    }
}
