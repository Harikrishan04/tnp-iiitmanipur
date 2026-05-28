<?php
/**
 * StudentModel — Data access layer for the `students` table.
 *
 * Methods:
 *   findById(studentId)       → Get student profile by user_id
 *   updateProfile(id, data)   → Update profile fields
 *   findAll(filters, pagination) → List students with filters (coordinator/admin)
 *   countAll(filters)         → Count matching students
 */

declare(strict_types=1);

namespace App\Models;

use PDO;

class StudentModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Find a student profile by student_id (= user_id).
     * Joins with users, departments, programs for complete data.
     *
     * @param string $studentId UUID
     * @return array|null Student row with joined data
     */
    public function findById(string $studentId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT s.*,
                    u.email, u.phone AS user_phone, u.is_active, u.account_activated,
                    u.first_login_at, u.last_login_at,
                    d.name AS department_name, d.code AS department_code,
                    p.name AS program_name, p.code AS program_code
             FROM students s
             JOIN users u ON u.user_id = s.student_id
             LEFT JOIN departments d ON d.dept_id = s.dept_id
             LEFT JOIN programs p ON p.program_id = s.program_id
             WHERE s.student_id = ?"
        );
        $stmt->execute([$studentId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Update student profile fields.
     * Only updates non-null values in $data.
     *
     * @param string $studentId UUID
     * @param array  $data      Associative array of column => value
     * @return bool  True if any rows updated
     */
    public function updateProfile(string $studentId, array $data): bool
    {
        // Allowed updatable columns — whitelist approach
        // Must match actual `students` table columns in new_schema.sql
        $allowed = [
            'name', 'roll_no', 'dept_id', 'program_id',
            'year_of_admission', 'year_of_passing', 'current_semester',
            'date_of_birth', 'gender', 'category', 'blood_group',
            'cpi', 'locality', 'city', 'state', 'pincode',
            'placement_status',
            // JSON columns
            'education_details_json', 'experiences_json',
            'skills_json', 'personal_links_json',
            'family_info_json', 'documents_json',
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

        $params[] = $studentId;
        $sql = "UPDATE students SET " . implode(', ', $setClauses) . " WHERE student_id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    /**
     * List students with optional filters and pagination.
     * Used by coordinators and admins.
     *
     * @param array $filters   Optional: dept_id, program_id, placement_status, search
     * @param int   $limit
     * @param int   $offset
     * @return array List of student rows
     */
    public function findAll(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['dept_id'])) {
            $where[]  = 's.dept_id = ?';
            $params[] = $filters['dept_id'];
        }

        if (!empty($filters['program_id'])) {
            $where[]  = 's.program_id = ?';
            $params[] = $filters['program_id'];
        }

        if (!empty($filters['placement_status'])) {
            $where[]  = 's.placement_status = ?';
            $params[] = $filters['placement_status'];
        }

        if (!empty($filters['search'])) {
            $where[]  = '(s.name LIKE ? OR s.roll_no LIKE ? OR u.email LIKE ?)';
            $term     = '%' . $filters['search'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT s.student_id, s.name, s.roll_no, s.cpi, s.placement_status,
                       s.admission_year, s.current_semester, s.photo_url,
                       u.email, u.is_active,
                       d.name AS department_name, p.name AS program_name
                FROM students s
                JOIN users u ON u.user_id = s.student_id
                LEFT JOIN departments d ON d.dept_id = s.dept_id
                LEFT JOIN programs p ON p.program_id = s.program_id
                {$whereClause}
                ORDER BY s.name ASC
                LIMIT ? OFFSET ?";

        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Count total students matching filters.
     *
     * @param array $filters Same as findAll()
     * @return int
     */
    public function countAll(array $filters = []): int
    {
        $where  = [];
        $params = [];

        if (!empty($filters['dept_id'])) {
            $where[]  = 's.dept_id = ?';
            $params[] = $filters['dept_id'];
        }
        if (!empty($filters['program_id'])) {
            $where[]  = 's.program_id = ?';
            $params[] = $filters['program_id'];
        }
        if (!empty($filters['placement_status'])) {
            $where[]  = 's.placement_status = ?';
            $params[] = $filters['placement_status'];
        }
        if (!empty($filters['search'])) {
            $where[]  = '(s.name LIKE ? OR s.roll_no LIKE ? OR u.email LIKE ?)';
            $term     = '%' . $filters['search'] . '%';
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total FROM students s
             JOIN users u ON u.user_id = s.student_id
             {$whereClause}"
        );
        $stmt->execute($params);
        return (int) $stmt->fetch()['total'];
    }
}
