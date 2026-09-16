-- Class creation fixes.
-- Run once against the application database:
--   mysql -u <user> -p <database> < migrations/008_teacher_classes_fields.sql

-- 1. guardian was NOT NULL with no default, and neither the school portal nor the
--    teacher app sends one. With strict SQL mode every new class was rejected.
-- 2. name was varchar(25) while the portal accepts up to 160 characters, so a
--    name like "Grade 6 Science Section B" failed too.
ALTER TABLE `teacher_classes`
    MODIFY `name` VARCHAR(160) COLLATE utf8mb4_general_ci NOT NULL,
    MODIFY `guardian` TEXT COLLATE utf8mb4_general_ci NULL;
