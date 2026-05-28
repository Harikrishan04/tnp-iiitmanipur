<?php
/**
 * VerificationService — Business logic for student, recruiter, and job verifications.
 */

declare(strict_types=1);

namespace App\Services;

use App\Config\Database;
use App\Helpers\Logger;
use App\Models\VerificationModel;
use PDO;

class VerificationService
{
    private PDO $db;
    private VerificationModel $verificationModel;

    public function __construct()
    {
        $this->db                = Database::getInstance();
        $this->verificationModel = new VerificationModel($this->db);
    }

    /**
     * Get list of verifications with filters and pagination.
     */
    public function getVerifications(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $list   = $this->verificationModel->findAll($filters, $perPage, $offset);
        $total  = $this->verificationModel->countAll($filters);

        return [
            'success' => true,
            'data'    => $list,
            'meta'    => [
                'page'        => $page,
                'per_page'    => $perPage,
                'total'       => $total,
                'total_pages' => (int) ceil($total / max($perPage, 1)),
            ],
        ];
    }

    /**
     * Get a specific verification record details including audit logs and entity info.
     */
    public function getVerification(string $id): array
    {
        $verification = $this->verificationModel->findById($id);
        if (!$verification) {
            return ['success' => false, 'message' => 'Verification record not found.'];
        }

        // Fetch logs
        $logs = $this->verificationModel->getLogs($id);

        // Fetch complete entity info
        $entityDetails = null;
        $entityId      = $verification['entity_id'];
        $entityType    = $verification['entity_type'];

        if ($entityType === 'student') {
            $stmt = $this->db->prepare(
                "SELECT s.*, u.email, u.phone AS user_phone,
                        d.name AS department_name, p.name AS program_name
                 FROM students s
                 JOIN users u ON u.user_id = s.student_id
                 LEFT JOIN departments d ON d.dept_id = s.dept_id
                 LEFT JOIN programs p ON p.program_id = s.program_id
                 WHERE s.student_id = ?"
            );
            $stmt->execute([$entityId]);
            $entityDetails = $stmt->fetch() ?: null;
        } elseif ($entityType === 'recruiter') {
            $stmt = $this->db->prepare(
                "SELECT r.*, u.email
                 FROM recruiters r
                 JOIN users u ON u.user_id = r.recruiter_id
                 WHERE r.recruiter_id = ?"
            );
            $stmt->execute([$entityId]);
            $entityDetails = $stmt->fetch() ?: null;
        } elseif ($entityType === 'job') {
            $stmt = $this->db->prepare(
                "SELECT j.*, jt.name AS job_type_label,
                        JSON_UNQUOTE(JSON_EXTRACT(r.company_details_json, '$.name')) AS company_name,
                        JSON_UNQUOTE(JSON_EXTRACT(r.company_details_json, '$.logo_url')) AS company_logo_url
                 FROM jobs j
                 JOIN recruiters r ON r.recruiter_id = j.recruiter_id
                 LEFT JOIN job_types jt ON jt.job_type_id = j.job_type_id
                 WHERE j.job_id = ?"
            );
            $stmt->execute([$entityId]);
            $entityDetails = $stmt->fetch() ?: null;
        }

        return [
            'success' => true,
            'data'    => [
                'verification'   => $verification,
                'logs'           => $logs,
                'entity_details' => $entityDetails,
            ],
        ];
    }

    /**
     * Update verification status.
     * Keeps jobs/students status in sync with verification status.
     */
    public function updateStatus(string $verificationId, string $status, string $verifiedBy, ?string $remark): array
    {
        $verification = $this->verificationModel->findById($verificationId);
        if (!$verification) {
            return ['success' => false, 'message' => 'Verification record not found.'];
        }

        $validStatuses = ['pending', 'under_review', 'verified', 'resubmit', 'rejected'];
        if (!in_array($status, $validStatuses, true)) {
            return ['success' => false, 'message' => 'Invalid status value.'];
        }

        $entityId   = $verification['entity_id'];
        $entityType = $verification['entity_type'];

        try {
            $this->db->beginTransaction();

            // 1. Update verification record
            $this->verificationModel->updateStatus($verificationId, $status, $verifiedBy, $remark);

            // 2. Sync status to target entity
            if ($entityType === 'job') {
                $jobStatus = 'pending';
                if ($status === 'verified') {
                    $jobStatus = 'verified';
                } elseif ($status === 'rejected') {
                    $jobStatus = 'cancelled';
                } elseif ($status === 'resubmit') {
                    $jobStatus = 'draft';
                }

                $stmt = $this->db->prepare(
                    "UPDATE jobs SET job_status = ? WHERE job_id = ?"
                );
                $stmt->execute([$jobStatus, $entityId]);
            }

            $this->db->commit();

            Logger::info('verification', "Verification status updated", [
                'verification_id' => $verificationId,
                'entity_id'       => $entityId,
                'entity_type'     => $entityType,
                'status'          => $status,
                'verified_by'     => $verifiedBy,
            ]);

            return ['success' => true, 'message' => 'Verification status updated successfully.'];
        } catch (\PDOException $e) {
            $this->db->rollBack();
            Logger::error('verification', "Failed updating verification status", ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Failed to update verification status.'];
        }
    }

    /**
     * Assign coordinator to verification task.
     */
    public function assignCoordinator(string $verificationId, ?string $coordinatorId): array
    {
        $verification = $this->verificationModel->findById($verificationId);
        if (!$verification) {
            return ['success' => false, 'message' => 'Verification record not found.'];
        }

        if ($verification['assigned_coordinator_id'] === $coordinatorId) {
            return ['success' => true, 'message' => 'Coordinator already assigned.'];
        }

        $updated = $this->verificationModel->assignCoordinator($verificationId, $coordinatorId);

        if ($updated) {
            Logger::info('verification', "Verification assigned", [
                'verification_id' => $verificationId,
                'coordinator_id'  => $coordinatorId,
            ]);
            return ['success' => true, 'message' => 'Coordinator assigned successfully.'];
        }

        return ['success' => false, 'message' => 'Failed to assign coordinator.'];
    }
}
