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
            $ann['visible_to_roles'] = json_decode($ann['visible_to_roles_json'] ?? '[]', true) ?: [];
            $ann['attachments'] = json_decode($ann['attachments_json'] ?? '[]', true) ?: [];
            $ann['targets'] = $this->adminModel->getAnnouncementTargets($ann['announcement_id']);
            unset($ann['attachments_json']);
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
        string $status,
        ?array $attachments,
        bool $sendEmail
    ): array {
        if (empty($title) || empty($body)) {
            return ['success' => false, 'message' => 'Title and body are required fields.'];
        }

        $visibleToRolesJson = json_encode($visibleToRoles);
        $attachmentsJson = !empty($attachments) ? json_encode($attachments) : null;

        try {
            $this->db->beginTransaction();

            $annId = $this->adminModel->createAnnouncement(
                $postedBy, $postedByRole, $title, $body, $priority, $jobId, $visibleToRolesJson, $publishAt, $expiresAt, $status, $attachmentsJson
            );

            // Add targets
            foreach ($targets as $target) {
                if (!empty($target['type'])) {
                    $this->adminModel->addAnnouncementTarget($annId, $target['type'], $target['value'] ?? null);
                }
            }

            $this->db->commit();
            Logger::info('admin', "Announcement created", ['announcement_id' => $annId, 'title' => $title]);

            // Email Notifications
            if ($sendEmail && in_array($priority, ['important', 'urgent'])) {
                // Determine recipients based on roles (Simplified: sending to all active users in those roles)
                // Real implementation would filter by branch/year targets.
                // Note: We use our BackgroundJob to actually send emails so it doesn't block.
                $stmt = $this->db->prepare("
                    SELECT email FROM users u 
                    JOIN roles r ON r.role_id = u.role_id
                    WHERE JSON_CONTAINS(?, JSON_QUOTE(r.name)) AND u.is_active = 1
                ");
                $stmt->execute([$visibleToRolesJson]);
                $emails = $stmt->fetchAll(\PDO::FETCH_COLUMN);

                if (!empty($emails)) {
                    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                    
                    try {
                        $mail->isSMTP();
                        $mail->Host       = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
                        $mail->SMTPAuth   = true;
                        $mail->Username   = $_ENV['SMTP_USER'] ?? $_ENV['SMTP_USERNAME'] ?? '';
                        $pass             = $_ENV['SMTP_PASS'] ?? $_ENV['SMTP_PASSWORD'] ?? '';
                        $mail->Password   = str_replace([' ', '"', "'"], '', $pass);
                        $mail->SMTPSecure = 'tls';
                        $mail->Port       = (int) ($_ENV['SMTP_PORT'] ?? 587);
                        $mail->Timeout    = 10;
                        $mail->SMTPKeepAlive = false;

                        $mail->setFrom(
                            $_ENV['SMTP_FROM_EMAIL'] ?? 'noreply@iiitmanipur.ac.in',
                            $_ENV['SMTP_FROM_NAME'] ?? 'TNP Cell'
                        );
                        $mail->isHTML(true);
                        $mail->Subject = 'TNP Announcement: ' . $title;
                        $mail->Body    = "
                            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px;'>
                                <h2 style='color: #8B2A8B; margin-bottom: 16px; border-bottom: 2px solid #8b2a8b; padding-bottom: 8px;'>TNP Announcement</h2>
                                <h3 style='margin: 0 0 16px;'>{$title}</h3>
                                <p style='color: #4b5563; line-height: 1.6; white-space: pre-wrap;'>" . htmlspecialchars($body) . "</p>
                                <p style='margin-top: 24px; font-size: 0.875rem; color: #9ca3af;'>Log in to the TNP Portal for more details or to view any attached documents.</p>
                            </div>
                        ";

                        foreach ($emails as $email) {
                            $mail->addAddress($email);
                            $mail->send();
                            $mail->clearAddresses(); // clear for next iteration
                        }
                    } catch (\Exception $e) {
                        Logger::error('admin', 'Failed to send announcement emails', ['error' => $e->getMessage()]);
                    }
                }
            }

            return ['success' => true, 'message' => 'Announcement posted successfully.', 'data' => ['announcement_id' => $annId]];
        } catch (\PDOException $e) {
            $this->db->rollBack();
            Logger::error('admin', "Failed creating announcement", ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Failed to create announcement.'];
        }
    }

    public function listPublicAnnouncements(): array
    {
        $announcements = $this->adminModel->getPublicAnnouncements();
        foreach ($announcements as &$ann) {
            $ann['attachments'] = json_decode($ann['attachments_json'] ?? '[]', true) ?: [];
            unset($ann['attachments_json']);
        }
        return ['success' => true, 'data' => $announcements];
    }

    public function listAnnouncementsForUser(string $userId, string $role): array
    {
        $announcements = $this->adminModel->getAnnouncementsForUser($userId, $role);
        foreach ($announcements as &$ann) {
            $ann['attachments'] = json_decode($ann['attachments_json'] ?? '[]', true) ?: [];
            $ann['is_read'] = (bool) ($ann['is_read'] ?? false);
            unset($ann['attachments_json']);
        }
        return ['success' => true, 'data' => $announcements];
    }

    public function markAnnouncementAsRead(string $userId, string $annId): array
    {
        $this->adminModel->markAnnouncementAsRead($userId, $annId);
        return ['success' => true, 'message' => 'Marked as read'];
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
