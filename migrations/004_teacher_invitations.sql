-- Secure teacher invitation records.
-- Raw invitation tokens are never stored; the application stores SHA-256 hashes.

CREATE TABLE `teacher_invitations` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `school_id` INT UNSIGNED NOT NULL,
    `invited_by` INT UNSIGNED NOT NULL,
    `email` VARCHAR(128) NOT NULL,
    `token_hash` CHAR(64) NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `accepted_at` DATETIME DEFAULT NULL,
    `accepted_user_id` INT UNSIGNED DEFAULT NULL,
    `revoked_at` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_teacher_invitation_token` (`token_hash`),
    KEY `idx_teacher_invitation_lookup` (`school_id`, `email`, `expires_at`),
    KEY `idx_teacher_invitation_status` (`school_id`, `accepted_at`, `revoked_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
