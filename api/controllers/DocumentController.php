<?php
/**
 * DocumentController — Handles document upload/download API endpoints.
 *
 * Routes:
 *   POST   /documents/upload     → upload a document (multipart/form-data)
 *   DELETE /documents/{doc_type} → delete a specific document
 *   GET    /documents/types      → list valid document types
 *   GET    /documents/usage      → get storage usage for current user
 *   POST   /documents/url        → save a Google Drive URL as document
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Response;
use App\Services\DocumentService;

class DocumentController
{
    private DocumentService $service;

    public function __construct()
    {
        $this->service = new DocumentService();
    }

    /**
     * POST /documents/upload — Upload a document file.
     * Expects multipart/form-data with:
     *   - file: the uploaded file
     *   - doc_type: one of the valid document type keys
     */
    public function upload(array $params = [], ?array $user = null): void
    {
        $role = $user['role'] ?? '';
        $docType = $_POST['doc_type'] ?? '';

        if (empty($docType)) {
            Response::error('doc_type is required.', 400);
            return;
        }

        if (!isset($_FILES['file'])) {
            Response::error('No file provided. Send as multipart/form-data with field name "file".', 400);
            return;
        }

        if ($role === 'student') {
            $studentId = $user['sub'];

            $result = $this->service->uploadStudentDocument($studentId, $docType, $_FILES['file']);
        } else {
            Response::error('Document upload not yet supported for this role.', 400);
            return;
        }

        if ($result['success']) {
            Response::success(['path' => $result['path']], 200, $result['message']);
        } else {
            Response::error($result['message'], 422);
        }
    }

    /**
     * POST /documents/upload-attachment — Upload an attachment for an announcement
     */
    public function uploadAttachment(array $params = [], ?array $user = null): void
    {
        $role = $user['role'] ?? '';

        if (!in_array($role, ['admin', 'coordinator'], true)) {
            Response::error('Unauthorized to upload attachments.', 403);
            return;
        }

        if (!isset($_FILES['file'])) {
            Response::error('No file provided.', 400);
            return;
        }

        $result = $this->service->uploadAnnouncementAttachment($_FILES['file']);

        if ($result['success']) {
            Response::success([
                'path' => $result['path'],
                'name' => $result['name']
            ], 200, $result['message']);
        } else {
            Response::error($result['message'], 422);
        }
    }

    /**
     * DELETE /documents/{doc_type} — Delete a specific document.
     */
    public function delete(array $params = [], ?array $user = null): void
    {
        $docType = $params['doc_type'] ?? '';
        $role = $user['role'] ?? '';

        if ($role === 'student') {
            $studentId = $user['sub'];

            $result = $this->service->deleteStudentDocument($studentId, $docType);
        } else {
            Response::error('Document deletion not yet supported for this role.', 400);
            return;
        }

        if ($result['success']) {
            Response::success(null, 200, $result['message']);
        } else {
            Response::error($result['message'], 422);
        }
    }

    /**
     * GET /documents/types — Get list of valid document types for frontend form.
     */
    public function types(array $params = [], ?array $user = null): void
    {
        Response::success($this->service->getDocTypes());
    }

    /**
     * GET /documents/usage — Get storage usage stats.
     */
    public function usage(array $params = [], ?array $user = null): void
    {
        $role = $user['role'] ?? '';

        if ($role === 'student') {
            $studentId = $user['sub'];

            $result = $this->service->getStorageUsage($studentId);
            Response::success($result);
        } else {
            Response::error('Not supported for this role.', 400);
        }
    }

    /**
     * POST /documents/url — Save a URL (e.g. Google Drive link) as a document.
     * Expects JSON: { "doc_type": "resume", "url": "https://drive.google.com/..." }
     */
    public function saveUrl(array $params = [], ?array $user = null): void
    {
        $role = $user['role'] ?? '';
        $input = json_decode(file_get_contents('php://input'), true);

        $docType = $input['doc_type'] ?? '';
        $url     = $input['url'] ?? '';

        if (empty($docType) || empty($url)) {
            Response::error('doc_type and url are required.', 400);
            return;
        }

        if ($role === 'student') {
            $studentId = $user['sub'];

            $result = $this->service->saveDocumentUrl($studentId, $docType, $url);
        } else {
            Response::error('Not supported for this role.', 400);
            return;
        }

        if ($result['success']) {
            Response::success(['path' => $result['path']], 200, $result['message']);
        } else {
            Response::error($result['message'], 422);
        }
    }
}
