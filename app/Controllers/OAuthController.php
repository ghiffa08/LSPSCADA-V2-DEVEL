<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\GoogleOAuthService;
use LogicException;

class OAuthController extends BaseController
{

    protected GoogleOAuthService $googleService;
    public function __construct()
    {
        $this->googleService = new GoogleOAuthService();
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
        $userModel = new \App\Models\UserModel();
        $userMythModel = new \App\Models\UserMythModel();
        $groupUserModel = new \App\Models\GroupUserModel();
        $user = $userModel->where('email', $googleUser['email'])->first();
        $db = \Config\Database::connect();
        if (!$user) {
            // Register as asesi by default
            $userId = $userModel->insert([
                'email' => $googleUser['email'],
                'password_hash' => password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT),
                'nama_lengkap' => $googleUser['name'] ?? $googleUser['email'],
                'google_id' => $googleUser['sub'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ], true);
            $user = $userModel->find($userId);
            // Insert to asesi table
            $db->table('asesi')->insert([
                'id_user' => $userId,
                'nik' => '',
                'tempat_lahir' => '',
                'tanggal_lahir' => null,
                'jenis_kelamin' => null,
                'pendidikan_terakhir' => null,
                'no_hp' => null,
            ]);
        } else {
            $userId = $user['id'];
            // Update google_id if not set
            if (empty($user['google_id'])) {
                $userModel->update($userId, ['google_id' => $googleUser['sub']]);
            }
        }
        // Pastikan user hanya di grup Asesi
        $groupId = $db->table('auth_groups')->where('name', 'Asesi')->get()->getFirstRow('array')['id'] ?? 36;
        // Hapus semua group user lama
        $db->table('auth_groups_users')->where('user_id', $userId)->delete();
        // Assign ke grup Asesi
        $db->table('auth_groups_users')->insert([
            'group_id' => $groupId,
            'user_id' => $userId
        ]);
        // Ambil entity user untuk Myth/Auth
        $userEntity = $userMythModel->where('email', $googleUser['email'])->first();
        // Ensure $userEntity is always an instance of App\Entities\User
        if (!($userEntity instanceof \App\Entities\User)) {
            $userEntity = new \App\Entities\User((array) $userEntity);
        }
        $auth = service('authentication');
        $auth->login($userEntity);
        // Redirect ke dashboard sesuai role
        if ($userEntity->isAdmin()) {
            return redirect()->to(site_url('admin/dashboard'));
        } elseif ($userEntity->isAsesor()) {
            return redirect()->to(site_url('asesor/dashboard'));
        } elseif ($userEntity->isAsesi()) {
            return redirect()->to(site_url('asesi/dashboard'));
        } else {
            return redirect()->to(site_url('dashboard'));
        }
    }
}
