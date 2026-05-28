<?php
/**
 * ApplicationController — Handles application API endpoints.
 *
 * Routes:
 *   POST /applications             → apply (submit application)
 *   GET  /applications/me          → myApplications (student's list)
 *   PUT  /applications/{id}/withdraw → withdraw (withdraw application)
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;
use App\Helpers\Validator;
use App\Services\ApplicationService;

class ApplicationController
{
    private ApplicationService $service;

    public function __construct()
    {
        $this->service = new ApplicationService();
    }

    /**
     * POST /applications — Apply to a job.
     * Body: { "job_id": "uuid" }
     */
    public function apply(array $params = [], ?array $user = null): void
    {
        $raw   = file_get_contents('php://input');
        $input = json_decode($raw, true);

        if (!is_array($input) || empty($input['job_id'])) {
            Response::error('job_id is required.', 422);
        }

        if (!Validator::uuid($input['job_id'])) {
            Response::error('Invalid job ID format.', 422);
        }

        $result = $this->service->apply($user['sub'], $input['job_id']);

        if ($result['success']) {
            Response::success(
                ['application_id' => $result['application_id'] ?? null],
                201,
                $result['message']
            );
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * GET /applications/me — Get authenticated student's applications.
     */
    public function myApplications(array $params = [], ?array $user = null): void
    {
        $pagination = Validator::pagination($_GET);

        $result = $this->service->getMyApplications(
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
     * PUT /applications/{id}/withdraw — Withdraw an application.
     */
    public function withdraw(array $params = [], ?array $user = null): void
    {
        $appId = $params['id'] ?? '';

        if (!Validator::uuid($appId)) {
            Response::error('Invalid application ID.', 400);
        }

        $result = $this->service->withdraw($appId, $user['sub']);

        if ($result['success']) {
            Response::success(null, 200, $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * PUT /applications/{id}/shortlist — Shortlist an applicant (Coordinator/Admin).
     */
    public function shortlist(array $params = [], ?array $user = null): void
    {
        $appId = $params['id'] ?? '';

        if (!Validator::uuid($appId)) {
            Response::error('Invalid application ID.', 400);
        }

        $result = $this->service->shortlist($appId, $user['sub']);

        if ($result['success']) {
            Response::success(null, 200, $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
