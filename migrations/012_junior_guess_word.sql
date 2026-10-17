-- Guess the Word for junior accounts: a picture, a hint, and a word to spell
-- from letter tiles.
--
-- Admins set the game up in three layers, each managed in the admin panel:
--   tbl_junior_guess_word_category  topics, e.g. "Animals"   (Topics & Levels page)
--   tbl_junior_guess_word_level     levels inside a topic    (Topics & Levels page)
--   tbl_junior_guess_word           the words, one level each (Questions page)
-- Deleting a topic deletes its levels, and deleting a level deletes its words;
-- db_operations.php removes the picture files first.
--
-- Topics have their own table rather than a junior_category type, so they never
-- show up in the Junior Quiz Zone or other screens that list junior categories.
--
-- The word is stored as the admin typed it. The game compares letters without
-- regard to case, and spaces, hyphens and apostrophes are shown, not guessed.
--
-- Safe to run more than once: existing tables are left untouched.
--
-- Run against the application database:
--   mysql -u <user> -p <database> < migrations/012_junior_guess_word.sql
-- or paste the whole file into phpMyAdmin's SQL tab with the database selected.

CREATE TABLE IF NOT EXISTS `tbl_junior_guess_word_category` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    -- languages.id when language mode is on, otherwise 0.
    `language_id` INT NOT NULL DEFAULT 0,
    `name` VARCHAR(100) NOT NULL,
    -- File name under images/guess-word/topics/. Empty when there is no picture.
    `image` VARCHAR(64) NOT NULL DEFAULT '',
    `row_order` INT UNSIGNED NOT NULL DEFAULT 0,
    -- 1 active, 0 hidden from the game.
    `status` TINYINT NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_junior_guess_word_category_play` (`status`, `language_id`, `row_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_junior_guess_word_level` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `category` INT UNSIGNED NOT NULL,
    -- Shown to the child, e.g. "Level 1" or "Easy".
    `name` VARCHAR(60) NOT NULL,
    `row_order` INT UNSIGNED NOT NULL DEFAULT 0,
    `status` TINYINT NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_junior_guess_word_level_play` (`category`, `status`, `row_order`),
    CONSTRAINT `fk_junior_guess_word_level_category` FOREIGN KEY (`category`) REFERENCES `tbl_junior_guess_word_category` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tbl_junior_guess_word` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `level_id` INT UNSIGNED NOT NULL,
    `word` VARCHAR(30) NOT NULL,
    `hint` VARCHAR(255) NOT NULL DEFAULT '',
    -- File name under images/guess-word/. Empty when there is no picture.
    `image` VARCHAR(64) NOT NULL DEFAULT '',
    `status` TINYINT NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_junior_guess_word_play` (`level_id`, `status`),
    CONSTRAINT `fk_junior_guess_word_level` FOREIGN KEY (`level_id`) REFERENCES `tbl_junior_guess_word_level` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
