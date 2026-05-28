<?php
/**
 * RecruiterModel — Data access layer for the `recruiters` table.
 *
 * Schema notes (verified 2026-05-26):
 *   - recruiter_id = user_id (1:1 with users)
 *   - company info lives in company_details_json (JSON column)
 *   - Contact split into primary_ and alt_ prefixed columns
 *   - No separate company table — JSON holds: name, logo_url, website, industry, size, etc.
 *
 * Methods:
 *   findById(recruiterId)       → Get recruiter profile with user info
 *   updateProfile(id, data)     → Update recruiter fields
 *   updateCompanyDetails(id, json) → Replace company_details_json
 *   findAll(filters, pagination)  → List recruiters (coordinator/admin)
 *   countAll(filters)           → Count matching recruiters
 */

declare(strict_types=1);

namespace App\Models;

use PDO;

class RecruiterModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Find a recruiter profile by recruiter_id (= user_id).
     * Joins users table for email and account status.
     *
     * @param string $recruiterId UUID
     * @return array|null
     */
    public function findById(string $recruiterId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT r.*,
                    u.email, u.phone AS user_phone, u.is_active, u.account_activated,
                    u.first_login_at, u.last_login_at
             FROM recruiters r
             JOIN users u ON u.user_id = r.recruiter_id
             WHERE r.recruiter_id = ?"
        );
        $stmt->execute([$recruiterId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Update recruiter contact fields.
     * Only updates whitelisted columns.
     *
     * @param string $recruiterId UUID
     * @param array  $data        Associative array of column => value
     * @return bool
     */
    public function updateProfile(string $recruiterId, array $data): bool
    {
        $allowed = [
            'primary_name', 'primary_position', 'primary_phone', 'primary_linkedin',
            'alt_name', 'alt_position', 'alt_email', 'alt_phone', 'alt_linkedin',
            'remark',
        ];

        $setClauses = [];
        $params     = [];

        foreach ($data as $col => $val) {
            if (in_array($col, $allowed, true)) {
                $setClauses[] = "`{$col}` = ?";
                $params[]     = $val;
            }
        }

        if (empty($setClauses)) {
            return false;
        }

        $params[] = $recruiterId;
        $sql = "UPDATE recruiters SET " . implode(', ', $setClauses) . " WHERE recruiter_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    /**
     * Update the company_details_json column.
     * Merges with existing JSON rather than replacing entirely.
     *
     * @param string $recruiterId UUID
     * @param array  $companyData Associative array (name, logo_url, website, industry, size, about)
     * @return bool
     */
    public function updateCompanyDetails(string $recruiterId, array $companyData): bool
    {
        // Fetch existing JSON first to merge
        $stmt = $this->db->prepare("SELECT company_details_json FROM recruiters WHERE recruiter_id = ?");
        $stmt->execute([$recruiterId]);
        $row      = $stmt->fetch();
        $existing = $row && $row['company_details_json']
            ? json_decode($row['company_details_json'], true)
            : [];

        $merged = array_merge($existing, $companyData);

        $stmt = $this->db->prepare(
            "UPDATE recruiters SET company_details_json = ? WHERE recruiter_id = ?"
        );
        $stmt->execute([json_encode($merged), $recruiterId]);
        return $stmt->rowCount() > 0;
    }

    /**
     * List all recruiters with optional filters (for coordinator/admin).
     *
     * @param array $filters Optional: search (company name / primary_name), account_activated
     * @param int   $limit
     * @param int   $offset
     * @return array
     */
    public function findAll(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $where  = [];
        $params = [];

        if (isset($filters['account_activated'])) {
            $where[]  = 'u.account_activated = ?';
            $params[] = (int) $filters['account_activated'];
        }
        if (!empty($filters['search'])) {
            $where[]  = "(r.primary_name LIKE ? OR JSON_UNQUOTE(JSON_EXTRACT(r.company_details_json, '$.name')) LIKE ? OR u.email LIKE ?)";
            $term     = '%' . $filters['search'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        $params[]    = $limit;
        $params[]    = $offset;

        $stmt = $this->db->prepare(
            "SELECT r.recruiter_id, r.primary_name, r.primary_position,
                    r.profile_completed, r.created_at,
                    JSON_UNQUOTE(JSON_EXTRACT(r.company_details_json, '$.name'))     AS company_name,
                    JSON_UNQUOTE(JSON_EXTRACT(r.company_details_json, '$.logo_url')) AS company_logo_url,
                    JSON_UNQUOTE(JSON_EXTRACT(r.company_details_json, '$.industry')) AS company_industry,
                    u.email, u.is_active, u.account_activated
             FROM recruiters r
             JOIN users u ON u.user_id = r.recruiter_id
             {$whereClause}
             ORDER BY r.created_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Count all recruiters matching filters.
     *
     * @param array $filters
     * @return int
     */
    public function countAll(array $filters = []): int
    {
        $where  = [];
        $params = [];

        if (isset($filters['account_activated'])) {
            $where[]  = 'u.account_activated = ?';
            $params[] = (int) $filters['account_activated'];
        }
        if (!empty($filters['search'])) {
            $where[]  = "(r.primary_name LIKE ? OR JSON_UNQUOTE(JSON_EXTRACT(r.company_details_json, '$.name')) LIKE ? OR u.email LIKE ?)";
            $term     = '%' . $filters['search'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total
             FROM recruiters r
             JOIN users u ON u.user_id = r.recruiter_id
             {$whereClause}"
        );
        $stmt->execute($params);
        return (int) $stmt->fetch()['total'];
    }

    /**
     * Get all jobs posted by this recruiter.
     *
     * @param string $recruiterId
     * @param int    $limit
     * @param int    $offset
     * @return array
     */
    public function findJobs(string $recruiterId, int $limit = 20, int $offset = 0): array
    {
        $stmt = $this->db->prepare(
            "SELECT j.job_id, j.title, j.job_status, j.ctc_lpa, j.stipend_pm,
                    j.apply_start, j.apply_end, j.applications_count, j.created_at,
                    jt.name AS job_type_label, ps.label AS session_label
             FROM jobs j
             LEFT JOIN job_types jt ON jt.job_type_id = j.job_type_id
             JOIN placement_sessions ps ON ps.session_id = j.session_id
             WHERE j.recruiter_id = ?
             ORDER BY j.created_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$recruiterId, $limit, $offset]);
        return $stmt->fetchAll();
    }
}
