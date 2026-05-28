<?php
/**
 * JobService — Business logic for job-related operations.
 *
 * Methods:
 *   getEligibleJobs(studentId, pagination)  → Jobs matching student eligibility
 *   getJobDetails(jobId)                    → Full job details with rounds
 */

declare(strict_types=1);

namespace App\Services;

use App\Config\Database;
use App\Models\JobModel;
use App\Models\StudentModel;
use PDO;

class JobService
{
    private PDO $db;
    private JobModel $jobModel;
    private StudentModel $studentModel;

    public function __construct()
    {
        $this->db           = Database::getInstance();
        $this->jobModel     = new JobModel($this->db);
        $this->studentModel = new StudentModel($this->db);
    }

    /**
     * Get eligible jobs for a student based on their profile.
     *
     * @param string $studentId UUID
     * @param int    $page
     * @param int    $perPage
     * @return array ['success' => bool, 'data' => array, 'meta' => array]
     */
    public function getEligibleJobs(string $studentId, int $page = 1, int $perPage = 20): array
    {
        $student = $this->studentModel->findById($studentId);

        if (!$student) {
            return ['success' => false, 'message' => 'Student profile not found.'];
        }

        $offset = ($page - 1) * $perPage;
        $jobs   = $this->jobModel->findEligible($student, $perPage, $offset);
        $total  = $this->jobModel->countEligible($student);

        return [
            'success' => true,
            'data'    => $jobs,
            'meta'    => [
                'page'        => $page,
                'per_page'    => $perPage,
                'total'       => $total,
                'total_pages' => (int) ceil($total / max($perPage, 1)),
            ],
        ];
    }

    /**
     * Get full job details including rounds.
     *
     * @param string $jobId UUID
     * @return array
     */
    public function getJobDetails(string $jobId): array
    {
        $job = $this->jobModel->findById($jobId);

        if (!$job) {
            return ['success' => false, 'message' => 'Job not found.'];
        }

        // Fetch rounds for this job
        $stmt = $this->db->prepare(
            "SELECT jr.round_id, jr.round_number, jr.round_status,
                    jr.scheduled_at, jr.location, jr.submission_deadline,
                    rt.name AS round_type_label
             FROM job_rounds jr
             LEFT JOIN round_types rt ON rt.round_type_id = jr.round_type_id
             WHERE jr.job_id = ?
             ORDER BY jr.round_number ASC"
        );
        $stmt->execute([$jobId]);
        $job['rounds'] = $stmt->fetchAll();

        return ['success' => true, 'data' => $job];
    }
}
