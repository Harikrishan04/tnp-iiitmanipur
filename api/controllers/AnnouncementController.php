<?php
/**
 * AnnouncementController — Handles public and user-facing announcements.
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;
use App\Services\AdminService;

class AnnouncementController
{
    private AdminService $service;

    public function __construct()
    {
        $this->service = new AdminService();
    }

    /**
     * GET /public/announcements
     */
    public function listPublic(array $params = [], ?array $user = null): void
    {
        $result = $this->service->listPublicAnnouncements();
        if ($result['success']) {
            Response::success($result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * GET /user/announcements
     */
    public function listForUser(array $params = [], ?array $user = null): void
    {
        $userId = $user['sub'] ?? '';
        $role = $user['role'] ?? '';

        $result = $this->service->listAnnouncementsForUser($userId, $role);
        if ($result['success']) {
            Response::success($result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * POST /user/announcements/{id}/read
     */
    public function markAsRead(array $params = [], ?array $user = null): void
    {
        $userId = $user['sub'] ?? '';
        $annId = $params['id'] ?? '';

        $result = $this->service->markAnnouncementAsRead($userId, $annId);
        if ($result['success']) {
            Response::success(null, 200, $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
