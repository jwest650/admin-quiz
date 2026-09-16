-- Class roster fixes, matching 008 for classes.
-- Run once against the application database:
--   mysql -u <user> -p <database> < migrations/009_teacher_class_students_fields.sql

-- 1. name was varchar(25), but a roster row copies the student's account name,
--    which can be up to 128 characters. With strict SQL mode, adding a student
--    with a longer name failed.
-- 2. guardian was NOT NULL with no default; it is optional information.
ALTER TABLE `teacher_class_students`
    MODIFY `name` VARCHAR(128) COLLATE utf8mb4_general_ci NOT NULL,
    MODIFY `guardian` TEXT COLLATE utf8mb4_general_ci NULL;
