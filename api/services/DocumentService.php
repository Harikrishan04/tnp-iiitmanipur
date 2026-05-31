<?php
/**
 * DocumentService — Handles file uploads and document management.
 *
 * Storage strategy:
 *   Local: uploads/{usertype}/{identifier}/{doc_type}.{ext}
 *     - Students:   uploads/students/{roll_no}/
 *     - Recruiters: uploads/recruiters/{company_slug}/
 *
 * Database: Paths stored as JSON in documents_json column.
 * Fallback: If local storage fails, accepts Google Drive URLs directly.
 */

declare(strict_types=1);

namespace App\Services;

use App\Config\Database;
use App\Helpers\Logger;

class DocumentService
{
    private \PDO $db;

    /** Base upload directory (relative to project root) */
    private string $uploadBase;

    /** Max total upload size per student in bytes (10 MB) */
    private const MAX_TOTAL_SIZE = 10 * 1024 * 1024;

    /** Allowed MIME types */
    private const ALLOWED_MIMES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    /** Max single file size (5 MB) */
    private const MAX_FILE_SIZE = 5 * 1024 * 1024;

    /** Valid document types and their labels */
    public const DOC_TYPES = [
        'photo'                 => ['label' => 'Passport Photo',          'accept' => 'image/*,.pdf'],
        '10th_marksheet'        => ['label' => '10th Marksheet',          'accept' => '.pdf'],
        '12th_marksheet'        => ['label' => '12th Marksheet',          'accept' => '.pdf'],
        'resume'                => ['label' => 'Resume',                  'accept' => '.pdf'],
        'internship_certs'      => ['label' => 'Internship Certificates', 'accept' => '.pdf'],
        'course_certs'          => ['label' => 'Course Certificates',     'accept' => '.pdf'],
        'category_certificate'  => ['label' => 'Category Certificate',    'accept' => '.pdf'],
        'pwd_certificate'       => ['label' => 'PwD Certificate',         'accept' => '.pdf'],
        'ews_certificate'       => ['label' => 'EWS Certificate',         'accept' => '.pdf'],
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
        // uploads/ is at the project root, one level above /api
        $this->uploadBase = dirname(__DIR__, 2) . '/uploads';
    }

    /**
     * Upload a file for a student.
     *
     * @param string $studentId  Student UUID
     * @param string $docType    One of DOC_TYPES keys
     * @param array  $file       $_FILES entry
     * @return array ['success' => bool, 'message' => string, 'path' => ?string]
     */
    public function uploadStudentDocument(string $studentId, string $docType, array $file): array
    {
        // 1. Validate document type
        if (!array_key_exists($docType, self::DOC_TYPES)) {
            return ['success' => false, 'message' => "Invalid document type: {$docType}"];
        }

        // 2. Validate file upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMsg = $this->uploadErrorMessage($file['error']);
            return ['success' => false, 'message' => "Upload error: {$errorMsg}"];
        }

        // 3. Validate MIME type
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        if (!in_array($mimeType, self::ALLOWED_MIMES, true)) {
            return ['success' => false, 'message' => "File type not allowed. Accepted: PDF, JPEG, PNG, WebP."];
        }

        // 4. Validate single file size
        if ($file['size'] > self::MAX_FILE_SIZE) {
            $maxMB = self::MAX_FILE_SIZE / (1024 * 1024);
            return ['success' => false, 'message' => "File exceeds maximum size of {$maxMB} MB."];
        }

        // 5. Get student roll_no for folder name
        $stmt = $this->db->prepare("SELECT s.roll_no FROM students s WHERE s.student_id = ?");
        $stmt->execute([$studentId]);
        $rollNo = $stmt->fetchColumn();

        if (!$rollNo) {
            return ['success' => false, 'message' => 'Please set your Roll Number in your profile before uploading documents.'];
        }

