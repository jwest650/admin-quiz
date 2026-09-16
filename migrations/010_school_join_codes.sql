-- School join codes: students with a UQuiz account join a school by entering its code.
-- A school has no code (NULL) until a school admin creates one, and turning the
-- code off sets it back to NULL.
-- Run once against the application database:
--   mysql -u <user> -p <database> < migrations/010_school_join_codes.sql

ALTER TABLE `schools`
    ADD COLUMN `join_code` VARCHAR(16) NULL AFTER `code`,
    ADD UNIQUE KEY `uniq_schools_join_code` (`join_code`);
