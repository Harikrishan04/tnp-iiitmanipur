<?php
/**
 * StudentService — Business logic for student operations.
 *
 * Methods:
 *   getProfile(studentId)            → Get full profile
 *   updateProfile(studentId, data)   → Validate + update profile
 *   getDepartments()                 → List all departments (for dropdowns)
 *   getPrograms()                    → List all programs (for dropdowns)
 */

declare(strict_types=1);

namespace App\Services;

use App\Config\Database;
use App\Helpers\Logger;
use App\Models\StudentModel;
use PDO;

class StudentService
{
    private PDO $db;
    private StudentModel $studentModel;

    public function __construct()
    {
        $this->db           = Database::getInstance();
        $this->studentModel = new StudentModel($this->db);
    }

    /**
     * Get a student's full profile.
     *
     * @param string $studentId UUID (= user_id)
     * @return array ['success' => bool, 'data' => array|null, 'message' => string]
     */
    public function getProfile(string $studentId): array
    {
        $profile = $this->studentModel->findById($studentId);

        if (!$profile) {
            return ['success' => false, 'message' => 'Student profile not found.'];
        }

        // Remove sensitive/internal fields before returning
        unset($profile['user_phone']);

        return ['success' => true, 'data' => $profile];
    }

    /**
     * Update a student's profile.
     * Validates data before persisting.
     *
     * @param string $studentId UUID
     * @param array  $data      Fields to update
     * @return array ['success' => bool, 'message' => string]
     */
    public function updateProfile(string $studentId, array $data): array
    {
        // JSON-encode any fields that are passed as arrays (from frontend)
        $jsonFields = [
            'education_details_json', 'experiences_json',
            'skills_json', 'personal_links_json',
            'family_info_json', 'documents_json',
        ];
        foreach ($jsonFields as $jf) {
            if (isset($data[$jf]) && is_array($data[$jf])) {
                $data[$jf] = json_encode($data[$jf], JSON_UNESCAPED_UNICODE);
            }
        }

        $errors = $this->validateProfileData($data);
        if (!empty($errors)) {
            return ['success' => false, 'message' => 'Validation failed.', 'errors' => $errors];
        }

        $updated = $this->studentModel->updateProfile($studentId, $data);

        // Fetch the updated profile to check completion
        $profile = $this->studentModel->findById($studentId);
        if ($profile) {
            // Required fields for profile_completed flag (phone is in users table, checked via user_phone alias)
            $required = ['name', 'roll_no', 'dept_id', 'program_id', 'cpi', 'current_semester', 'date_of_birth'];
            $completed = true;
            foreach ($required as $field) {
                if ($profile[$field] === null || $profile[$field] === '') {
                    $completed = false;
                    break;
                }
            }

            // Update profile_completed in DB if it changed
            $currentCompleted = (bool) $profile['profile_completed'];
            if ($completed !== $currentCompleted) {
                $stmt = $this->db->prepare("UPDATE students SET profile_completed = ? WHERE student_id = ?");
                $stmt->execute([$completed ? 1 : 0, $studentId]);
            }

            // Automatically set verification to 'pending' if it was 'draft' and profile is completed
            if ($completed) {
                $stmtVerif = $this->db->prepare(
                    "UPDATE verifications SET status = 'pending' WHERE entity_id = ? AND entity_type = 'student' AND status = 'draft'"
                );
                $stmtVerif->execute([$studentId]);
            } else {
                $stmtVerif = $this->db->prepare(
                    "UPDATE verifications SET status = 'draft' WHERE entity_id = ? AND entity_type = 'student' AND status = 'pending'"
                );
                $stmtVerif->execute([$studentId]);
            }
        }

        if ($updated) {
            Logger::info('student', "Profile updated", ['student_id' => $studentId]);
            return ['success' => true, 'message' => 'Profile updated successfully.'];
        }

        return ['success' => true, 'message' => 'No changes detected.'];
    }

    /**
     * Get all departments for dropdown selectors.
     *
     * @return array
     */
    public function getDepartments(): array
    {
        $stmt = $this->db->query("SELECT dept_id, name, code FROM departments ORDER BY name");
        return $stmt->fetchAll();
    }

    /**
     * Get all programs for dropdown selectors.
     *
     * @return array
     */
    public function getPrograms(): array
    {
        $stmt = $this->db->query("SELECT program_id, name, code, duration_years FROM programs ORDER BY name");
        return $stmt->fetchAll();
    }

    /**
     * Validate profile update data.
     *
     * @param array $data
     * @return array Errors (empty if valid)
     */
    private function validateProfileData(array $data): array
    {
        $errors = [];

        if (isset($data['cpi'])) {
            $cpi = (float) $data['cpi'];
            if ($cpi < 0 || $cpi > 10) {
                $errors['cpi'] = 'CPI must be between 0 and 10.';
            }
        }

        if (isset($data['current_semester'])) {
            $sem = (int) $data['current_semester'];
            if ($sem < 1 || $sem > 10) {
                $errors['current_semester'] = 'Semester must be between 1 and 10.';
            }
        }

        if (isset($data['phone']) && !empty($data['phone'])) {
            if (!preg_match('/^[0-9]{10,15}$/', $data['phone'])) {
                $errors['phone'] = 'Phone must be 10-15 digits.';
            }
        }

        if (isset($data['date_of_birth']) && !empty($data['date_of_birth'])) {
            $dob = strtotime($data['date_of_birth']);
            if ($dob === false || $dob > time()) {
                $errors['date_of_birth'] = 'Invalid date of birth.';
            }
        }

        return $errors;
    }
}
