<?php
/**
 * ApplicationModel — Data access layer for the `applications` table.
 *
 * IMPORTANT — Actual column notes (verified 2026-05-26):
 *   - applications.resume_url (NOT NULL) — must be supplied on insert
 *   - applications.eligibility_snapshot (JSON NOT NULL) — must be supplied on insert
 *   - applications.status enum: applied|shortlisted|in_process|selected|
 *                               not_selected|withdrawn|offer_accepted|offer_declined
 *   - No company_name column on recruiters — use company_details_json JSON extract
 *
 * Methods:
 *   create(studentId, jobId, resumeUrl, snapshot) → Insert application
 *   findByStudent(studentId, limit, offset)        → Student's applications
 *   countByStudent(studentId)                      → Count
 *   hasApplied(studentId, jobId)                   → Duplicate check
 *   withdraw(applicationId, studentId)             → Withdraw
 */

declare(strict_types=1);

namespace App\Models;

use PDO;

class ApplicationModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Create a new application.
     *
     * resume_url and eligibility_snapshot are NOT NULL in the schema.
     * eligibility_snapshot captures the student's CPI/dept at time of apply.
     *
     * @param string $studentId          UUID
     * @param string $jobId              UUID
     * @param string $resumeUrl          Student's current resume URL (empty string if none uploaded yet)
     * @param array  $eligibilitySnapshot Key stats at time of application
     * @return string The new application_id
     */
    public function create(
        string $studentId,
        string $jobId,
        string $resumeUrl = '',
        array $eligibilitySnapshot = []
    ): string {
        $snapshotJson = json_encode($eligibilitySnapshot ?: new \stdClass());

        $stmt = $this->db->prepare(
            "INSERT INTO applications
                (student_id, job_id, resume_url, eligibility_snapshot, status)
             VALUES (?, ?, ?, ?, 'applied')"
        );
        $stmt->execute([$studentId, $jobId, $resumeUrl, $snapshotJson]);

        // Retrieve the auto-generated UUID application_id
        $stmt = $this->db->prepare(
            "SELECT application_id FROM applications
             WHERE student_id = ? AND job_id = ?
             ORDER BY applied_at DESC LIMIT 1"
        );
        $stmt->execute([$studentId, $jobId]);
        return $stmt->fetch()['application_id'];
    }

    /**
     * Check if a student has already applied to a job (excluding withdrawn).
     *
     * @return bool
     */
    public function hasApplied(string $studentId, string $jobId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM applications
             WHERE student_id = ? AND job_id = ? AND status != 'withdrawn'
             LIMIT 1"
        );
        $stmt->execute([$studentId, $jobId]);
        return (bool) $stmt->fetch();
    }

    /**
     * Get all applications for a student with job + company details.
     *
     * Company name is extracted from the recruiters.company_details_json column.
     *
     * @param string $studentId UUID
     * @param int    $limit
     * @param int    $offset
     * @return array
     */
    public function findByStudent(string $studentId, int $limit = 20, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                a.application_id, a.status, a.applied_at,
                a.is_shortlisted, a.shortlisted_at, a.offer_accepted_at,
                a.resume_url,
                j.job_id, j.title, j.ctc_lpa, j.location, j.job_status,
                jt.name AS job_type_label,
                JSON_UNQUOTE(JSON_EXTRACT(r.company_details_json, '$.name'))     AS company_name,
                JSON_UNQUOTE(JSON_EXTRACT(r.company_details_json, '$.logo_url')) AS company_logo_url
             FROM applications a
             JOIN jobs j ON j.job_id = a.job_id
             JOIN recruiters r ON r.recruiter_id = j.recruiter_id
             LEFT JOIN job_types jt ON jt.job_type_id = j.job_type_id
             WHERE a.student_id = ?
             ORDER BY a.applied_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$studentId, $limit, $offset]);
        return $stmt->fetchAll();
    }

    /**
     * Count total applications for a student.
     *
     * @param string $studentId
     * @return int
     */
    public function countByStudent(string $studentId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total FROM applications WHERE student_id = ?"
        );
        $stmt->execute([$studentId]);
        return (int) $stmt->fetch()['total'];
    }

    /**
     * Withdraw an application. Only the owning student can withdraw from 'applied' status.
     *
     * @param string $applicationId
     * @param string $studentId
     * @return bool True if withdrawn successfully
     */
    public function withdraw(string $applicationId, string $studentId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE applications
             SET status = 'withdrawn',
                 withdrawn_at = NOW(),
                 withdrawal_reason = 'other'
             WHERE application_id = ?
               AND student_id = ?
               AND status = 'applied'"
        );
        $stmt->execute([$applicationId, $studentId]);
        return $stmt->rowCount() > 0;
    }
}
