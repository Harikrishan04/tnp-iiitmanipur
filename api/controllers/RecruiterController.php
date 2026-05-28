<?php
/**
 * RecruiterController — Handles recruiter profile and job management API endpoints.
 *
 * Routes:
 *   GET  /recruiters/me                    → getProfile (own profile)
 *   PUT  /recruiters/me/contact            → updateContact (primary/alt contact)
 *   PUT  /recruiters/me/company            → updateCompany (company_details_json)
 *   GET  /recruiters/me/jobs               → myJobs (posted jobs)
 *   GET  /recruiters/me/jobs/{id}/applications → jobApplications
 *   POST /recruiters/me/jobs               → postJob (create draft)
 *   PUT  /recruiters/me/jobs/{id}          → updateJob (edit draft)
 *   PUT  /recruiters/me/jobs/{id}/submit   → submitJob (draft → pending)
 *   GET  /recruiters                       → list (admin/coordinator)
 *   GET  /recruiters/{id}                  → getById (admin/coordinator)
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\Response;
use App\Helpers\Validator;
use App\Models\JobModel;
use App\Services\RecruiterService;

class RecruiterController
{
    private RecruiterService $service;

    public function __construct()
    {
        $this->service = new RecruiterService();
    }

    /**
     * GET /recruiters/me — Own profile.
     */
    public function me(array $params = [], ?array $user = null): void
    {
        $result = $this->service->getProfile($user['sub']);
        $result['success']
            ? Response::success($result['data'])
            : Response::notFound($result['message']);
    }

    /**
     * PUT /recruiters/me/contact — Update primary/alt contact fields.
     */
    public function updateContact(array $params = [], ?array $user = null): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $result = $this->service->updateContact($user['sub'], $input);
        $result['success']
            ? Response::success(null, 200, $result['message'])
            : Response::error($result['message'], 422, $result['errors'] ?? []);
    }

    /**
     * PUT /recruiters/me/company — Update company_details_json.
     */
    public function updateCompany(array $params = [], ?array $user = null): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $result = $this->service->updateCompany($user['sub'], $input);
        $result['success']
            ? Response::success(null, 200, $result['message'])
            : Response::error($result['message'], 422, $result['errors'] ?? []);
    }

    /**
     * GET /recruiters/me/jobs — List this recruiter's job postings.
     */
    public function myJobs(array $params = [], ?array $user = null): void
    {
        $page = Validator::pagination($_GET)['page'];
        $result = $this->service->getMyJobs($user['sub'], $page);
        Response::paginated($result['data'], $result['meta']['page'], $result['meta']['per_page'], $result['meta']['total']);
    }

    /**
     * GET /recruiters/me/jobs/{id}/applications — Applications for a specific job.
     */
    public function jobApplications(array $params = [], ?array $user = null): void
    {
        $jobId = $params['id'] ?? '';
        if (!Validator::uuid($jobId)) { Response::error('Invalid job ID.', 400); return; }

        $pagination = Validator::pagination($_GET);
        $result     = $this->service->getJobApplications($jobId, $user['sub'], $pagination['page'], $pagination['per_page']);

        $result['success']
            ? Response::paginated($result['data'], $result['meta']['page'], $result['meta']['per_page'], $result['meta']['total'])
            : Response::error($result['message']);
    }

    /**
     * POST /recruiters/me/jobs — Create a new job in draft status.
     * Body: { title, description, job_type_id, session_id, ctc_lpa, ... }
     */
    public function postJob(array $params = [], ?array $user = null): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        if (empty($input['title'])) {
            Response::error('Job title is required.', 422);
            return;
        }

        // Get active session_id if not provided
        $sessionId = $input['session_id'] ?? null;
        if (!$sessionId) {
            $db   = Database::getInstance();
            $stmt = $db->query("SELECT session_id FROM placement_sessions WHERE is_active = TRUE LIMIT 1");
            $sess = $stmt->fetch();
            if (!$sess) { Response::error('No active placement session found.'); return; }
            $sessionId = $sess['session_id'];
        }

        // Encode JSON arrays if passed as PHP arrays
        if (isset($input['allowed_branches_json']) && is_array($input['allowed_branches_json'])) {
            $input['allowed_branches_json'] = json_encode($input['allowed_branches_json']);
        }
        if (isset($input['allowed_programs_json']) && is_array($input['allowed_programs_json'])) {
            $input['allowed_programs_json'] = json_encode($input['allowed_programs_json']);
        }

        try {
            $db      = Database::getInstance();
            $jobModel = new JobModel($db);
            $jobId   = $jobModel->create($user['sub'], $sessionId, $input);
            Response::success(['job_id' => $jobId], 201, 'Job draft created.');
        } catch (\PDOException $e) {
            Response::error('Failed to create job: ' . $e->getMessage());
        }
    }

    /**
     * PUT /recruiters/me/jobs/{id} — Update a draft or pending job.
     */
    public function updateJob(array $params = [], ?array $user = null): void
    {
        $jobId = $params['id'] ?? '';
        if (!Validator::uuid($jobId)) { Response::error('Invalid job ID.', 400); return; }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        if (isset($input['allowed_branches_json']) && is_array($input['allowed_branches_json'])) {
            $input['allowed_branches_json'] = json_encode($input['allowed_branches_json']);
        }
        if (isset($input['allowed_programs_json']) && is_array($input['allowed_programs_json'])) {
            $input['allowed_programs_json'] = json_encode($input['allowed_programs_json']);
        }

        $db      = Database::getInstance();
        $jobModel = new JobModel($db);
        $updated = $jobModel->update($jobId, $user['sub'], $input);

        $updated
            ? Response::success(null, 200, 'Job updated.')
            : Response::error('Job not found or cannot be edited (not in draft/pending).', 403);
    }

    /**
     * PUT /recruiters/me/jobs/{id}/submit — Submit draft for coordinator review.
     */
    public function submitJob(array $params = [], ?array $user = null): void
    {
        $jobId = $params['id'] ?? '';
        if (!Validator::uuid($jobId)) { Response::error('Invalid job ID.', 400); return; }

        $db      = Database::getInstance();
        $jobModel = new JobModel($db);
        $done    = $jobModel->submitForReview($jobId, $user['sub']);

        $done
            ? Response::success(null, 200, 'Job submitted for review.')
            : Response::error('Job not found or already submitted.', 403);
    }

    /**
     * GET /recruiters/me/lookups — job types, depts, programs for form dropdowns.
     */
    public function lookups(array $params = [], ?array $user = null): void
    {
        $db      = Database::getInstance();
        $jobModel = new JobModel($db);
        $studentService = new \App\Services\StudentService();
        Response::success([
            'job_types'   => $jobModel->getJobTypes(),
            'departments' => $studentService->getDepartments(),
            'programs'    => $studentService->getPrograms(),
        ]);
    }

    /**
     * GET /recruiters — List all recruiters (coordinator/admin).
     */
    public function list(array $params = [], ?array $user = null): void
    {
        $pagination = Validator::pagination($_GET);
        $filters    = [
            'account_activated' => isset($_GET['account_activated']) ? (int)$_GET['account_activated'] : null,
            'search'            => $_GET['search'] ?? null,
        ];
        // Remove null filters
        $filters = array_filter($filters, fn($v) => $v !== null);

        $db    = Database::getInstance();
        $model = new \App\Models\RecruiterModel($db);

        $recruiters = $model->findAll($filters, $pagination['per_page'], $pagination['offset']);
        $total      = $model->countAll($filters);

        Response::paginated($recruiters, $pagination['page'], $pagination['per_page'], $total);
    }

    /**
     * GET /recruiters/{id} — Get recruiter by ID (coordinator/admin).
     */
    public function getById(array $params = [], ?array $user = null): void
    {
        $id = $params['id'] ?? '';
        if (!Validator::uuid($id)) { Response::error('Invalid recruiter ID.', 400); return; }

        $result = $this->service->getProfile($id);
        $result['success']
            ? Response::success($result['data'])
            : Response::notFound($result['message']);
    }
}
