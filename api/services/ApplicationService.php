<?php
/**
 * ApplicationService — Business logic for job applications.
 *
 * Methods:
 *   apply(studentId, jobId)              → Apply to a job (with validations)
 *   getMyApplications(studentId, page)   → Student's applications
 *   withdraw(applicationId, studentId)   → Withdraw an application
 */

declare(strict_types=1);

namespace App\Services;

use App\Config\Database;
use App\Helpers\Logger;
use App\Models\ApplicationModel;
use App\Models\JobModel;
use App\Models\StudentModel;
use PDO;

class ApplicationService
{
    private PDO $db;
    private ApplicationModel $appModel;
    private JobModel $jobModel;
    private StudentModel $studentModel;

    public function __construct()
    {
        $this->db           = Database::getInstance();
        $this->appModel     = new ApplicationModel($this->db);
        $this->jobModel     = new JobModel($this->db);
        $this->studentModel = new StudentModel($this->db);
    }

    /**
     * Apply to a job. Validates eligibility, duplicates, capacity, and placement status.
     *
     * @param string $studentId UUID
     * @param string $jobId     UUID
     * @return array ['success' => bool, 'message' => string]
     */
    public function apply(string $studentId, string $jobId): array
    {
        // Check student exists
        $student = $this->studentModel->findById($studentId);
        if (!$student) {
            return ['success' => false, 'message' => 'Student profile not found.'];
        }

        // Check if already placed
        if ($student['placement_status'] === 'placed') {
            return ['success' => false, 'message' => 'You are already placed and cannot apply to more jobs.'];
        }

        // Check job exists and is approved
        $job = $this->jobModel->findById($jobId);
        if (!$job) {
            return ['success' => false, 'message' => 'Job not found.'];
        }
        if ($job['job_status'] !== 'opened') {
            return ['success' => false, 'message' => 'This job is not open for applications.'];
        }

        // Check apply window
        $now = time();
        if ($job['apply_start'] && strtotime($job['apply_start']) > $now) {
            return ['success' => false, 'message' => 'Application window has not opened yet.'];
        }
        if ($job['apply_end'] && strtotime($job['apply_end']) < $now) {
            return ['success' => false, 'message' => 'Application deadline has passed.'];
        }

        // Check CPI eligibility
        if ($job['min_cpi'] && $student['cpi'] !== null && (float) $student['cpi'] < (float) $job['min_cpi']) {
            return ['success' => false, 'message' => 'Your CPI does not meet the minimum requirement.'];
        }

        // Check duplicate application
        if ($this->appModel->hasApplied($studentId, $jobId)) {
            return ['success' => false, 'message' => 'You have already applied to this job.'];
        }

        // Build eligibility snapshot captured at time of apply
        $snapshot = [
            'cpi'        => $student['cpi'],
            'dept_id'    => $student['dept_id'],
            'program_id' => $student['program_id'],
            'applied_at' => date('Y-m-d H:i:s'),
        ];

        // Use student's current resume URL (empty string if not uploaded yet)
        $resumeUrl = $student['documents_json']
            ? (json_decode($student['documents_json'], true)['resume_url'] ?? '')
            : '';

        // Apply (the DB trigger trg_before_application_insert handles capacity check)
        try {
            $appId = $this->appModel->create($studentId, $jobId, $resumeUrl, $snapshot);
            Logger::info('application', "Student applied", [
                'student_id' => $studentId,
                'job_id'     => $jobId,
                'app_id'     => $appId,
            ]);
            return ['success' => true, 'message' => 'Application submitted successfully.', 'application_id' => $appId];
        } catch (\PDOException $e) {
            if (str_contains($e->getMessage(), 'capacity')) {
                return ['success' => false, 'message' => 'This job has reached maximum applicant capacity.'];
            }
            Logger::error('application', "Apply failed", ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Failed to submit application.'];
        }
    }

    /**
     * Get a student's applications with job details.
     *
     * @param string $studentId
     * @param int    $page
     * @param int    $perPage
     * @return array
     */
    public function getMyApplications(string $studentId, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $apps   = $this->appModel->findByStudent($studentId, $perPage, $offset);
        $total  = $this->appModel->countByStudent($studentId);

        return [
            'success' => true,
            'data'    => $apps,
            'meta'    => [
                'page'        => $page,
                'per_page'    => $perPage,
                'total'       => $total,
                'total_pages' => (int) ceil($total / max($perPage, 1)),
            ],
        ];
    }

    /**
     * Withdraw an application.
     *
     * @param string $applicationId
     * @param string $studentId
     * @return array
     */
    public function withdraw(string $applicationId, string $studentId): array
    {
        $withdrawn = $this->appModel->withdraw($applicationId, $studentId);

        if ($withdrawn) {
            Logger::info('application', "Application withdrawn", [
                'application_id' => $applicationId,
                'student_id'     => $studentId,
            ]);
            return ['success' => true, 'message' => 'Application withdrawn.'];
        }

        return ['success' => false, 'message' => 'Unable to withdraw. Application may already be processed.'];
    }

    /**
     * Shortlist an application (Coordinator/Admin only).
     *
     * @param string $applicationId
     * @param string $coordinatorUserId
     * @return array
     */
    public function shortlist(string $applicationId, string $coordinatorUserId): array
    {
        $stmt = $this->db->prepare(
            "UPDATE applications
             SET is_shortlisted = TRUE,
                 shortlisted_at = NOW(),
                 shortlisted_by = ?,
                 status         = 'shortlisted'
             WHERE application_id = ? AND status = 'applied'"
        );
        $stmt->execute([$coordinatorUserId, $applicationId]);

        if ($stmt->rowCount() > 0) {
            Logger::info('application', "Application shortlisted", [
                'application_id' => $applicationId,
                'shortlisted_by' => $coordinatorUserId,
            ]);
            return ['success' => true, 'message' => 'Applicant shortlisted successfully.'];
        }

        return ['success' => false, 'message' => 'Unable to shortlist. Application not found or not in applied status.'];
    }
}
