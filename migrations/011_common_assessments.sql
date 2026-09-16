-- Common assessments: a district gives one quiz to chosen schools and grades
-- during a test window, and every answer is scored on the server.
--
-- An assessment is addressed by its own id: the quiz game page is opened with
-- ?assessment_id=, and the attempt and response rows below hold the results the
-- district and school portals report on. Deliberately no teacher_assign row is
-- involved, because that table allows a teacher only one assignment per quiz
-- (unique key on category_id + teacher_id), which would stop a district giving
-- the same quiz as an assessment twice.
--
-- Column types follow the tables they point at: districts, schools and users have
-- unsigned ids, while the older teacher_* tables have signed ones. A foreign key
-- needs the exact same type, so these are not interchangeable.
--
-- Run once against the application database:
--   mysql -u <user> -p <database> < migrations/011_common_assessments.sql

CREATE TABLE `common_assessments` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `district_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(160) NOT NULL,
    -- teacher_category.uid of the quiz being given.
    `quiz_uid` CHAR(36) NOT NULL,
    `opens_at` DATETIME NOT NULL,
    `closes_at` DATETIME NOT NULL,
    `attempts_allowed` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    -- Comma separated grades, e.g. '5,6'. NULL means every class takes part.
    `grades` VARCHAR(64) NULL,
    -- 1 active, 0 cancelled.
    `status` TINYINT NOT NULL DEFAULT 1,
    `created_by` INT UNSIGNED NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_common_assessments_district` (`district_id`, `status`),
    KEY `idx_common_assessments_window` (`opens_at`, `closes_at`),
    CONSTRAINT `fk_common_assessments_district` FOREIGN KEY (`district_id`) REFERENCES `districts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- The schools an assessment is given to.
CREATE TABLE `common_assessment_schools` (
    `assessment_id` INT UNSIGNED NOT NULL,
    `school_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`assessment_id`, `school_id`),
    KEY `idx_common_assessment_schools_school` (`school_id`),
    CONSTRAINT `fk_common_assessment_schools_assessment` FOREIGN KEY (`assessment_id`) REFERENCES `common_assessments` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_common_assessment_schools_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- One row per finished attempt. The unique key on the attempt number is what stops
-- a student going past the attempts the district allowed, even from two tabs at once.
CREATE TABLE `common_assessment_attempts` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `assessment_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `school_id` INT UNSIGNED NOT NULL,
    -- The class it counts towards, kept even if the class is renamed later.
    `class_id` INT NULL,
    `attempt_number` TINYINT UNSIGNED NOT NULL DEFAULT 1,
    `score` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `total_questions` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `questions_answered` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    `percent` DECIMAL(5,2) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_common_assessment_attempt` (`assessment_id`, `user_id`, `attempt_number`),
    KEY `idx_common_assessment_attempts_school` (`assessment_id`, `school_id`),
    KEY `idx_common_assessment_attempts_class` (`assessment_id`, `class_id`),
    CONSTRAINT `fk_common_assessment_attempts_assessment` FOREIGN KEY (`assessment_id`) REFERENCES `common_assessments` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_common_assessment_attempts_school` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_common_assessment_attempts_class` FOREIGN KEY (`class_id`) REFERENCES `teacher_classes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- One row per question in an attempt, so the reports can show which questions a
-- cohort found hard and what they answered instead. There is no key to
-- teacher_questions on purpose: deleting a question must not erase past results.
CREATE TABLE `common_assessment_responses` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `attempt_id` INT UNSIGNED NOT NULL,
    `assessment_id` INT UNSIGNED NOT NULL,
    `question_id` INT NOT NULL,
    `answer` VARCHAR(255) NOT NULL DEFAULT '',
    `is_correct` TINYINT NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_common_assessment_response` (`attempt_id`, `question_id`),
    KEY `idx_common_assessment_responses_question` (`assessment_id`, `question_id`),
    CONSTRAINT `fk_common_assessment_responses_attempt` FOREIGN KEY (`attempt_id`) REFERENCES `common_assessment_attempts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- A class grade, set in the school portal. Assessments limited to grades reach only
-- the classes whose grade matches; classes without one take part in district-wide
-- assessments only.
ALTER TABLE `teacher_classes`
    ADD COLUMN `grade` VARCHAR(16) NULL AFTER `name`;
