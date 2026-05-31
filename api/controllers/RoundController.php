<?php
/**
 * RoundController — Handles the full placement drive lifecycle:
 *
 * Job status transitions (coordinator/admin):
 *   PUT  /rounds/jobs/{id}/open     → verified → opened
 *   PUT  /rounds/jobs/{id}/close    → opened   → closed
 *
 * Round management (coordinator/admin):
 *   GET  /rounds/jobs/{id}/rounds         → List rounds for a job
 *   POST /rounds/jobs/{id}/rounds         → Add a round
 *   GET  /rounds/types                    → Round type lookup
 *
 * Round lifecycle (coordinator/admin):
 *   PUT  /rounds/{id}/start               → Start a round
 *   PUT  /rounds/{id}/results             → Enter/update bulk results
 *   PUT  /rounds/{id}/end                 → End a round
 *   PUT  /rounds/{id}/publish             → Publish results to students
 *   GET  /rounds/{id}/results             → View round results
 *
 * Final selection (coordinator/admin):
 *   POST /rounds/jobs/{id}/select         → Select students + create placements
 *
 * Student view:
 *   GET  /rounds/jobs/{id}/my-results     → Student's own results for a job
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;
use App\Helpers\Validator;
use App\Services\RoundService;

class RoundController
{
    private RoundService $service;

    public function __construct()
    {
        $this->service = new RoundService();
    }

    // ═══ JOB STATUS ═══

    /** PUT /rounds/jobs/{id}/open */
    public function openJob(array $params = [], ?array $user = null): void
    {
        $jobId = $params['id'] ?? '';
        if (!Validator::uuid($jobId)) { Response::error('Invalid job ID.', 400); return; }

        $result = $this->service->openJob($jobId);
        $result['success']
            ? Response::success(null, 200, $result['message'])
            : Response::error($result['message'], 422);
    }

    /** PUT /rounds/jobs/{id}/close */
    public function closeJob(array $params = [], ?array $user = null): void
    {
        $jobId = $params['id'] ?? '';
        if (!Validator::uuid($jobId)) { Response::error('Invalid job ID.', 400); return; }

        $result = $this->service->closeJob($jobId);
        $result['success']
            ? Response::success(null, 200, $result['message'])
            : Response::error($result['message'], 422);
    }

    // ═══ ROUNDS ═══

    /** GET /rounds/types */
    public function getRoundTypes(array $params = [], ?array $user = null): void
    {
        Response::success($this->service->getRoundTypes());
    }

    /** GET /rounds/jobs/{id}/rounds */
    public function listRounds(array $params = [], ?array $user = null): void
    {
        $jobId = $params['id'] ?? '';
        if (!Validator::uuid($jobId)) { Response::error('Invalid job ID.', 400); return; }

        $result = $this->service->getRoundsForJob($jobId);
        Response::success($result['data']);
    }

    /** POST /rounds/jobs/{id}/rounds */
    public function addRound(array $params = [], ?array $user = null): void
    {
        $jobId = $params['id'] ?? '';
        if (!Validator::uuid($jobId)) { Response::error('Invalid job ID.', 400); return; }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $suggestedBy = in_array($user['role'] ?? '', ['recruiter'], true) ? 'recruiter' : 'admin';

        $result = $this->service->addRound($jobId, $input, $suggestedBy);
        $result['success']
            ? Response::success($result['data'], 201, $result['message'])
            : Response::error($result['message'], 422);
    }

    // ═══ ROUND LIFECYCLE ═══

    /** PUT /rounds/{id}/start */
    public function startRound(array $params = [], ?array $user = null): void
    {
        $roundId = $params['id'] ?? '';
        if (!Validator::uuid($roundId)) { Response::error('Invalid round ID.', 400); return; }

        $result = $this->service->startRound($roundId);
        $result['success']
            ? Response::success($result['data'] ?? null, 200, $result['message'])
            : Response::error($result['message'], 422);
    }

    /** PUT /rounds/{id}/results — Bulk enter results */
    public function enterResults(array $params = [], ?array $user = null): void
    {
        $roundId = $params['id'] ?? '';
        if (!Validator::uuid($roundId)) { Response::error('Invalid round ID.', 400); return; }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $results = $input['results'] ?? [];

        if (!is_array($results) || empty($results)) {
            Response::error('results array is required.', 422);
            return;
        }

        $result = $this->service->enterResults($roundId, $results, $user['sub']);
        $result['success']
            ? Response::success(null, 200, $result['message'])
            : Response::error($result['message'], 422);
    }

    /** PUT /rounds/{id}/end */
    public function endRound(array $params = [], ?array $user = null): void
    {
        $roundId = $params['id'] ?? '';
        if (!Validator::uuid($roundId)) { Response::error('Invalid round ID.', 400); return; }

        $result = $this->service->endRound($roundId);
        $result['success']
            ? Response::success(null, 200, $result['message'])
            : Response::error($result['message'], 422);
    }

    /** PUT /rounds/{id}/publish */
    public function publishResults(array $params = [], ?array $user = null): void
    {
        $roundId = $params['id'] ?? '';
        if (!Validator::uuid($roundId)) { Response::error('Invalid round ID.', 400); return; }

        $result = $this->service->publishResults($roundId, $user['sub']);
        $result['success']
            ? Response::success(null, 200, $result['message'])
            : Response::error($result['message'], 422);
    }

    /** GET /rounds/{id}/results */
    public function getRoundResults(array $params = [], ?array $user = null): void
    {
        $roundId = $params['id'] ?? '';
        if (!Validator::uuid($roundId)) { Response::error('Invalid round ID.', 400); return; }

        $result = $this->service->getRoundResults($roundId);
        Response::success($result['data']);
    }

    /** POST /rounds/jobs/{id}/select — Final selection */
    public function selectStudents(array $params = [], ?array $user = null): void
    {
        $jobId = $params['id'] ?? '';
        if (!Validator::uuid($jobId)) { Response::error('Invalid job ID.', 400); return; }

        $input = json_decode(file_get_contents('php://input'), true) ?? [];
        $applicationIds = $input['application_ids'] ?? [];

        if (!is_array($applicationIds) || empty($applicationIds)) {
            Response::error('application_ids array is required.', 422);
            return;
        }

        $result = $this->service->selectStudents($jobId, $applicationIds, $input, $user['sub']);
        $result['success']
            ? Response::success(null, 200, $result['message'])
            : Response::error($result['message'], 422);
    }

    /** GET /rounds/jobs/{id}/my-results — Student's own results */
    public function getMyResults(array $params = [], ?array $user = null): void
    {
        $jobId = $params['id'] ?? '';
        if (!Validator::uuid($jobId)) { Response::error('Invalid job ID.', 400); return; }

        $result = $this->service->getMyRoundResults($user['sub'], $jobId);
        Response::success($result['data']);
    }
}
