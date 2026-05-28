<?php
/**
 * RecruiterService — Business logic for recruiter operations.
 *
 * Methods:
 *   getProfile(recruiterId)              → Full profile with company details
 *   updateContact(recruiterId, data)     → Update primary/alt contact
 *   updateCompany(recruiterId, data)     → Update company_details_json
 *   getMyJobs(recruiterId, page)         → Paginated list of posted jobs
 *   getJobApplications(jobId, recruiterId) → Applications for a recruiter's job
 */

declare(strict_types=1);

namespace App\Services;

use App\Config\Database;
use App\Helpers\Logger;
use App\Models\RecruiterModel;
use App\Models\JobModel;
use PDO;

class RecruiterService
{
    private PDO $db;
    private RecruiterModel $recruiterModel;
    private JobModel $jobModel;

    public function __construct()
    {
        $this->db             = Database::getInstance();
        $this->recruiterModel = new RecruiterModel($this->db);
        $this->jobModel       = new JobModel($this->db);
    }

    /**
     * Get recruiter's full profile.
     *
     * Decodes company_details_json into a structured 'company' key
     * so the frontend receives a clean object.
     *
     * @param string $recruiterId UUID
     * @return array
     */
    public function getProfile(string $recruiterId): array
    {
        $profile = $this->recruiterModel->findById($recruiterId);

        if (!$profile) {
            return ['success' => false, 'message' => 'Recruiter profile not found.'];
        }

        // Decode JSON company details into structured key
        if (!empty($profile['company_details_json'])) {
            $profile['company'] = json_decode($profile['company_details_json'], true) ?? [];
        } else {
            $profile['company'] = [];
        }
        unset($profile['company_details_json']);

        return ['success' => true, 'data' => $profile];
    }

    /**
     * Update recruiter's contact information.
     *
     * @param string $recruiterId
     * @param array  $data
     * @return array
     */
    public function updateContact(string $recruiterId, array $data): array
    {
        $errors = $this->validateContact($data);
        if (!empty($errors)) {
            return ['success' => false, 'message' => 'Validation failed.', 'errors' => $errors];
        }

        $updated = $this->recruiterModel->updateProfile($recruiterId, $data);
        Logger::info('recruiter', "Contact updated", ['recruiter_id' => $recruiterId]);
        return ['success' => true, 'message' => $updated ? 'Contact updated.' : 'No changes.'];
    }

    /**
     * Update company details (stored as JSON).
     *
     * @param string $recruiterId
     * @param array  $data Keys: name, logo_url, website, industry, size, about, hq_location
     * @return array
     */
    public function updateCompany(string $recruiterId, array $data): array
    {
        $allowed = ['name', 'logo_url', 'website', 'industry', 'size', 'about', 'hq_location'];
        $clean   = array_intersect_key($data, array_flip($allowed));

        if (empty($clean)) {
            return ['success' => false, 'message' => 'No valid company fields provided.'];
        }

        if (!empty($clean['website']) && !filter_var($clean['website'], FILTER_VALIDATE_URL)) {
            return ['success' => false, 'message' => 'Invalid website URL.', 'errors' => ['website' => 'Must be a valid URL.']];
        }

        $this->recruiterModel->updateCompanyDetails($recruiterId, $clean);
        Logger::info('recruiter', "Company details updated", ['recruiter_id' => $recruiterId]);
        return ['success' => true, 'message' => 'Company profile updated.'];
    }

    /**
     * Get paginated list of jobs posted by this recruiter.
     *
     * @param string $recruiterId
     * @param int    $page
     * @param int    $perPage
     * @return array
     */
    public function getMyJobs(string $recruiterId, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $jobs   = $this->recruiterModel->findJobs($recruiterId, $perPage, $offset);

        return [
            'success' => true,
            'data'    => $jobs,
            'meta'    => [
                'page'     => $page,
                'per_page' => $perPage,
                'total'    => count($jobs), // lightweight count for own jobs
            ],
        ];
    }

    /**
     * Get applications for a specific job owned by this recruiter.
     * Validates ownership before returning data.
     *
     * @param string $jobId
     * @param string $recruiterId  Must own the job
     * @param int    $page
     * @param int    $perPage
     * @return array
     */
    public function getJobApplications(string $jobId, string $recruiterId, int $page = 1, int $perPage = 20): array
    {
        // Verify recruiter owns this job
        $stmt = $this->db->prepare("SELECT job_id FROM jobs WHERE job_id = ? AND recruiter_id = ?");
        $stmt->execute([$jobId, $recruiterId]);
        if (!$stmt->fetch()) {
            return ['success' => false, 'message' => 'Job not found or access denied.'];
        }

        $offset = ($page - 1) * $perPage;

        $stmt = $this->db->prepare(
            "SELECT a.application_id, a.status, a.applied_at, a.is_shortlisted,
                    a.resume_url, a.resume_visible_to_recruiter,
                    s.name AS student_name, s.roll_no, s.cpi, s.dept_id,
                    d.name AS department_name,
                    u.email AS student_email
             FROM applications a
             JOIN students s ON s.student_id = a.student_id
             JOIN users u ON u.user_id = a.student_id
             LEFT JOIN departments d ON d.dept_id = s.dept_id
             WHERE a.job_id = ?
               AND a.status != 'withdrawn'
               AND a.resume_visible_to_recruiter = TRUE
             ORDER BY a.applied_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$jobId, $perPage, $offset]);
        $apps = $stmt->fetchAll();

        // Count total
        $cnt = $this->db->prepare(
            "SELECT COUNT(*) AS total FROM applications
             WHERE job_id = ? AND status != 'withdrawn' AND resume_visible_to_recruiter = TRUE"
        );
        $cnt->execute([$jobId]);
        $total = (int) $cnt->fetch()['total'];

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
     * Validate contact update data.
     *
     * @param array $data
     * @return array Errors (empty = valid)
     */
    private function validateContact(array $data): array
    {
        $errors = [];
        if (isset($data['primary_phone']) && !empty($data['primary_phone'])) {
            if (!preg_match('/^[0-9+\-\s]{7,15}$/', $data['primary_phone'])) {
                $errors['primary_phone'] = 'Invalid phone format.';
            }
        }
        if (isset($data['alt_phone']) && !empty($data['alt_phone'])) {
            if (!preg_match('/^[0-9+\-\s]{7,15}$/', $data['alt_phone'])) {
                $errors['alt_phone'] = 'Invalid phone format.';
            }
        }
        if (isset($data['primary_linkedin']) && !empty($data['primary_linkedin'])) {
            if (!str_contains($data['primary_linkedin'], 'linkedin.com')) {
                $errors['primary_linkedin'] = 'Must be a LinkedIn URL.';
            }
        }
        return $errors;
    }
}
