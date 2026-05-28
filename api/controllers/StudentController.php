<?php
/**
 * StudentController — Handles student profile API endpoints.
 *
 * Routes:
 *   GET  /students/me         → getProfile (own profile)
 *   PUT  /students/me         → updateProfile (own profile)
 *   GET  /students/lookups    → getLookups (departments, programs)
 *   GET  /students            → list (coordinator/admin only)
 *   GET  /students/{id}       → getById (coordinator/admin only)
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;
use App\Helpers\Validator;
use App\Services\StudentService;

class StudentController
{
    private StudentService $service;

    public function __construct()
    {
        $this->service = new StudentService();
    }

    /**
     * GET /students/me — Get authenticated student's profile.
     */
    public function me(array $params = [], ?array $user = null): void
    {
        $result = $this->service->getProfile($user['sub']);

        if ($result['success']) {
            Response::success($result['data']);
        } else {
            Response::notFound($result['message']);
        }
    }

    /**
     * PUT /students/me — Update authenticated student's profile.
     */
    public function updateMe(array $params = [], ?array $user = null): void
    {
        $raw   = file_get_contents('php://input');
        $input = json_decode($raw, true);

        if (!is_array($input)) {
            Response::error('Invalid JSON input.', 400);
        }

        $result = $this->service->updateProfile($user['sub'], $input);

        if ($result['success']) {
            Response::success(null, 200, $result['message']);
        } else {
            Response::error($result['message'], 422, $result['errors'] ?? []);
        }
    }

    /**
     * GET /students/lookups — Get departments and programs for form dropdowns.
     */
    public function lookups(array $params = [], ?array $user = null): void
    {
        Response::success([
            'departments' => $this->service->getDepartments(),
            'programs'    => $this->service->getPrograms(),
        ]);
    }

    /**
     * GET /students — List students (coordinator/admin).
     */
    public function list(array $params = [], ?array $user = null): void
    {
        $pagination = Validator::pagination($_GET);

        $filters = [
            'dept_id'          => $_GET['dept_id'] ?? null,
            'program_id'      => $_GET['program_id'] ?? null,
            'placement_status' => $_GET['placement_status'] ?? null,
            'search'          => $_GET['search'] ?? null,
        ];

        $students = (new \App\Models\StudentModel(\App\Config\Database::getInstance()))
            ->findAll($filters, $pagination['per_page'], $pagination['offset']);

        $total = (new \App\Models\StudentModel(\App\Config\Database::getInstance()))
            ->countAll($filters);

        Response::paginated($students, $pagination['page'], $pagination['per_page'], $total);
    }

    /**
     * GET /students/{id} — Get a student by ID (coordinator/admin).
     */
    public function getById(array $params = [], ?array $user = null): void
    {
        $id = $params['id'] ?? '';

        if (!Validator::uuid($id)) {
            Response::error('Invalid student ID.', 400);
        }

        $result = $this->service->getProfile($id);

        if ($result['success']) {
            Response::success($result['data']);
        } else {
            Response::notFound($result['message']);
        }
    }
}
