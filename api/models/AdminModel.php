<?php
/**
 * AdminModel — Database operations for placement sessions, announcements, statistics, and user management.
 */

declare(strict_types=1);

namespace App\Models;

use PDO;

class AdminModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // ═══ PLACEMENT SESSIONS ═══

    public function getSessions(): array
    {
        $stmt = $this->db->query("SELECT * FROM placement_sessions ORDER BY start_date DESC");
        return $stmt->fetchAll();
    }

    public function findSessionById(string $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM placement_sessions WHERE session_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function createSession(string $label, string $startDate, string $endDate, ?string $createdBy): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO placement_sessions (label, start_date, end_date, created_by)
             VALUES (?, ?, ?, ?)"
        );
        return $stmt->execute([$label, $startDate, $endDate, $createdBy]);
    }

    public function activateSession(string $sessionId): bool
    {
        // 1. Deactivate all sessions
        $this->db->query("UPDATE placement_sessions SET is_active = FALSE");
        
        // 2. Activate specific session
        $stmt = $this->db->prepare("UPDATE placement_sessions SET is_active = TRUE WHERE session_id = ?");
        return $stmt->execute([$sessionId]) && $stmt->rowCount() > 0;
    }

    // ═══ ANNOUNCEMENTS ═══

    public function getAnnouncements(): array
    {
        $stmt = $this->db->query(
            "SELECT a.*, u.email AS posted_by_email,
             (SELECT COUNT(*) FROM announcement_reads r WHERE r.announcement_id = a.announcement_id) AS read_count
             FROM announcements a
             JOIN users u ON u.user_id = a.posted_by
             ORDER BY a.created_at DESC"
        );
        return $stmt->fetchAll();
    }

    public function getPublicAnnouncements(): array
    {
        $stmt = $this->db->query(
            "SELECT a.announcement_id, a.title, a.body, a.priority, a.publish_at, a.attachments_json
             FROM announcements a
             WHERE a.status = 'published' AND a.publish_at <= NOW()
             ORDER BY a.created_at DESC"
        );
        return $stmt->fetchAll();
    }

    public function getAnnouncementsForUser(string $userId, string $role): array
    {
        $sql = "SELECT a.*, 
                 IF(r.read_id IS NOT NULL, 1, 0) AS is_read
                 FROM announcements a
                 LEFT JOIN announcement_reads r ON r.announcement_id = a.announcement_id AND r.user_id = ?
                 WHERE a.status = 'published' AND a.publish_at <= NOW()
                   AND JSON_CONTAINS(a.visible_to_roles_json, ?)
                 ORDER BY a.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, json_encode($role)]);
        return $stmt->fetchAll();
    }

    public function markAnnouncementAsRead(string $userId, string $annId): bool
    {
        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO announcement_reads (announcement_id, user_id) VALUES (?, ?)"
        );
        return $stmt->execute([$annId, $userId]);
    }

    public function createAnnouncement(
        string $postedBy,
        string $postedByRole,
        string $title,
        string $body,
        ?string $priority,
        ?string $jobId,
        string $visibleToRolesJson,
        ?string $publishAt,
        ?string $expiresAt,
        string $status,
        ?string $attachmentsJson = null
    ): string {
        $id = $this->db->query("SELECT UUID()")->fetchColumn();
        $pub = $publishAt ?: date('Y-m-d H:i:s');
        $pri = $priority ?: 'normal';

        $stmt = $this->db->prepare(
            "INSERT INTO announcements (announcement_id, posted_by, posted_by_role, title, body, priority, job_id, visible_to_roles_json, publish_at, expires_at, status, attachments_json)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $id, $postedBy, $postedByRole, $title, $body, $pri, $jobId, $visibleToRolesJson, $pub, $expiresAt, $status, $attachmentsJson
        ]);
        return $id;
    }

    public function addAnnouncementTarget(string $announcementId, string $targetType, ?string $targetValue): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO announcement_targets (announcement_id, target_type, target_value)
             VALUES (?, ?, ?)"
        );
        return $stmt->execute([$announcementId, $targetType, $targetValue]);
    }

    public function getAnnouncementTargets(string $announcementId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM announcement_targets WHERE announcement_id = ?");
        $stmt->execute([$announcementId]);
        return $stmt->fetchAll();
    }

    public function deleteAnnouncement(string $id): bool
    {
        // Targets deleted automatically due to ON DELETE CASCADE
        $stmt = $this->db->prepare("DELETE FROM announcements WHERE announcement_id = ?");
        return $stmt->execute([$id]) && $stmt->rowCount() > 0;
    }

    // ═══ STATISTICS ═══

    public function getOverviewStats(): array
    {
        // 1. Total placed students
        $placedCount = $this->db->query(
            "SELECT COUNT(DISTINCT student_id) FROM placements WHERE offer_status = 'accepted'"
        )->fetchColumn();

        // 2. Total active students (profile verified)
        $activeStudents = $this->db->query(
            "SELECT COUNT(*) FROM students s
             JOIN verifications v ON v.entity_id = s.student_id AND v.entity_type = 'student'
             WHERE v.status = 'verified'"
        )->fetchColumn();

        // 3. Active jobs count
        $activeJobs = $this->db->query(
            "SELECT COUNT(*) FROM jobs WHERE job_status = 'opened'"
        )->fetchColumn();

        // 4. CTC metrics
        $ctcMetrics = $this->db->query(
            "SELECT MAX(actual_ctc_lpa) AS max_ctc, AVG(actual_ctc_lpa) AS avg_ctc 
             FROM placements WHERE offer_status = 'accepted'"
        )->fetch();

        // 5. Placements by branch/department
        $branchStats = $this->db->query(
            "SELECT d.code AS branch, COUNT(p.placement_id) AS placed
             FROM departments d
             LEFT JOIN students s ON s.dept_id = d.dept_id
             LEFT JOIN placements p ON p.student_id = s.student_id AND p.offer_status = 'accepted'
             GROUP BY d.dept_id"
        )->fetchAll();

        return [
            'placed_count' => (int) $placedCount,
            'active_students' => (int) $activeStudents,
            'active_jobs' => (int) $activeJobs,
            'max_ctc' => $ctcMetrics['max_ctc'] ? (float) $ctcMetrics['max_ctc'] : 0.0,
            'avg_ctc' => $ctcMetrics['avg_ctc'] ? (float) $ctcMetrics['avg_ctc'] : 0.0,
            'branch_stats' => $branchStats
        ];
    }

    // ═══ USER MANAGEMENT ═══

    public function listUsers(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['role'])) {
            $where[] = "r.name = ?";
            $params[] = $filters['role'];
        }

        if (isset($filters['is_active'])) {
            $where[] = "u.is_active = ?";
            $params[] = $filters['is_active'] ? 1 : 0;
        }

        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        $sql = "SELECT u.user_id, u.email, u.phone, u.is_active, u.created_at, r.name AS role_name 
                FROM users u
                JOIN roles r ON r.role_id = u.role_id
                {$whereClause}
                ORDER BY u.created_at DESC
                LIMIT ? OFFSET ?";

        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function countUsers(array $filters = []): int
    {
        $where = [];
        $params = [];

        if (!empty($filters['role'])) {
            $where[] = "r.name = ?";
            $params[] = $filters['role'];
        }

        if (isset($filters['is_active'])) {
            $where[] = "u.is_active = ?";
            $params[] = $filters['is_active'] ? 1 : 0;
        }

        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total 
             FROM users u
             JOIN roles r ON r.role_id = u.role_id
             {$whereClause}"
        );
        $stmt->execute($params);
        return (int) $stmt->fetch()['total'];
    }

    public function updateUserStatus(string $userId, bool $isActive): bool
    {
        $stmt = $this->db->prepare("UPDATE users SET is_active = ? WHERE user_id = ?");
        return $stmt->execute([$isActive ? 1 : 0, $userId]);
    }

    public function createCoordinatorAccount(
        string $email,
        ?string $phone,
        string $name,
        string $deptCode,
        string $designation,
        string $team,
        string $coordType,
        ?string $adminId
    ): ?string {
        $stmt = $this->db->prepare("CALL CreateCoordinatorAccount(?, ?, ?, ?, ?, ?, ?, ?, @new_user_id)");
        $stmt->execute([
            $email,
            $phone,
            $name,
            $deptCode,
            $designation,
            $team,
            $coordType,
            $adminId
        ]);
        
        $result = $this->db->query("SELECT @new_user_id AS new_id")->fetch();
        return $result['new_id'] ?? null;
    }
}
