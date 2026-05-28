<?php
/**
 * JobController — Handles job listing API endpoints.
 *
 * Routes:
 *   GET /jobs         → list (eligible jobs for students, all for admins)
 *   GET /jobs/{id}    → show (job detail with rounds)
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;
use App\Helpers\Validator;
use App\Services\JobService;

class JobController
{
    private JobService $service;

    public function __construct()
    {
        $this->service = new JobService();
    }

    /**
     * GET /jobs — List eligible jobs for the authenticated student.
     */
    public function list(array $params = [], ?array $user = null): void
    {
        $pagination = Validator::pagination($_GET);

        $result = $this->service->getEligibleJobs(
            $user['sub'],
            $pagination['page'],
            $pagination['per_page']
        );

        if ($result['success']) {
            Response::paginated($result['data'], $result['meta']['page'], $result['meta']['per_page'], $result['meta']['total']);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * GET /jobs/{id} — Get full job details with rounds.
     */
    public function show(array $params = [], ?array $user = null): void
    {
        $jobId = $params['id'] ?? '';

        if (!Validator::uuid($jobId)) {
            Response::error('Invalid job ID.', 400);
        }

        $result = $this->service->getJobDetails($jobId);

        if ($result['success']) {
            Response::success($result['data']);
        } else {
            Response::notFound($result['message']);
        }
    }
}
