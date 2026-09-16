-- District admins belong to a district, not to one school: they see every
-- active school in their district, including schools added later.
-- Run once against the application database:
--   mysql -u <user> -p <database> < migrations/007_district_members.sql

CREATE TABLE `district_members` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `district_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_district_member` (`district_id`, `user_id`),
    KEY `idx_district_members_user` (`user_id`, `status`),
    CONSTRAINT `fk_district_members_district` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Move district admins who were added school by school onto their district.
INSERT IGNORE INTO `district_members` (`district_id`, `user_id`, `status`)
SELECT s.`district_id`, sm.`user_id`, sm.`status`
FROM `school_members` sm
INNER JOIN `schools` s ON s.`id` = sm.`school_id`
WHERE sm.`role` = 'district_admin' AND s.`district_id` IS NOT NULL;

DELETE sm FROM `school_members` sm
INNER JOIN `schools` s ON s.`id` = sm.`school_id`
WHERE sm.`role` = 'district_admin' AND s.`district_id` IS NOT NULL;
