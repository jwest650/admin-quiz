-- School and district portal foundation.
-- Run once against the application database:
--   mysql -u <user> -p <database> < migrations/002_school_district_portal.sql

CREATE TABLE `districts` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(160) NOT NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_districts_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `schools` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `district_id` INT UNSIGNED DEFAULT NULL,
    `name` VARCHAR(160) NOT NULL,
    `code` VARCHAR(48) NOT NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_schools_code` (`code`),
    KEY `idx_schools_district` (`district_id`),
    KEY `idx_schools_status` (`status`),
    CONSTRAINT `fk_schools_district` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `school_members` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `school_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `role` ENUM('district_admin', 'school_admin', 'teacher', 'student') NOT NULL,
    `status` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_school_member` (`school_id`, `user_id`),
    KEY `idx_school_members_user_role` (`user_id`, `role`, `status`),
    CONSTRAINT `fk_school_members_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `teacher_classes`
    ADD COLUMN `school_id` INT UNSIGNED NULL AFTER `teacher_id`,
    ADD KEY `idx_teacher_classes_school` (`school_id`);

-- Existing teachers remain usable. New school-admin workflows must populate
-- school_members and school_id explicitly before data appears in the portal.
