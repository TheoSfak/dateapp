-- DateApp Database Schema
-- Phase 1: Foundation

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `dateapp`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `dateapp`;

-- ============================================================
-- USERS – core account data and credentials
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
    `id`                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email`               VARCHAR(255) NOT NULL UNIQUE,
    `password_hash`       VARCHAR(255) NOT NULL,
    `email_verified_at`   DATETIME     NULL DEFAULT NULL,
    `verification_token`  VARCHAR(64)  NULL DEFAULT NULL,
    `is_premium`          TINYINT(1)   NOT NULL DEFAULT 0,
    `is_verified`         TINYINT(1)   NOT NULL DEFAULT 0,
    `role`                ENUM('user','admin') NOT NULL DEFAULT 'user',
    `status`              ENUM('active','suspended','banned') NOT NULL DEFAULT 'active',
    `last_login_at`       DATETIME     NULL DEFAULT NULL,
    `last_active_at`      DATETIME     NULL DEFAULT NULL,
    `created_at`          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX `idx_users_email` (`email`),
    INDEX `idx_users_status` (`status`)
) ENGINE=InnoDB;

-- ============================================================
-- PROFILES – user details and demographics
-- ============================================================
CREATE TABLE IF NOT EXISTS `profiles` (
    `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`         INT UNSIGNED NOT NULL UNIQUE,
    `name`            VARCHAR(100) NOT NULL DEFAULT '',
    `bio`             TEXT         NULL,
    `date_of_birth`   DATE         NULL,
    `gender`          ENUM('male','female','non-binary','other') NULL,
    `looking_for`     ENUM('male','female','everyone')           NULL,
    `relationship_goal` ENUM('long-term','short-term','friendship','casual','undecided') NULL DEFAULT 'undecided',
    `height_cm`       SMALLINT UNSIGNED NULL,
    `smoking`         ENUM('never','sometimes','regularly') NULL,
    `drinking`        ENUM('never','sometimes','regularly') NULL,
    `latitude`        DECIMAL(10,7) NULL,
    `longitude`       DECIMAL(10,7) NULL,
    `city`            VARCHAR(100)  NULL,
    `country`         VARCHAR(100)  NULL,
    `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT `fk_profiles_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_profiles_gender` (`gender`),
    INDEX `idx_profiles_location` (`latitude`, `longitude`)
) ENGINE=InnoDB;

-- ============================================================
-- PHOTOS – uploaded user images
-- ============================================================
CREATE TABLE IF NOT EXISTS `photos` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`     INT UNSIGNED NOT NULL,
    `file_path`   VARCHAR(500) NOT NULL,
    `is_primary`  TINYINT(1)   NOT NULL DEFAULT 0,
    `uploaded_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT `fk_photos_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_photos_user` (`user_id`)
) ENGINE=InnoDB;

