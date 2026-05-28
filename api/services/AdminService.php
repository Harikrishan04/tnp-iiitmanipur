<?php
/**
 * AdminService — Business logic for TNP administrative operations.
 */

declare(strict_types=1);

namespace App\Services;

use App\Config\Database;
use App\Helpers\Logger;
use App\Models\AdminModel;
use PDO;

class AdminService
{
    private PDO $db;
    private AdminModel $adminModel;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->adminModel = new AdminModel($this->db);
    }

    // ═══ PLACEMENT SESSIONS ═══

    public function listSessions(): array
    {
        $sessions = $this->adminModel->getSessions();
        return ['success' => true, 'data' => $sessions];
    }

    public function createSession(string $label, string $startDate, string $endDate, ?string $createdBy): array
    {
        if (empty($label)) {
            return ['success' => false, 'message' => 'Session label is required (e.g. 2025-26).'];
        }

        if (strtotime($endDate) <= strtotime($startDate)) {
            return ['success' => false, 'message' => 'End date must be after start date.'];
        }

        try {
            $created = $this->adminModel->createSession($label, $startDate, $endDate, $createdBy);
            if ($created) {
                Logger::info('admin', "Placement session created", ['label' => $label]);
                return ['success' => true, 'message' => 'Placement session created successfully.'];
            }
            return ['success' => false, 'message' => 'Failed to create placement session.'];
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                return ['success' => false, 'message' => 'Session label must be unique.'];
            }
            Logger::error('admin', "Failed creating session", ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Database error creating session.'];
        }
    }

    public function activateSession(string $sessionId): array
    {
        $session = $this->adminModel->findSessionById($sessionId);
        if (!$session) {
            return ['success' => false, 'message' => 'Placement session not found.'];
        }

        try {
            $activated = $this->adminModel->activateSession($sessionId);
            if ($activated) {
                Logger::info('admin', "Placement session activated", ['session_id' => $sessionId]);
                return ['success' => true, 'message' => 'Session activated successfully. All other sessions deactivated.'];
            }
            return ['success' => false, 'message' => 'Failed to activate session or already active.'];
        } catch (\PDOException $e) {
            Logger::error('admin', "Failed activating session", ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Database error activating session.'];
        }
    }

    // ═══ ANNOUNCEMENTS ═══

    public function listAnnouncements(): array
    {
        $announcements = $this->adminModel->getAnnouncements();
        
        // Fetch targets for each announcement
        foreach ($announcements as &$ann) {
            $ann['visible_to_roles'] = json_decode($ann['visible_to_roles_json'], true) ?: [];
            $ann['targets'] = $this->adminModel->getAnnouncementTargets($ann['announcement_id']);
        }

        return ['success' => true, 'data' => $announcements];
    }

    public function createAnnouncement(
        string $postedBy,
        string $postedByRole,
        string $title,
        string $body,
        ?string $priority,
        ?string $jobId,
        array $visibleToRoles,
        array $targets,
        ?string $publishAt,
        ?string $expiresAt,
        string $status
    ): array {
        if (empty($title) || empty($body)) {
            return ['success' => false, 'message' => 'Title and body are required fields.'];
        }

        $visibleToRolesJson = json_encode($visibleToRoles);

        try {
            $this->db->beginTransaction();

            $annId = $this->adminModel->createAnnouncement(
                $postedBy, $postedByRole, $title, $body, $priority, $jobId, $visibleToRolesJson, $publishAt, $expiresAt, $status
            );

            // Add targets
            foreach ($targets as $target) {
                if (!empty($target['type'])) {
                    $this->adminModel->addAnnouncementTarget($annId, $target['type'], $target['value'] ?? null);
                }
            }

            $this->db->commit();
            Logger::info('admin', "Announcement created", ['announcement_id' => $annId, 'title' => $title]);

            return ['success' => true, 'message' => 'Announcement posted successfully.', 'data' => ['announcement_id' => $annId]];
        } catch (\PDOException $e) {
            $this->db->rollBack();
            Logger::error('admin', "Failed creating announcement", ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Failed to create announcement.'];
        }
    }

    public function deleteAnnouncement(string $id): array
    {
        try {
            $deleted = $this->adminModel->deleteAnnouncement($id);
            if ($deleted) {
                Logger::info('admin', "Announcement deleted", ['announcement_id' => $id]);
                return ['success' => true, 'message' => 'Announcement deleted successfully.'];
            }
            return ['success' => false, 'message' => 'Announcement not found or already deleted.'];
        } catch (\PDOException $e) {
            Logger::error('admin', "Failed deleting announcement", ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Failed to delete announcement.'];
        }
    }

    // ═══ STATISTICS ═══

    public function getStatistics(): array
    {
        try {
            $stats = $this->adminModel->getOverviewStats();
            return ['success' => true, 'data' => $stats];
        } catch (\PDOException $e) {
            Logger::error('admin', "Failed fetching stats", ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Failed to fetch statistics.'];
        }
    }

    // ═══ USER MANAGEMENT ═══

    public function listUsers(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $users = $this->adminModel->listUsers($filters, $perPage, $offset);
        $total = $this->adminModel->countUsers($filters);

        return [
            'success' => true,
            'data' => $users,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => (int) ceil($total / max($perPage, 1))
            ]
        ];
    }

    public function updateUserStatus(string $userId, bool $isActive): array
    {
        try {
            $updated = $this->adminModel->updateUserStatus($userId, $isActive);
            if ($updated) {
                Logger::info('admin', "User status updated", ['user_id' => $userId, 'is_active' => $isActive]);
                return ['success' => true, 'message' => 'User status updated successfully.'];
            }
            return ['success' => false, 'message' => 'Failed to update user status or user not found.'];
        } catch (\PDOException $e) {
            Logger::error('admin', "Failed updating user status", ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Failed to update user status.'];
        }
    }

    public function createCoordinator(
        string $email,
        ?string $phone,
        string $name,
        string $deptCode,
        string $designation,
        string $team,
        string $coordType,
        ?string $adminId
    ): array {
        if (empty($email) || empty($name)) {
            return ['success' => false, 'message' => 'Email and name are required.'];
        }

        try {
            $newId = $this->adminModel->createCoordinatorAccount(
                $email, $phone, $name, $deptCode, $designation, $team, $coordType, $adminId
            );

            if ($newId) {
                Logger::info('admin', "Coordinator created", ['email' => $email, 'user_id' => $newId]);
                return ['success' => true, 'message' => 'Coordinator created successfully.', 'data' => ['user_id' => $newId]];
            }
            return ['success' => false, 'message' => 'Failed to create coordinator.'];
        } catch (\PDOException $e) {
            if ($e->getCode() === '23000') {
                return ['success' => false, 'message' => 'Email is already registered.'];
            }
            Logger::error('admin', "Failed creating coordinator", ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Database error creating coordinator.'];
        }
    }
}
