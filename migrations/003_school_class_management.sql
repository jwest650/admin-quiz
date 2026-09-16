-- School-admin class management.
-- Preflight duplicates before applying:
--   SELECT school_id, name, COUNT(*) AS duplicate_count
--   FROM teacher_classes WHERE school_id IS NOT NULL
--   GROUP BY school_id, name HAVING COUNT(*) > 1;

ALTER TABLE `teacher_classes`
    ADD UNIQUE KEY `uniq_teacher_classes_school_name` (`school_id`, `name`);