-- ============================================================
-- INTERACTIONS – likes, dislikes, super-likes
-- ============================================================
CREATE TABLE IF NOT EXISTS `interactions` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `actor_id`    INT UNSIGNED NOT NULL,
    `target_id`   INT UNSIGNED NOT NULL,
    `action_type` ENUM('like','dislike','superlike') NOT NULL,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT `fk_interactions_actor`  FOREIGN KEY (`actor_id`)  REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_interactions_target` FOREIGN KEY (`target_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uq_interaction` (`actor_id`, `target_id`),
    INDEX `idx_interactions_target` (`target_id`)
) ENGINE=InnoDB;

-- ============================================================
-- MATCHES – created when two users mutually like each other
-- ============================================================
CREATE TABLE IF NOT EXISTS `matches` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_1_id`   INT UNSIGNED NOT NULL,
    `user_2_id`   INT UNSIGNED NOT NULL,
    `matched_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT `fk_matches_user1` FOREIGN KEY (`user_1_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_matches_user2` FOREIGN KEY (`user_2_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uq_match` (`user_1_id`, `user_2_id`),
    INDEX `idx_matches_user2` (`user_2_id`)
) ENGINE=InnoDB;

-- ============================================================
-- MESSAGES – chat logs between matched users
-- ============================================================
CREATE TABLE IF NOT EXISTS `messages` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `match_id`     INT UNSIGNED NOT NULL,
    `sender_id`    INT UNSIGNED NOT NULL,
    `message_text` TEXT         NOT NULL,
    `is_read`      TINYINT(1)  NOT NULL DEFAULT 0,
    `read_at`      DATETIME    NULL DEFAULT NULL,
    `sent_at`      DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT `fk_messages_match`  FOREIGN KEY (`match_id`)  REFERENCES `matches`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_messages_sender` FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_messages_match` (`match_id`),
    INDEX `idx_messages_sender` (`sender_id`)
) ENGINE=InnoDB;

-- ============================================================
-- REPORTS – user reports for moderation
-- ============================================================
CREATE TABLE IF NOT EXISTS `reports` (
    `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `reporter_id`   INT UNSIGNED NOT NULL,
    `reported_id`   INT UNSIGNED NOT NULL,
    `reason`        VARCHAR(500) NOT NULL,
    `status`        ENUM('pending','reviewed','dismissed') NOT NULL DEFAULT 'pending',
    `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT `fk_reports_reporter` FOREIGN KEY (`reporter_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_reports_reported` FOREIGN KEY (`reported_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- BLOCKS – user blocks
-- ============================================================
CREATE TABLE IF NOT EXISTS `blocks` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `blocker_id`  INT UNSIGNED NOT NULL,
    `blocked_id`  INT UNSIGNED NOT NULL,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT `fk_blocks_blocker` FOREIGN KEY (`blocker_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_blocks_blocked` FOREIGN KEY (`blocked_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uq_block` (`blocker_id`, `blocked_id`)
) ENGINE=InnoDB;

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
-- USER_INTERESTS – user-to-interest junction
-- ============================================================
CREATE TABLE IF NOT EXISTS `user_interests` (
    `user_id`     INT UNSIGNED NOT NULL,
    `interest_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`user_id`, `interest_id`),
    CONSTRAINT `fk_ui_user`     FOREIGN KEY (`user_id`)     REFERENCES `users`(`id`)     ON DELETE CASCADE,
    CONSTRAINT `fk_ui_interest` FOREIGN KEY (`interest_id`) REFERENCES `interests`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- USER_SCORES – soft ELO and behavioral signals
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
-- VERIFICATION_REQUESTS – photo identity verification
-- ============================================================
CREATE TABLE IF NOT EXISTS `verification_requests` (
    `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`     INT UNSIGNED NOT NULL,
    `gesture`     VARCHAR(50)  NOT NULL,
    `photo_path`  VARCHAR(500) NOT NULL,
    `status`      ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    `reviewed_by` INT UNSIGNED NULL DEFAULT NULL,
    `reviewed_at` DATETIME     NULL DEFAULT NULL,
    `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_vr_user`     FOREIGN KEY (`user_id`)     REFERENCES `users`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_vr_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    INDEX `idx_vr_status` (`status`)
) ENGINE=InnoDB;

-- ============================================================
-- AVAILABILITY_SLOTS – recurring free-time blocks for dates
-- ============================================================
CREATE TABLE IF NOT EXISTS `availability_slots` (
    `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`      INT UNSIGNED    NOT NULL,
    `day_of_week`  TINYINT UNSIGNED NOT NULL COMMENT '0=Mon … 6=Sun',
    `start_time`   TIME NOT NULL,
    `end_time`     TIME NOT NULL,
    `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_as_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_as_user` (`user_id`)
) ENGINE=InnoDB;

-- ============================================================
-- PROFILE_BOOSTS – consumable visibility multiplier
-- ============================================================
CREATE TABLE IF NOT EXISTS `profile_boosts` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id`     INT UNSIGNED NOT NULL,
    `started_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `expires_at`  DATETIME NOT NULL,
    `multiplier`  DECIMAL(3,1) NOT NULL DEFAULT 3.0,
    CONSTRAINT `fk_pb_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_pb_user` (`user_id`),
    INDEX `idx_pb_expires` (`expires_at`)
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
