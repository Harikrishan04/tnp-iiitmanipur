<?php
/**
 * Route Definitions — All API endpoints registered here.
 *
 * Middleware shorthand:
 *   'auth'                   → JWT required
 *   'role:student'           → JWT + student role required
 *   'role:admin,coordinator' → JWT + one of these roles required
 *
 * This file is included by index.php and receives $router.
 */

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\StudentController;
use App\Controllers\JobController;
use App\Controllers\ApplicationController;
use App\Controllers\RecruiterController;
use App\Controllers\CoordinatorController;
use App\Controllers\AdminController;

// ─── Auth (no auth required) ─────────────────────────────────────────────────
$router->group('/auth', function ($router) {
    $router->post('/login',      [AuthController::class, 'login']);
    $router->post('/verify-otp', [AuthController::class, 'verifyOtp']);
    $router->post('/refresh',    [AuthController::class, 'refresh'], ['auth']);
});
// ─── Students ────────────────────────────────────────────────────────────────
$router->group('/students', function ($router) {
    $router->get('/me',       [StudentController::class, 'me'],       ['auth', 'role:student']);
    $router->put('/me',       [StudentController::class, 'updateMe'], ['auth', 'role:student']);
    $router->get('/lookups',  [StudentController::class, 'lookups'],  ['auth', 'role:student']);
    $router->get('',          [StudentController::class, 'list'],     ['auth', 'role:coordinator,admin']);
    $router->get('/{id}',     [StudentController::class, 'getById'],  ['auth', 'role:coordinator,admin']);
});

// ─── Jobs ────────────────────────────────────────────────────────────────────
$router->group('/jobs', function ($router) {
    $router->get('',       [JobController::class, 'list'], ['auth', 'role:student']);
    $router->get('/{id}',  [JobController::class, 'show'], ['auth']);
});

// ─── Applications ────────────────────────────────────────────────────────────
$router->group('/applications', function ($router) {
    $router->post('',              [ApplicationController::class, 'apply'],           ['auth', 'role:student']);
    $router->get('/me',            [ApplicationController::class, 'myApplications'],  ['auth', 'role:student']);
    $router->put('/{id}/withdraw', [ApplicationController::class, 'withdraw'],        ['auth', 'role:student']);
    $router->put('/{id}/shortlist',[ApplicationController::class, 'shortlist'],       ['auth', 'role:coordinator,admin']);
});

// ─── Recruiters ──────────────────────────────────────────────────────────────
$router->group('/recruiters', function ($router) {
    // Own profile
    $router->get('/me',                         [RecruiterController::class, 'me'],              ['auth', 'role:recruiter']);
    $router->put('/me/contact',                 [RecruiterController::class, 'updateContact'],   ['auth', 'role:recruiter']);
    $router->put('/me/company',                 [RecruiterController::class, 'updateCompany'],   ['auth', 'role:recruiter']);
    $router->get('/me/lookups',                 [RecruiterController::class, 'lookups'],         ['auth', 'role:recruiter']);

    // Job management
    $router->get('/me/jobs',                    [RecruiterController::class, 'myJobs'],          ['auth', 'role:recruiter']);
    $router->post('/me/jobs',                   [RecruiterController::class, 'postJob'],         ['auth', 'role:recruiter']);
    $router->put('/me/jobs/{id}',               [RecruiterController::class, 'updateJob'],       ['auth', 'role:recruiter']);
    $router->put('/me/jobs/{id}/submit',        [RecruiterController::class, 'submitJob'],       ['auth', 'role:recruiter']);
    $router->get('/me/jobs/{id}/applications',  [RecruiterController::class, 'jobApplications'], ['auth', 'role:recruiter']);

    // Coordinator/admin views
    $router->get('',      [RecruiterController::class, 'list'],    ['auth', 'role:coordinator,admin']);
    $router->get('/{id}', [RecruiterController::class, 'getById'], ['auth', 'role:coordinator,admin']);
});

// ─── Verifications (Phase 4) ──────────────────────────────────────────────────
$router->group('/verifications', function ($router) {
    $router->get('',              [CoordinatorController::class, 'listVerifications'],  ['auth', 'role:coordinator,admin']);
    $router->get('/{id}',         [CoordinatorController::class, 'getVerification'],   ['auth', 'role:coordinator,admin']);
    $router->put('/{id}',         [CoordinatorController::class, 'updateVerification'],['auth', 'role:coordinator,admin']);
    $router->put('/{id}/assign',  [CoordinatorController::class, 'assignVerification'],['auth', 'role:coordinator,admin']);
});

// ─── Administrative (Phase 5) ────────────────────────────────────────────────
$router->group('/admin', function ($router) {
    $router->get('/sessions',              [AdminController::class, 'listSessions'],      ['auth', 'role:admin']);
    $router->post('/sessions',             [AdminController::class, 'createSession'],     ['auth', 'role:admin']);
    $router->put('/sessions/{id}/activate',[AdminController::class, 'activateSession'],   ['auth', 'role:admin']);
    $router->get('/announcements',         [AdminController::class, 'listAnnouncements'], ['auth', 'role:admin']);
    $router->post('/announcements',        [AdminController::class, 'createAnnouncement'],['auth', 'role:admin']);
    $router->delete('/announcements/{id}',  [AdminController::class, 'deleteAnnouncement'],['auth', 'role:admin']);
    $router->get('/stats',                 [AdminController::class, 'getStats'],          ['auth', 'role:admin']);
    $router->get('/users',                 [AdminController::class, 'listUsers'],          ['auth', 'role:admin']);
    $router->post('/users/coordinator',    [AdminController::class, 'createCoordinator'],  ['auth', 'role:admin']);
    $router->put('/users/{id}/status',     [AdminController::class, 'updateUserStatus'],  ['auth', 'role:admin']);
});
