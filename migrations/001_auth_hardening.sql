-- Auth hardening migration.
-- Run once against the admin database:
--   mysql -u <user> -p <database> < migrations/001_auth_hardening.sql

-- 1. Widen auth_pass so it can hold a modern password hash.
--    bcrypt is 60 chars and the old column was varchar(50); until this runs,
--    the application deliberately keeps writing MD5 rather than storing a
--    truncated (and therefore unusable) hash.
ALTER TABLE `authenticate`
    MODIFY `auth_pass` VARCHAR(255) NOT NULL;

-- 2. Enforce unique usernames. Login matches on auth_username, so duplicates
--    would make which account you get non-deterministic.
--    If this fails, resolve the duplicates first:
--      SELECT auth_username, COUNT(*) c FROM authenticate GROUP BY auth_username HAVING c > 1;
ALTER TABLE `authenticate`
    ADD UNIQUE KEY `uniq_auth_username` (`auth_username`);

-- 3. Activate every existing admin.
--    Login now requires status = 1. Accounts created through the admin panel
--    were inserted with status = 0 and there has never been a UI to enable
--    them, so without this step those admins would be locked out.
UPDATE `authenticate` SET `status` = 1 WHERE `status` <> 1;

-- 4. Default new rows to active, matching what the panel now inserts.
ALTER TABLE `authenticate`
    MODIFY `status` INT(11) NOT NULL DEFAULT 1;

-- After this migration each admin's stored MD5 hash is upgraded to bcrypt
-- automatically on their next successful login.
