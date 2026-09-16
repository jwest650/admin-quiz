-- Let one person hold several roles at the same school, for example school
-- admin and teacher. Each role can still be granted only once per school.
-- Run once against the application database:
--   mysql -u <user> -p <database> < migrations/006_school_member_multiple_roles.sql

-- The new key starts with school_id, so it keeps backing fk_school_members_school
-- once the old one-role-per-school key is dropped in the same statement.
ALTER TABLE `school_members`
    ADD UNIQUE KEY `uniq_school_member_role` (`school_id`, `user_id`, `role`),
    DROP INDEX `uniq_school_member`;
