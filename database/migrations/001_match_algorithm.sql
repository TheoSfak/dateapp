-- Match Algorithm Migration
-- Adds: interests, user_interests, user_scores, user_dealbreakers tables
-- Alters: users (last_active_at)

USE `dateapp`;

-- ============================================================
-- INTERESTS – predefined tag catalog
-- ============================================================
CREATE TABLE IF NOT EXISTS `interests` (
    `id`       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name`     VARCHAR(50)  NOT NULL,
    `emoji`    VARCHAR(10)  NOT NULL DEFAULT '',
    `category` VARCHAR(30)  NOT NULL,
    UNIQUE KEY `uq_interest_name` (`name`)
) ENGINE=InnoDB;

-- ============================================================
-- USER_INTERESTS – junction table (many-to-many)
-- ============================================================
CREATE TABLE IF NOT EXISTS `user_interests` (
    `user_id`     INT UNSIGNED NOT NULL,
    `interest_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`user_id`, `interest_id`),
    CONSTRAINT `fk_ui_user`     FOREIGN KEY (`user_id`)     REFERENCES `users`(`id`)     ON DELETE CASCADE,
    CONSTRAINT `fk_ui_interest` FOREIGN KEY (`interest_id`) REFERENCES `interests`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- USER_SCORES – soft ELO + behavioral signals
-- ============================================================
CREATE TABLE IF NOT EXISTS `user_scores` (
    `user_id`              INT UNSIGNED PRIMARY KEY,
    `elo_score`            DECIMAL(8,2)  NOT NULL DEFAULT 1000.00,
    `like_ratio`           DECIMAL(5,4)  NOT NULL DEFAULT 0.5000,
    `profile_completeness` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `response_rate`        DECIMAL(5,4)  NOT NULL DEFAULT 1.0000,
    `photo_count`          TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `updated_at`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_scores_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- USER_DEALBREAKERS – configurable hard filters
-- ============================================================
CREATE TABLE IF NOT EXISTS `user_dealbreakers` (
    `id`      INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `field`   VARCHAR(30)  NOT NULL,
    `value`   VARCHAR(100) NOT NULL,
    CONSTRAINT `fk_db_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uq_dealbreaker` (`user_id`, `field`)
) ENGINE=InnoDB;

-- ============================================================
-- ALTER users – add last_active_at
-- ============================================================
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `last_active_at` DATETIME NULL DEFAULT NULL AFTER `last_login_at`;
CREATE INDEX IF NOT EXISTS `idx_users_last_active` ON `users` (`last_active_at`);

-- ============================================================
-- Seed default user_scores for existing users
-- ============================================================
INSERT IGNORE INTO `user_scores` (`user_id`)
SELECT `id` FROM `users`;
