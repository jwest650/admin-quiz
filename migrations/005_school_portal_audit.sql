-- Audit trail for school portal administrator actions.

CREATE TABLE `school_portal_audit_logs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `school_id` INT UNSIGNED NOT NULL,
    `actor_user_id` INT UNSIGNED NOT NULL,
    `event_name` VARCHAR(64) NOT NULL,
    `subject_type` VARCHAR(32) NOT NULL,
    `subject_id` BIGINT UNSIGNED DEFAULT NULL,
    `metadata` JSON DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_school_audit_recent` (`school_id`, `created_at`),
    KEY `idx_school_audit_subject` (`school_id`, `subject_type`, `subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
