<?php
/**
 * AdminController — Handles administrative endpoints.
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;
use App\Helpers\Validator;
use App\Services\AdminService;

class AdminController
{
    private AdminService $service;

    public function __construct()
    {
        $this->service = new AdminService();
    }

    // ═══ SESSIONS ═══

    public function listSessions(array $params = [], ?array $user = null): void
    {
        $result = $this->service->listSessions();
        if ($result['success']) {
            Response::success($result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function createSession(array $params = [], ?array $user = null): void
    {
        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true);

        if (!is_array($input) || empty($input['label']) || empty($input['start_date']) || empty($input['end_date'])) {
            Response::error('label, start_date, and end_date are required.', 422);
        }

        $result = $this->service->createSession(
            $input['label'],
            $input['start_date'],
            $input['end_date'],
            $user['sub'] ?? null
        );

        if ($result['success']) {
            Response::success(null, 201, $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function activateSession(array $params = [], ?array $user = null): void
    {
        $id = $params['id'] ?? '';
        if (!Validator::uuid($id)) {
            Response::error('Invalid session ID.', 400);
        }

        $result = $this->service->activateSession($id);
        if ($result['success']) {
            Response::success(null, 200, $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    // ═══ ANNOUNCEMENTS ═══

    public function listAnnouncements(array $params = [], ?array $user = null): void
    {
        $result = $this->service->listAnnouncements();
        if ($result['success']) {
            Response::success($result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    public function createAnnouncement(array $params = [], ?array $user = null): void
    {
        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true);

        if (!is_array($input) || empty($input['title']) || empty($input['body'])) {
            Response::error('title and body are required fields.', 422);
        }

        $postedBy = $user['sub'] ?? '';
        $postedByRole = $user['role'] ?? 'admin';

        $result = $this->service->createAnnouncement(
            $postedBy,
            $postedByRole,
            $input['title'],
            $input['body'],
            $input['priority'] ?? 'normal',
            $input['job_id'] ?? null,
            $input['visible_to_roles'] ?? ['student', 'recruiter'],
            $input['targets'] ?? [],
            $input['publish_at'] ?? null,
            $input['expires_at'] ?? null,
            $input['status'] ?? 'published',
            $input['attachments'] ?? [],
            isset($input['send_email']) ? (bool) $input['send_email'] : true
        );

        if ($result['success']) {
            Response::success($result['data'], 201, $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function deleteAnnouncement(array $params = [], ?array $user = null): void
    {
        $id = $params['id'] ?? '';
        if (!Validator::uuid($id)) {
            Response::error('Invalid announcement ID.', 400);
        }

        $result = $this->service->deleteAnnouncement($id);
        if ($result['success']) {
            Response::success(null, 200, $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    // ═══ STATISTICS ═══

    public function getStats(array $params = [], ?array $user = null): void
    {
        $result = $this->service->getStatistics();
        if ($result['success']) {
            Response::success($result['data']);
        } else {
            Response::error($result['message']);
        }
    }

    // ═══ USER MANAGEMENT ═══

    public function listUsers(array $params = [], ?array $user = null): void
    {
        $pagination = Validator::pagination($_GET);
        $filters = [
            'role' => $_GET['role'] ?? null,
            'is_active' => isset($_GET['is_active']) ? ($_GET['is_active'] === 'true' || $_GET['is_active'] === '1') : null,
        ];

        $result = $this->service->listUsers($filters, $pagination['page'], $pagination['per_page']);

        if ($result['success']) {
            Response::paginated(
                $result['data'],
                $result['meta']['page'],
                $result['meta']['per_page'],
                $result['meta']['total']
            );
        } else {
            Response::error($result['message']);
        }
    }

    public function updateUserStatus(array $params = [], ?array $user = null): void
    {
        $id = $params['id'] ?? '';
        if (!Validator::uuid($id)) {
            Response::error('Invalid user ID.', 400);
        }

        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true);

        if (!is_array($input) || !isset($input['is_active'])) {
            Response::error('is_active value is required.', 422);
        }

        $result = $this->service->updateUserStatus($id, (bool) $input['is_active']);

        if ($result['success']) {
            Response::success(null, 200, $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    public function createCoordinator(array $params = [], ?array $user = null): void
    {
        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true);

        if (!is_array($input) || empty($input['email']) || empty($input['name'])) {
            Response::error('email and name are required fields.', 422);
        }

        $adminId = !empty($user['sub']) ? $user['sub'] : null;

        $result = $this->service->createCoordinator(
            $input['email'],
            !empty($input['phone']) ? $input['phone'] : null,
            $input['name'],
            $input['dept_code'] ?? 'CSE', // Defaulting for now if not provided
            $input['designation'] ?? 'Coordinator',
            $input['team'] ?? 'Placement Team',
            $input['coord_type'] ?? 'faculty',
            $adminId
        );

        if ($result['success']) {
            Response::success($result['data'], 201, $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
