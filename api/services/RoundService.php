<?php
/**
 * RoundService — Orchestrates the placement drive lifecycle.
 *
 * Lifecycle:
 *   Job: draft → pending → verified → opened → closed
 *   Round: draft → scheduled → ongoing → completed
 *
 * Responsibilities:
 *   openJob(jobId)               → verified → opened (coordinator/admin)
 *   closeJob(jobId)              → opened → closed
 *   addRound(jobId, data)        → Create round for a job
 *   startRound(roundId)          → scheduled/draft → ongoing
 *   enterResults(roundId, data)  → Bulk-enter round_results for participants
 *   endRound(roundId)            → ongoing → completed, updates application statuses
 *   publishResults(roundId)      → Set result_released = true
 *   selectStudents(jobId, appIds)→ Create placement records for selected students
 */

declare(strict_types=1);

namespace App\Services;

use App\Config\Database;
use App\Helpers\Logger;
use PDO;

class RoundService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ═══ JOB STATUS ═══

    /**
     * Open a verified job for student applications.
     * Transitions: verified → opened
     */
    public function openJob(string $jobId): array
    {
        $stmt = $this->db->prepare(
            "UPDATE jobs SET job_status = 'opened'
             WHERE job_id = ? AND job_status = 'verified'"
        );
        $stmt->execute([$jobId]);

        if ($stmt->rowCount() > 0) {
            Logger::info('round', "Job opened for applications", ['job_id' => $jobId]);
            return ['success' => true, 'message' => 'Job is now open for student applications.'];
        }

        return ['success' => false, 'message' => 'Job not found or not in verified status.'];
    }

    /**
     * Close a job (no more applications).
     * Transitions: opened → closed
     */
    public function closeJob(string $jobId): array
    {
        $stmt = $this->db->prepare(
            "UPDATE jobs SET job_status = 'closed'
             WHERE job_id = ? AND job_status IN ('opened','verified')"
        );
        $stmt->execute([$jobId]);

        if ($stmt->rowCount() > 0) {
            Logger::info('round', "Job closed", ['job_id' => $jobId]);
            return ['success' => true, 'message' => 'Job closed.'];
        }

        return ['success' => false, 'message' => 'Job not found or cannot be closed from its current status.'];
    }

    // ═══ ROUNDS ═══

    /**
     * Get round types for dropdowns.
     */
    public function getRoundTypes(): array
    {
        $stmt = $this->db->query("SELECT round_type_id, name, code FROM round_types WHERE is_active = 1 ORDER BY name");
        return $stmt->fetchAll();
    }

    /**
     * Get all rounds for a job.
     */
    public function getRoundsForJob(string $jobId): array
    {
        $stmt = $this->db->prepare(
            "SELECT jr.round_id, jr.round_number, jr.round_name, jr.round_status,
                    jr.scheduled_at, jr.location, jr.instructions,
                    jr.submission_deadline, jr.duration_mins, jr.max_score,
                    jr.is_finalized, jr.suggested_by,
                    rt.name AS round_type_label, rt.code AS round_type_code
             FROM job_rounds jr
             LEFT JOIN round_types rt ON rt.round_type_id = jr.round_type_id
             WHERE jr.job_id = ? AND jr.is_cancelled = 0
             ORDER BY jr.round_number ASC"
        );
        $stmt->execute([$jobId]);
        return ['success' => true, 'data' => $stmt->fetchAll()];
    }

    /**
     * Add a round to a job.
     *
     * @param string $jobId
     * @param array  $data  { round_type_id, round_name, scheduled_at, location, instructions, duration_mins, max_score }
     * @param string $suggestedBy  'recruiter' or 'admin'
     */
    public function addRound(string $jobId, array $data, string $suggestedBy = 'admin'): array
    {
        // Validate job exists
        $job = $this->db->prepare("SELECT job_id, job_status FROM jobs WHERE job_id = ?");
        $job->execute([$jobId]);
        $jobRow = $job->fetch();

        if (!$jobRow) {
            return ['success' => false, 'message' => 'Job not found.'];
        }

        if (empty($data['round_type_id'])) {
            return ['success' => false, 'message' => 'round_type_id is required.'];
        }
        if (empty($data['round_name'])) {
            return ['success' => false, 'message' => 'round_name is required.'];
        }

        // Auto-assign round_number
        $stmtNum = $this->db->prepare(
            "SELECT COALESCE(MAX(round_number), 0) + 1 AS next_num
             FROM job_rounds WHERE job_id = ? AND is_cancelled = 0"
        );
        $stmtNum->execute([$jobId]);
        $nextNum = (int) $stmtNum->fetch()['next_num'];

        $stmt = $this->db->prepare(
            "INSERT INTO job_rounds
             (job_id, round_type_id, round_number, round_name, instructions,
              scheduled_at, submission_deadline, location, duration_mins, max_score, suggested_by, round_status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'scheduled')"
        );
        $stmt->execute([
            $jobId,
            $data['round_type_id'],
            $nextNum,
            $data['round_name'],
            $data['instructions'] ?? null,
            $data['scheduled_at'] ?? null,
            $data['submission_deadline'] ?? null,
            $data['location'] ?? null,
            isset($data['duration_mins']) ? (int)$data['duration_mins'] : null,
            isset($data['max_score']) ? (float)$data['max_score'] : null,
            $suggestedBy,
        ]);

        // Get the new round_id
        $stmtId = $this->db->prepare(
            "SELECT round_id FROM job_rounds WHERE job_id = ? ORDER BY created_at DESC LIMIT 1"
        );
        $stmtId->execute([$jobId]);
        $roundId = $stmtId->fetch()['round_id'];

        Logger::info('round', "Round added", ['job_id' => $jobId, 'round_id' => $roundId, 'num' => $nextNum]);
        return ['success' => true, 'message' => "Round {$nextNum} created.", 'data' => ['round_id' => $roundId]];
    }

    /**
     * Start a round — transitions it to 'ongoing'.
     * Also creates pending round_result rows for all eligible applicants.
     */
    public function startRound(string $roundId): array
    {
        $stmt = $this->db->prepare(
            "SELECT jr.round_id, jr.job_id, jr.round_status, jr.round_number
             FROM job_rounds jr WHERE jr.round_id = ? AND jr.is_cancelled = 0"
        );
        $stmt->execute([$roundId]);
        $round = $stmt->fetch();

        if (!$round) {
            return ['success' => false, 'message' => 'Round not found.'];
        }
        if (!in_array($round['round_status'], ['draft', 'scheduled'], true)) {
            return ['success' => false, 'message' => 'Round is already ongoing or completed.'];
        }

        $this->db->beginTransaction();
        try {
            // Mark round as ongoing
            $this->db->prepare(
                "UPDATE job_rounds SET round_status = 'ongoing' WHERE round_id = ?"
            )->execute([$roundId]);

            // For round 1: all active (non-withdrawn) applicants participate
            // For round N>1: only students who passed round N-1
            if ($round['round_number'] === 1) {
                $stmtApps = $this->db->prepare(
                    "SELECT application_id, student_id FROM applications
                     WHERE job_id = ? AND status NOT IN ('withdrawn')"
                );
                $stmtApps->execute([$round['job_id']]);
            } else {
                // Students who passed the previous round
                $stmtApps = $this->db->prepare(
                    "SELECT a.application_id, a.student_id
                     FROM applications a
                     JOIN round_results rr ON rr.application_id = a.application_id
                     JOIN job_rounds jr ON jr.round_id = rr.round_id
                     WHERE jr.job_id = ?
                       AND jr.round_number = ?
                       AND rr.result = 'pass'
                       AND a.status NOT IN ('withdrawn')"
                );
                $stmtApps->execute([$round['job_id'], $round['round_number'] - 1]);
            }

            $applicants = $stmtApps->fetchAll();

            // Update application status to 'in_process'
            $this->db->prepare(
                "UPDATE applications a
                 JOIN job_rounds jr ON jr.job_id = a.job_id
                 SET a.status = 'in_process'
                 WHERE jr.round_id = ? AND a.status NOT IN ('withdrawn','not_selected')"
            )->execute([$roundId]);

            // Create pending result rows for each participant
            $stmtInsert = $this->db->prepare(
                "INSERT IGNORE INTO round_results (round_id, application_id, student_id, result)
                 VALUES (?, ?, ?, 'pending')"
            );
            foreach ($applicants as $app) {
                $stmtInsert->execute([$roundId, $app['application_id'], $app['student_id']]);
            }

            $participantCount = count($applicants);
            $this->db->commit();
            Logger::info('round', "Round started", ['round_id' => $roundId, 'participants' => $participantCount]);
            return [
                'success' => true,
                'message' => "Round started. {$participantCount} participants enrolled.",
                'data' => ['participants' => $participantCount]
            ];
        } catch (\Exception $e) {
            $this->db->rollBack();
            Logger::error('round', "Failed to start round", ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Failed to start round.'];
        }
    }

    /**
     * Enter/update results for participants in a round (bulk).
     *
     * @param string $roundId
     * @param array  $results  [ { student_id, result: 'pass'|'fail'|'absent', score, feedback }, ... ]
     * @param string $enteredBy  User ID of coordinator/admin entering results
     */
    public function enterResults(string $roundId, array $results, string $enteredBy): array
    {
        $stmt = $this->db->prepare(
            "SELECT round_id, round_status FROM job_rounds WHERE round_id = ?"
        );
        $stmt->execute([$roundId]);
        $round = $stmt->fetch();

        if (!$round || $round['round_status'] !== 'ongoing') {
            return ['success' => false, 'message' => 'Round not found or not in ongoing status.'];
        }

        if (empty($results)) {
            return ['success' => false, 'message' => 'No results provided.'];
        }

        $validResults = ['pass', 'fail', 'absent'];
        $updated = 0;

        $stmtUpdate = $this->db->prepare(
            "UPDATE round_results
             SET result = ?, score = ?, feedback = ?, entered_by = ?
             WHERE round_id = ? AND student_id = ?"
        );

        foreach ($results as $r) {
            if (empty($r['student_id'])) continue;
            $result = $r['result'] ?? 'pending';
            if (!in_array($result, $validResults, true)) continue;

            $stmtUpdate->execute([
                $result,
                isset($r['score']) ? (float)$r['score'] : null,
                $r['feedback'] ?? null,
                $enteredBy,
                $roundId,
                $r['student_id'],
            ]);
            $updated += $stmtUpdate->rowCount();
        }

        Logger::info('round', "Results entered", ['round_id' => $roundId, 'count' => $updated]);
        return ['success' => true, 'message' => "{$updated} result(s) recorded."];
    }

    /**
     * End a round — transitions to 'completed'.
     * Updates application statuses based on results.
     */
    public function endRound(string $roundId): array
    {
        $stmt = $this->db->prepare(
            "SELECT round_id, job_id, round_status FROM job_rounds WHERE round_id = ?"
        );
        $stmt->execute([$roundId]);
        $round = $stmt->fetch();

        if (!$round || $round['round_status'] !== 'ongoing') {
            return ['success' => false, 'message' => 'Round not found or not currently ongoing.'];
        }

        $this->db->beginTransaction();
        try {
            // Mark round as completed
            $this->db->prepare(
                "UPDATE job_rounds SET round_status = 'completed', is_finalized = 1,
                 finalized_at = NOW() WHERE round_id = ?"
            )->execute([$roundId]);

            // Mark failed/absent students' applications as 'not_selected'
            $this->db->prepare(
                "UPDATE applications a
                 JOIN round_results rr ON rr.application_id = a.application_id
                 SET a.status = 'not_selected'
                 WHERE rr.round_id = ? AND rr.result IN ('fail','absent')"
            )->execute([$roundId]);

            $this->db->commit();
            Logger::info('round', "Round ended", ['round_id' => $roundId]);
            return ['success' => true, 'message' => 'Round completed. Results saved.'];
        } catch (\Exception $e) {
            $this->db->rollBack();
            Logger::error('round', "Failed to end round", ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Failed to end round.'];
        }
    }

    /**
     * Publish round results — makes them visible to students.
     */
    public function publishResults(string $roundId, string $releasedBy): array
    {
        $stmt = $this->db->prepare(
            "SELECT round_id, round_status FROM job_rounds WHERE round_id = ?"
        );
        $stmt->execute([$roundId]);
        $round = $stmt->fetch();

        if (!$round || $round['round_status'] !== 'completed') {
            return ['success' => false, 'message' => 'Round must be completed before publishing results.'];
        }

        $this->db->prepare(
            "UPDATE round_results
             SET result_released = 1, released_at = NOW(), released_by = ?
             WHERE round_id = ?"
        )->execute([$releasedBy, $roundId]);

        Logger::info('round', "Results published", ['round_id' => $roundId]);
        return ['success' => true, 'message' => 'Results published to students.'];
    }

    /**
     * Select final students for a job — creates placement records.
     *
     * @param string   $jobId
     * @param string[] $applicationIds  Applications to mark as selected
     * @param array    $data            { actual_ctc_lpa, offer_date, joining_date, session_id }
     * @param string   $recordedBy      User ID of coordinator/admin
     */
    public function selectStudents(string $jobId, array $applicationIds, array $data, string $recordedBy): array
    {
        if (empty($applicationIds)) {
            return ['success' => false, 'message' => 'No applications selected.'];
        }

        // Validate job
        $stmt = $this->db->prepare(
            "SELECT j.job_id, j.ctc_lpa, j.session_id,
                    JSON_UNQUOTE(JSON_EXTRACT(r.company_details_json,'$.name')) AS company_name
             FROM jobs j JOIN recruiters r ON r.recruiter_id = j.recruiter_id
             WHERE j.job_id = ?"
        );
        $stmt->execute([$jobId]);
        $job = $stmt->fetch();

        if (!$job) {
            return ['success' => false, 'message' => 'Job not found.'];
        }

        $this->db->beginTransaction();
        try {
            $placed = 0;
            foreach ($applicationIds as $appId) {
                // Get student from application
                $stmtApp = $this->db->prepare(
                    "SELECT a.application_id, a.student_id FROM applications a
                     WHERE a.application_id = ? AND a.job_id = ? AND a.status NOT IN ('withdrawn')"
                );
                $stmtApp->execute([$appId, $jobId]);
                $app = $stmtApp->fetch();
                if (!$app) continue;

                // Update application status to 'selected'
                $this->db->prepare(
                    "UPDATE applications SET status = 'selected' WHERE application_id = ?"
                )->execute([$appId]);

                // Create placement record (ON DUPLICATE KEY UPDATE for safety)
                $this->db->prepare(
                    "INSERT INTO placements
                     (student_id, job_id, application_id, placement_type,
                      company_name_manual, actual_ctc_lpa, offer_date, joining_date,
                      offer_status, session_id, recorded_by)
                     VALUES (?, ?, ?, 'campus', ?, ?, ?, ?, 'offered', ?, ?)
                     ON DUPLICATE KEY UPDATE
                       actual_ctc_lpa = VALUES(actual_ctc_lpa),
                       offer_status   = 'offered',
                       recorded_by    = VALUES(recorded_by)"
                )->execute([
                    $app['student_id'],
                    $jobId,
                    $appId,
                    $job['company_name'],
                    $data['actual_ctc_lpa'] ?? $job['ctc_lpa'],
                    $data['offer_date'] ?? null,
                    $data['joining_date'] ?? null,
                    $data['session_id'] ?? $job['session_id'],
                    $recordedBy,
                ]);

                // Mark student as placed
                $this->db->prepare(
                    "UPDATE students SET placement_status = 'placed' WHERE student_id = ?"
                )->execute([$app['student_id']]);

                $placed++;
            }

            $this->db->commit();
            Logger::info('round', "Students selected/placed", ['job_id' => $jobId, 'count' => $placed]);
            return ['success' => true, 'message' => "{$placed} student(s) selected and placement record created."];
        } catch (\Exception $e) {
            $this->db->rollBack();
            Logger::error('round', "Failed to select students", ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Failed to process selections.'];
        }
    }

    /**
     * Get round results (for coordinator view).
     */
    public function getRoundResults(string $roundId): array
    {
        $stmt = $this->db->prepare(
            "SELECT rr.result_id, rr.result, rr.score, rr.feedback,
                    rr.result_released, rr.released_at,
                    s.name AS student_name, s.roll_no, s.cpi,
                    u.email AS student_email,
                    a.application_id, a.status AS application_status
             FROM round_results rr
             JOIN students s ON s.student_id = rr.student_id
             JOIN users u ON u.user_id = rr.student_id
             JOIN applications a ON a.application_id = rr.application_id
             WHERE rr.round_id = ?
             ORDER BY s.name ASC"
        );
        $stmt->execute([$roundId]);
        return ['success' => true, 'data' => $stmt->fetchAll()];
    }

    /**
     * Get student's round results (for student view — only released ones).
     */
    public function getMyRoundResults(string $studentId, string $jobId): array
    {
        $stmt = $this->db->prepare(
            "SELECT rr.result, rr.score, rr.feedback, rr.released_at,
                    jr.round_number, jr.round_name,
                    rt.name AS round_type_label
             FROM round_results rr
             JOIN job_rounds jr ON jr.round_id = rr.round_id
             LEFT JOIN round_types rt ON rt.round_type_id = jr.round_type_id
             WHERE rr.student_id = ? AND jr.job_id = ?
               AND rr.result_released = 1
             ORDER BY jr.round_number ASC"
        );
        $stmt->execute([$studentId, $jobId]);
        return ['success' => true, 'data' => $stmt->fetchAll()];
    }
}
