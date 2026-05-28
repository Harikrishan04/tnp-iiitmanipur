<?php
/**
 * CoordinatorController — Handles coordinator API endpoints.
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;
use App\Helpers\Validator;
use App\Services\VerificationService;

class CoordinatorController
{
    private VerificationService $service;

    public function __construct()
    {
        $this->service = new VerificationService();
    }

    /**
     * GET /verifications — List verifications.
     */
    public function listVerifications(array $params = [], ?array $user = null): void
    {
        $pagination = Validator::pagination($_GET);
        $filters    = [
            'entity_type'              => $_GET['entity_type'] ?? null,
            'status'                   => $_GET['status'] ?? null,
            'assigned_coordinator_id'  => $_GET['assigned_coordinator_id'] ?? null,
        ];

        $result = $this->service->getVerifications($filters, $pagination['page'], $pagination['per_page']);

        if ($result['success']) {
            Response::paginated(
                $result['data'],
                $result['meta']['page'],
                $result['meta']['per_page'],
                $result['meta']['total']
            );
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * GET /verifications/{id} — Get verification details.
     */
    public function getVerification(array $params = [], ?array $user = null): void
    {
        $id = $params['id'] ?? '';

        if (!Validator::uuid($id)) {
            Response::error('Invalid verification ID.', 400);
        }

        $result = $this->service->getVerification($id);

        if ($result['success']) {
            Response::success($result['data']);
        } else {
            Response::notFound($result['message']);
        }
    }

    /**
     * PUT /verifications/{id} — Update verification status (verify/reject/resubmit).
     */
    public function updateVerification(array $params = [], ?array $user = null): void
    {
        $id = $params['id'] ?? '';

        if (!Validator::uuid($id)) {
            Response::error('Invalid verification ID.', 400);
        }

        $raw   = file_get_contents('php://input');
        $input = json_decode($raw, true);

        if (!is_array($input) || empty($input['status'])) {
            Response::error('Status is required.', 422);
        }

        $result = $this->service->updateStatus($id, $input['status'], $user['sub'], $input['remark'] ?? null);

        if ($result['success']) {
            Response::success(null, 200, $result['message']);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * PUT /verifications/{id}/assign — Assign coordinator.
     */
    public function assignVerification(array $params = [], ?array $user = null): void
    {
        $id = $params['id'] ?? '';

        if (!Validator::uuid($id)) {
            Response::error('Invalid verification ID.', 400);
        }

        $raw   = file_get_contents('php://input');
        $input = json_decode($raw, true);

        $coordinatorId = $input['assigned_coordinator_id'] ?? null;
        if ($coordinatorId !== null && !Validator::uuid($coordinatorId)) {
            Response::error('Invalid coordinator ID.', 422);
        }

        $result = $this->service->assignCoordinator($id, $coordinatorId);

        if ($result['success']) {
            Response::success(null, 200, $result['message']);
        } else {
            Response::error($result['message']);
        }
    }
}
