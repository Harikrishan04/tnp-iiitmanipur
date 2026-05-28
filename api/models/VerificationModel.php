<?php
/**
 * VerificationModel — Data access layer for verifications and verification logs.
 */

declare(strict_types=1);

namespace App\Models;

use PDO;

class VerificationModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Find verification record by entity ID and entity type.
     */
    public function findByEntity(string $entityId, string $entityType): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM verifications WHERE entity_id = ? AND entity_type = ?"
        );
        $stmt->execute([$entityId, $entityType]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Find verification by its verification_id.
     */
    public function findById(string $verificationId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM verifications WHERE verification_id = ?"
        );
        $stmt->execute([$verificationId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Create or update verification record for an entity.
     */
    public function upsert(string $entityId, string $entityType, string $status, ?string $assignedCoordinatorId = null): bool
    {
        $existing = $this->findByEntity($entityId, $entityType);

        if ($existing) {
            $stmt = $this->db->prepare(
                "UPDATE verifications
                 SET status = ?, assigned_coordinator_id = COALESCE(?, assigned_coordinator_id)
                 WHERE entity_id = ? AND entity_type = ?"
            );
            $stmt->execute([$status, $assignedCoordinatorId, $entityId, $entityType]);
            return $stmt->rowCount() > 0;
        } else {
            $stmt = $this->db->prepare(
                "INSERT INTO verifications (entity_id, entity_type, status, assigned_coordinator_id)
                 VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([$entityId, $entityType, $status, $assignedCoordinatorId]);
            return $this->db->lastInsertId() !== false || $stmt->rowCount() > 0;
        }
    }

    /**
     * List verifications with optional filters and pagination.
     */
    public function findAll(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['entity_type'])) {
            $where[]  = 'v.entity_type = ?';
            $params[] = $filters['entity_type'];
        }

        if (!empty($filters['status'])) {
            $where[]  = 'v.status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['assigned_coordinator_id'])) {
            $where[]  = 'v.assigned_coordinator_id = ?';
            $params[] = $filters['assigned_coordinator_id'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Select with joins depending on the entity type
        $sql = "SELECT v.verification_id, v.entity_id, v.entity_type, v.status,
                       v.assigned_coordinator_id, v.verified_by, v.verified_at,
                       v.remark, v.created_at, v.updated_at,
                       c.name AS coordinator_name,
                       -- Entity specific info
                       CASE v.entity_type
                           WHEN 'student' THEN s.name
                           WHEN 'recruiter' THEN JSON_UNQUOTE(JSON_EXTRACT(r.company_details_json, '$.name'))
                           WHEN 'job' THEN j.title
                       END AS entity_name,
                       CASE v.entity_type
                           WHEN 'student' THEN s.roll_no
                           WHEN 'recruiter' THEN r.primary_name
                           WHEN 'job' THEN JSON_UNQUOTE(JSON_EXTRACT(rj.company_details_json, '$.name'))
                       END AS entity_subtitle,
                       CASE v.entity_type
                           WHEN 'student' THEN u.email
                           WHEN 'recruiter' THEN u.email
                           WHEN 'job' THEN rj_u.email
                       END AS entity_email
                FROM verifications v
                LEFT JOIN coordinators c ON c.coordinator_id = v.assigned_coordinator_id
                -- Joins for student details
                LEFT JOIN students s ON s.student_id = v.entity_id AND v.entity_type = 'student'
                LEFT JOIN users u ON u.user_id = v.entity_id AND v.entity_type IN ('student', 'recruiter')
                -- Joins for recruiter details
                LEFT JOIN recruiters r ON r.recruiter_id = v.entity_id AND v.entity_type = 'recruiter'
                -- Joins for job details
                LEFT JOIN jobs j ON j.job_id = v.entity_id AND v.entity_type = 'job'
                LEFT JOIN recruiters rj ON rj.recruiter_id = j.recruiter_id
                LEFT JOIN users rj_u ON rj_u.user_id = rj.recruiter_id
                {$whereClause}
                ORDER BY v.updated_at DESC
                LIMIT ? OFFSET ?";

        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Count verifications matching filters.
     */
    public function countAll(array $filters = []): int
    {
        $where  = [];
        $params = [];

        if (!empty($filters['entity_type'])) {
            $where[]  = 'entity_type = ?';
            $params[] = $filters['entity_type'];
        }

        if (!empty($filters['status'])) {
            $where[]  = 'status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['assigned_coordinator_id'])) {
            $where[]  = 'assigned_coordinator_id = ?';
            $params[] = $filters['assigned_coordinator_id'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total FROM verifications {$whereClause}"
        );
        $stmt->execute($params);
        return (int) $stmt->fetch()['total'];
    }

    /**
     * Update verification status and optional details.
     */
    public function updateStatus(string $verificationId, string $status, ?string $verifiedBy, ?string $remark): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE verifications
             SET status = ?, verified_by = ?, verified_at = NOW(), remark = ?
             WHERE verification_id = ?"
        );
        $stmt->execute([$status, $verifiedBy, $remark, $verificationId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Assign coordinator to a verification.
     */
    public function assignCoordinator(string $verificationId, ?string $coordinatorId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE verifications
             SET assigned_coordinator_id = ?
             WHERE verification_id = ?"
        );
        $stmt->execute([$coordinatorId, $verificationId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Get logs for a verification record.
     */
    public function getLogs(string $verificationId): array
    {
        $stmt = $this->db->prepare(
            "SELECT vl.*, u.email AS changed_by_email
             FROM verification_logs vl
             LEFT JOIN users u ON u.user_id = vl.changed_by
             WHERE vl.verification_id = ?
             ORDER BY vl.changed_at DESC"
        );
        $stmt->execute([$verificationId]);
        return $stmt->fetchAll();
    }
}