        // Sanitize roll_no for folder name
        $folderName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $rollNo);

        // 6. Check total size limit
        $studentDir = $this->uploadBase . '/students/' . $folderName;
        $currentSize = $this->getDirectorySize($studentDir);
        if (($currentSize + $file['size']) > self::MAX_TOTAL_SIZE) {
            $maxMB = self::MAX_TOTAL_SIZE / (1024 * 1024);
            $usedMB = round($currentSize / (1024 * 1024), 2);
            return ['success' => false, 'message' => "Total upload limit is {$maxMB} MB. You have used {$usedMB} MB. Please delete an existing document first."];
        }

        // 7. Create directory if it doesn't exist
        if (!is_dir($studentDir)) {
            if (!mkdir($studentDir, 0755, true)) {
                Logger::error('document', 'Failed to create upload directory', ['dir' => $studentDir]);
                return ['success' => false, 'message' => 'Server error: could not create upload directory.'];
            }
        }

        // 8. Determine file extension
        $ext = $this->getExtensionFromMime($mimeType);

        // 9. Build filename: {doc_type}.{ext}
        $filename = $docType . '.' . $ext;
        $destPath = $studentDir . '/' . $filename;

        // 10. Move uploaded file (overwrites existing)
        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            Logger::error('document', 'move_uploaded_file failed', ['dest' => $destPath]);
            return ['success' => false, 'message' => 'Server error: could not save file.'];
        }

        // 11. Build the web-accessible relative path
        $relativePath = '/uploads/students/' . $folderName . '/' . $filename;

        // 12. Update documents_json in students table
        $this->updateDocumentsJson($studentId, $docType, $relativePath);

        Logger::info('document', 'Student document uploaded', [
            'student_id' => $studentId,
            'doc_type'   => $docType,
            'path'       => $relativePath,
            'size'       => $file['size'],
        ]);

        return [
            'success' => true,
            'message' => self::DOC_TYPES[$docType]['label'] . ' uploaded successfully.',
            'path'    => $relativePath,
        ];
    }

    /**
     * Delete a student's document.
     */
    public function deleteStudentDocument(string $studentId, string $docType): array
    {
        if (!array_key_exists($docType, self::DOC_TYPES)) {
            return ['success' => false, 'message' => "Invalid document type: {$docType}"];
        }

        // Get current documents_json
        $stmt = $this->db->prepare("SELECT documents_json FROM students WHERE student_id = ?");
        $stmt->execute([$studentId]);
        $docsJson = $stmt->fetchColumn();
        $docs = json_decode($docsJson ?: '{}', true) ?: [];

        if (!isset($docs[$docType])) {
            return ['success' => false, 'message' => 'Document not found.'];
        }

        // Delete the physical file
        $fullPath = dirname(__DIR__, 2) . $docs[$docType];
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        // Remove from JSON
        unset($docs[$docType]);
        $stmt = $this->db->prepare("UPDATE students SET documents_json = ? WHERE student_id = ?");
        $stmt->execute([json_encode($docs, JSON_UNESCAPED_UNICODE), $studentId]);

        Logger::info('document', 'Student document deleted', [
            'student_id' => $studentId,
            'doc_type'   => $docType,
        ]);

        return ['success' => true, 'message' => 'Document deleted successfully.'];
    }

    /**
     * Save a Google Drive URL as a document (fallback when local upload is not possible).
     */
    public function saveDocumentUrl(string $studentId, string $docType, string $url): array
    {
        if (!array_key_exists($docType, self::DOC_TYPES)) {
            return ['success' => false, 'message' => "Invalid document type: {$docType}"];
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return ['success' => false, 'message' => 'Invalid URL format.'];
        }

        $this->updateDocumentsJson($studentId, $docType, $url);

        Logger::info('document', 'Student document URL saved', [
            'student_id' => $studentId,
            'doc_type'   => $docType,
            'url'        => $url,
        ]);

        return [
            'success' => true,
            'message' => self::DOC_TYPES[$docType]['label'] . ' URL saved successfully.',
            'path'    => $url,
        ];
    }

    /**
     * Get the document type config (for frontend form rendering).
     */
    public function getDocTypes(): array
    {
        return self::DOC_TYPES;
    }

    /**
     * Upload an attachment for an announcement (Admin/Coordinator).
     *
     * @param array $file $_FILES entry
     * @return array ['success' => bool, 'message' => string, 'path' => ?string, 'name' => ?string]
     */
    public function uploadAnnouncementAttachment(array $file): array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMsg = $this->uploadErrorMessage($file['error']);
            return ['success' => false, 'message' => "Upload error: {$errorMsg}"];
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        
        // Allow PDFs, Images, Word, Excel for announcements
        $allowed = array_merge(self::ALLOWED_MIMES, [
            'application/msword', 
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/zip',
            'text/csv'
        ]);

        if (!in_array($mimeType, $allowed, true)) {
            return ['success' => false, 'message' => "File type not allowed for announcements."];
        }

        if ($file['size'] > self::MAX_FILE_SIZE) {
            $maxMB = self::MAX_FILE_SIZE / (1024 * 1024);
            return ['success' => false, 'message' => "File exceeds maximum size of {$maxMB} MB."];
        }

        $dir = $this->uploadBase . '/announcements/' . date('Y/m');
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                Logger::error('document', 'Failed to create announcement upload directory', ['dir' => $dir]);
                return ['success' => false, 'message' => 'Server error: could not create directory.'];
            }
        }

        // Generate safe unique filename
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION) ?: $this->getExtensionFromMime($mimeType);
        $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
        $filename = $safeName . '_' . uniqid() . '.' . $ext;
        $destPath = $dir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            Logger::error('document', 'move_uploaded_file failed for announcement', ['dest' => $destPath]);
            return ['success' => false, 'message' => 'Server error: could not save file.'];
        }

        $relativePath = '/uploads/announcements/' . date('Y/m') . '/' . $filename;

        return [
            'success' => true,
            'message' => 'Attachment uploaded successfully.',
            'path'    => $relativePath,
            'name'    => $file['name'],
        ];
    }

    /**
     * Get storage usage for a student.
     */
    public function getStorageUsage(string $studentId): array
    {
        $stmt = $this->db->prepare("SELECT roll_no FROM students WHERE student_id = ?");
        $stmt->execute([$studentId]);
        $rollNo = $stmt->fetchColumn();

        if (!$rollNo) {
            return ['used' => 0, 'limit' => self::MAX_TOTAL_SIZE, 'percentage' => 0];
        }

        $folderName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $rollNo);
        $studentDir = $this->uploadBase . '/students/' . $folderName;
        $used = $this->getDirectorySize($studentDir);

        return [
            'used'       => $used,
            'limit'      => self::MAX_TOTAL_SIZE,
            'percentage' => round(($used / self::MAX_TOTAL_SIZE) * 100, 1),
        ];
    }

    // ─── Private Helpers ─────────────────────────────────────────────────────

    /**
     * Merge a new document path into the student's documents_json.
     */
    private function updateDocumentsJson(string $studentId, string $docType, string $path): void
    {
        $stmt = $this->db->prepare("SELECT documents_json FROM students WHERE student_id = ?");
        $stmt->execute([$studentId]);
        $current = $stmt->fetchColumn();

        $docs = json_decode($current ?: '{}', true) ?: [];
        $docs[$docType] = $path;

        $stmt = $this->db->prepare("UPDATE students SET documents_json = ? WHERE student_id = ?");
        $stmt->execute([json_encode($docs, JSON_UNESCAPED_UNICODE), $studentId]);
    }

    /**
     * Calculate the total size of a directory in bytes.
     */
    private function getDirectorySize(string $dir): int
    {
        if (!is_dir($dir)) return 0;

        $size = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir)) as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
        return $size;
    }

    /**
     * Map MIME type to file extension.
     */
    private function getExtensionFromMime(string $mime): string
    {
        return match ($mime) {
            'application/pdf' => 'pdf',
            'image/jpeg'      => 'jpg',
            'image/png'       => 'png',
            'image/webp'      => 'webp',
            default           => 'bin',
        };
    }

    /**
     * Human-readable upload error messages.
     */
    private function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE   => 'File exceeds server upload limit.',
            UPLOAD_ERR_FORM_SIZE  => 'File exceeds form upload limit.',
            UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder on server.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the upload.',
            default               => 'Unknown upload error.',
        };
    }
}
