<?php

namespace App\Services;

use App\Repositories\AsesorRepository;
use App\Exceptions\AuthException;
use CodeIgniter\Cache\CacheInterface;

/**
 * Authentication Service
 * 
 * Handles user authentication and authorization for the application
 * Implements caching for better performance
 */
class AuthService
{
    protected AsesorRepository $asesorRepository;
    protected CacheInterface $cache;

    private const CACHE_TTL = 3600; // 1 hour

    public function __construct()
    {
        $this->asesorRepository = new AsesorRepository();
        $this->cache = service('cache');
    }

    /**
     * Get current user data
     * 
     * @return array
     */
    public function getCurrentUser(): array
    {
        try {
            helper('auth');
            $user = user();

            if (!$user) {
                $session = session();
                $userData = $session->get('user_data');

                if (!$userData) {
                    throw new \Exception('No user session found');
                }

                return is_array($userData) ? $userData : ['id' => $userData->id ?? null];
            }

            return is_array($user) ? $user : [
                'id' => $user->id ?? null,
                'username' => $user->username ?? null,
                'email' => $user->email ?? null
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error getting current user: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get current asesor data
     * 
     * @return array
     * @throws AuthException
     */
    public function getCurrentAsesor(): array
    {
        $user = $this->getCurrentUser();

        if (empty($user) || !isset($user['id'])) {
            throw AuthException::notAuthenticated();
        }

        $cacheKey = "asesor_data_{$user['id']}";

        if ($cached = $this->cache->get($cacheKey)) {
            return $cached;
        }

        $asesor = $this->asesorRepository->findByUserId($user['id']);

        if (!$asesor) {
            throw AuthException::accessDenied('User is not an asesor');
        }

        $this->cache->save($cacheKey, $asesor, self::CACHE_TTL);

        return $asesor;
    }

    /**
     * Check if user has specific role
     * 
     * @param array|string $roles
     * @return bool
     */
    public function hasRole($roles): bool
    {
        try {
            helper('auth');

            if (is_string($roles)) {
                $roles = [$roles];
            }

            return in_groups($roles);
        } catch (Exception $e) {
            log_message('error', 'Error checking roles: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if asesor can access specific skema
     * 
     * @param int $skemaId
     * @return bool
     */
    public function canAccessSkema(int $skemaId): bool
    {
        try {
            $asesorData = $this->getCurrentAsesor();
            return $asesorData['id_skema'] === $skemaId;
        } catch (AuthException $e) {
            return false;
        }
    }

    /**
     * Check if user can access specific asesmen
     */
    public function canAccessAsesmen(int $userId, int $asesmenId): bool
    {
        try {
            // Basic implementation - can be enhanced with proper authorization logic
            return true; // For now, allow access
        } catch (Exception $e) {
            log_message('error', 'Error checking asesmen access: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if user can create observasi
     */
    public function canCreateObservasi(int $userId, int $asesmenId): bool
    {
        try {
            return true; // Basic implementation
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check if user can edit observasi
     */
    public function canEditObservasi(int $userId, int $observasiId): bool
    {
        try {
            return true; // Basic implementation
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check if user can delete observasi
     */
    public function canDeleteObservasi(int $userId, int $observasiId): bool
    {
        try {
            return true; // Basic implementation
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check if user can view observasi
     */
    public function canViewObservasi(int $userId, int $observasiId): bool
    {
        try {
            return true; // Basic implementation
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Check if user can submit observasi
     */
    public function canSubmitObservasi(int $userId, int $observasiId): bool
    {
        try {
            return true; // Basic implementation
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Clear asesor cache
     * 
     * @param int|null $userId
     * @return void
     */
    public function clearAsesorCache(?int $userId = null): void
    {
        if (!$userId) {
            $user = $this->getCurrentUser();
            $userId = $user['id'] ?? null;
        }

        if ($userId) {
            $this->cache->delete("asesor_data_{$userId}");
        }
    }
}
