-- TNP: Add foreign keys. Run only after cleaning orphan rows.
-- Orphans to fix first, if any:
--   SELECT event_id, event_organiser_id FROM events e WHERE NOT EXISTS (SELECT 1 FROM recruiters r WHERE r.recruiter_id = e.event_organiser_id);
--   SELECT participant_entry_id, participant_id, event_id FROM participants p WHERE NOT EXISTS (SELECT 1 FROM students s WHERE s.student_id = p.participant_id) OR NOT EXISTS (SELECT 1 FROM events e WHERE e.event_id = p.event_id);
--   SELECT user_id, role_id FROM users u WHERE NOT EXISTS (SELECT 1 FROM roles r WHERE r.id = u.role_id);

USE tnpdb;

ALTER TABLE `events`
  ADD CONSTRAINT `fk_events_organiser`
  FOREIGN KEY (`event_organiser_id`) REFERENCES `recruiters` (`recruiter_id`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `participants`
  ADD CONSTRAINT `fk_participants_student`
  FOREIGN KEY (`participant_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `participants`
  ADD CONSTRAINT `fk_participants_event`
  FOREIGN KEY (`event_id`) REFERENCES `events` (`event_id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_role`
  FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
