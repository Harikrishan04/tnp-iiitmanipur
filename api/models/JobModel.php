<?php
/**
 * JobModel — Data access layer for the `jobs` table.
 *
 * IMPORTANT — Actual column notes (verified 2026-05-26):
 *   - job_status enum: 'draft'|'pending'|'verified'|'opened'|'closed'|'cancelled'
 *   - No company_name column — extract from recruiters.company_details_json
 *   - Eligibility uses: min_cpi, allowed_branches_json, allowed_programs_json (JSON arrays)
 *   - applications_count is a denormalized counter column on the jobs table
 *
 * Methods:
 *   findById(jobId)             → Full job detail with recruiter info + rounds
 *   findEligible(student, ..)   → Jobs open for a student's dept + CPI
 *   countEligible(student)      → Count eligible jobs
 *   findAll(filters, ..)        → All jobs for admin/coordinator
 */

declare(strict_types=1);

namespace App\Models;

use PDO;

class JobModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Find a job by ID with recruiter company info and session label.
     *
     * Company name/logo are stored as JSON in recruiters.company_details_json.
     * We use JSON_UNQUOTE + JSON_EXTRACT to pull them into the result row.
     *
     * @param string $jobId UUID
     * @return array|null
     */
    public function findById(string $jobId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT
                j.job_id, j.title, j.description, j.location,
                j.ctc_lpa, j.stipend_pm, j.salary_type,
                j.min_cpi, j.allowed_year_of_passing,
                j.allowed_branches_json, j.allowed_programs_json,
                j.apply_start, j.apply_end, j.max_participants,
                j.applications_count, j.job_status,
                j.documents_json, j.created_at,
                ps.label AS session_label,
                jt.name AS job_type_label,
                JSON_UNQUOTE(JSON_EXTRACT(r.company_details_json, '$.name'))     AS company_name,
                JSON_UNQUOTE(JSON_EXTRACT(r.company_details_json, '$.logo_url')) AS company_logo_url,
                JSON_UNQUOTE(JSON_EXTRACT(r.company_details_json, '$.website'))  AS company_website,
                r.primary_name AS contact_name,
                r.primary_position AS contact_position
             FROM jobs j
             JOIN recruiters r ON r.recruiter_id = j.recruiter_id
             JOIN placement_sessions ps ON ps.session_id = j.session_id
             LEFT JOIN job_types jt ON jt.job_type_id = j.job_type_id
             WHERE j.job_id = ?"
        );
        $stmt->execute([$jobId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Find jobs eligible for a student.
     *
     * Eligibility logic:
     *   1. Session must be active
     *   2. job_status = 'opened' (the correct enum value for public/open)
     *   3. Apply window open (apply_start <= NOW <= apply_end, or nulls = open)
     *   4. min_cpi: student.cpi >= job.min_cpi (or job has no CPI requirement)
     *   5. allowed_branches_json: job targets student's dept, or no restriction (null)
     *   6. allowed_programs_json: job targets student's program, or no restriction (null)
     *
     * @param array $student Student row (needs: cpi, dept_id, program_id)
     * @param int   $limit
     * @param int   $offset
     * @return array
     */
    public function findEligible(array $student, int $limit = 20, int $offset = 0): array
    {
        // JSON_CONTAINS checks if the student's dept_id/program_id is in the allowed JSON arrays
        $stmt = $this->db->prepare(
            "SELECT
                j.job_id, j.title, j.location,
                j.ctc_lpa, j.stipend_pm, j.salary_type,
                j.apply_start, j.apply_end, j.min_cpi,
                j.applications_count, j.job_status,
                jt.name AS job_type_label,
                JSON_UNQUOTE(JSON_EXTRACT(r.company_details_json, '$.name'))     AS company_name,
                JSON_UNQUOTE(JSON_EXTRACT(r.company_details_json, '$.logo_url')) AS company_logo_url
             FROM jobs j
             JOIN recruiters r ON r.recruiter_id = j.recruiter_id
             JOIN placement_sessions ps ON ps.session_id = j.session_id
             LEFT JOIN job_types jt ON jt.job_type_id = j.job_type_id
             WHERE ps.is_active = TRUE
               AND j.job_status = 'opened'
               AND (j.apply_start IS NULL OR j.apply_start <= NOW())
               AND (j.apply_end   IS NULL OR j.apply_end   >= NOW())
               AND (j.min_cpi IS NULL OR ? IS NULL OR j.min_cpi <= ?)
               AND (j.allowed_branches_json IS NULL
                    OR ? IS NULL
                    OR JSON_CONTAINS(j.allowed_branches_json, JSON_QUOTE(?)))
               AND (j.allowed_programs_json IS NULL
                    OR ? IS NULL
                    OR JSON_CONTAINS(j.allowed_programs_json, JSON_QUOTE(?)))
             ORDER BY j.created_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([
            $student['cpi'], $student['cpi'],
            $student['dept_id'], $student['dept_id'],
            $student['program_id'], $student['program_id'],
            $limit, $offset,
        ]);
        return $stmt->fetchAll();
    }

    /**
     * Count eligible jobs for a student.
     *
     * @param array $student
     * @return int
     */
    public function countEligible(array $student): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total
             FROM jobs j
             JOIN placement_sessions ps ON ps.session_id = j.session_id
             WHERE ps.is_active = TRUE
               AND j.job_status = 'opened'
               AND (j.apply_start IS NULL OR j.apply_start <= NOW())
               AND (j.apply_end   IS NULL OR j.apply_end   >= NOW())
               AND (j.min_cpi IS NULL OR ? IS NULL OR j.min_cpi <= ?)
               AND (j.allowed_branches_json IS NULL
                    OR ? IS NULL
                    OR JSON_CONTAINS(j.allowed_branches_json, JSON_QUOTE(?)))
               AND (j.allowed_programs_json IS NULL
                    OR ? IS NULL
                    OR JSON_CONTAINS(j.allowed_programs_json, JSON_QUOTE(?)))"
        );
        $stmt->execute([
            $student['cpi'], $student['cpi'],
            $student['dept_id'], $student['dept_id'],
            $student['program_id'], $student['program_id'],
        ]);
        return (int) $stmt->fetch()['total'];
    }

    /**
     * List all jobs with optional filters (for coordinators/admins).
     *
     * @param array $filters  Optional: session_id, job_status, recruiter_id, search
     * @param int   $limit
     * @param int   $offset
     * @return array
     */
    public function findAll(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['session_id'])) {
            $where[]  = 'j.session_id = ?';
            $params[] = $filters['session_id'];
        }
        if (!empty($filters['job_status'])) {
            $where[]  = 'j.job_status = ?';
            $params[] = $filters['job_status'];
        }
        if (!empty($filters['recruiter_id'])) {
            $where[]  = 'j.recruiter_id = ?';
            $params[] = $filters['recruiter_id'];
        }
        if (!empty($filters['search'])) {
            $where[]  = "(j.title LIKE ? OR JSON_UNQUOTE(JSON_EXTRACT(r.company_details_json, '$.name')) LIKE ?)";
            $term     = '%' . $filters['search'] . '%';
            $params[] = $term;
            $params[] = $term;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $params[]    = $limit;
        $params[]    = $offset;

        $stmt = $this->db->prepare(
            "SELECT
                j.job_id, j.title, j.job_status, j.ctc_lpa,
                j.apply_start, j.apply_end, j.applications_count,
                jt.name AS job_type_label,
                ps.label AS session_label,
                JSON_UNQUOTE(JSON_EXTRACT(r.company_details_json, '$.name'))     AS company_name,
                JSON_UNQUOTE(JSON_EXTRACT(r.company_details_json, '$.logo_url')) AS company_logo_url
             FROM jobs j
             JOIN recruiters r ON r.recruiter_id = j.recruiter_id
             JOIN placement_sessions ps ON ps.session_id = j.session_id
             LEFT JOIN job_types jt ON jt.job_type_id = j.job_type_id
             {$whereClause}
             ORDER BY j.created_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Create a new job posting.
     *
     * Starts in 'draft' status — recruiter can edit before submitting for coordinator review.
     *
     * @param string $recruiterId   UUID
     * @param string $sessionId     UUID — must be an active session
     * @param array  $data          Job fields (title, description, ctc_lpa, etc.)
     * @return string New job_id
     */
    public function create(string $recruiterId, string $sessionId, array $data): string
    {
        $allowed = [
            'job_type_id', 'title', 'description', 'location',
            'ctc_lpa', 'stipend_pm', 'salary_type',
            'min_cpi', 'allowed_year_of_passing',
            'allowed_branches_json', 'allowed_programs_json',
            'apply_start', 'apply_end', 'max_participants',
        ];

        $columns = ['recruiter_id', 'session_id', 'job_status'];
        $values  = [$recruiterId, $sessionId, 'draft'];

        foreach ($data as $col => $val) {
            if (in_array($col, $allowed, true) && $val !== null) {
                $columns[] = $col;
                $values[]  = $val;
            }
        }

        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        $colStr       = implode(', ', array_map(fn($c) => "`{$c}`", $columns));

        $stmt = $this->db->prepare("INSERT INTO jobs ({$colStr}) VALUES ({$placeholders})");
        $stmt->execute($values);

        // Retrieve auto-generated UUID
        $stmt = $this->db->prepare(
            "SELECT job_id FROM jobs WHERE recruiter_id = ? AND session_id = ? ORDER BY created_at DESC LIMIT 1"
        );
        $stmt->execute([$recruiterId, $sessionId]);
        return $stmt->fetch()['job_id'];
    }

    /**
     * Update an existing job. Only the owning recruiter can update, and only if in draft/pending.
     *
     * @param string $jobId
     * @param string $recruiterId
     * @param array  $data
     * @return bool
     */
    public function update(string $jobId, string $recruiterId, array $data): bool
    {
        $allowed = [
            'job_type_id', 'title', 'description', 'location',
            'ctc_lpa', 'stipend_pm', 'salary_type',
            'min_cpi', 'allowed_year_of_passing',
            'allowed_branches_json', 'allowed_programs_json',
            'apply_start', 'apply_end', 'max_participants',
        ];

        $setClauses = [];
        $params     = [];

        foreach ($data as $col => $val) {
            if (in_array($col, $allowed, true)) {
                $setClauses[] = "`{$col}` = ?";
                $params[]     = $val;
            }
        }

        if (empty($setClauses)) return false;

        $params[] = $jobId;
        $params[] = $recruiterId;

        $stmt = $this->db->prepare(
            "UPDATE jobs SET " . implode(', ', $setClauses) .
            " WHERE job_id = ? AND recruiter_id = ? AND job_status IN ('draft','pending')"
        );
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    /**
     * Submit a job for coordinator review (draft → pending).
     *
     * @param string $jobId
     * @param string $recruiterId
     * @return bool
     */
    public function submitForReview(string $jobId, string $recruiterId): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE jobs SET job_status = 'pending'
             WHERE job_id = ? AND recruiter_id = ? AND job_status = 'draft'"
        );
        $stmt->execute([$jobId, $recruiterId]);
        $updated = $stmt->rowCount() > 0;

        if ($updated) {
            $stmtVerif = $this->db->prepare(
                "INSERT INTO verifications (entity_id, entity_type, status)
                 VALUES (?, 'job', 'pending')
                 ON DUPLICATE KEY UPDATE status = 'pending'"
            );
            $stmtVerif->execute([$jobId]);
        }

        return $updated;
    }

    /**
     * Get all job types for form dropdowns.
     *
     * @return array
     */
    public function getJobTypes(): array
    {
        $stmt = $this->db->query(
            "SELECT job_type_id, name, code FROM job_types WHERE is_active = 1 ORDER BY name"
        );
        return $stmt->fetchAll();
    }
}

