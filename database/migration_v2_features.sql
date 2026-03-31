-- ============================================================
-- Migration v2: Voice Notes, Smart Icebreakers, Spotlight Prompts
-- ============================================================

USE `dateapp`;

-- ── 1. Voice Notes: extend messages table ────────────────
ALTER TABLE `messages`
    ADD COLUMN `message_type` ENUM('text','voice') NOT NULL DEFAULT 'text' AFTER `message_text`,
    ADD COLUMN `voice_path` VARCHAR(500) NULL DEFAULT NULL AFTER `message_type`,
    ADD COLUMN `voice_duration` SMALLINT UNSIGNED NULL DEFAULT NULL AFTER `voice_path`;

-- ── 2. Spotlight Prompts ─────────────────────────────────
CREATE TABLE IF NOT EXISTS `spotlight_prompts` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `prompt`     VARCHAR(300) NOT NULL,
    `emoji`      VARCHAR(10) NOT NULL DEFAULT '💬',
    `category`   ENUM('fun','deep','creative','icebreaker') NOT NULL DEFAULT 'fun',
    `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_prompt` (`prompt`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `user_prompt_answers` (
    `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id`    INT UNSIGNED NOT NULL,
    `prompt_id`  INT UNSIGNED NOT NULL,
    `answer`     VARCHAR(500) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_upa_user`   FOREIGN KEY (`user_id`)   REFERENCES `users`(`id`)             ON DELETE CASCADE,
    CONSTRAINT `fk_upa_prompt` FOREIGN KEY (`prompt_id`) REFERENCES `spotlight_prompts`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `uq_user_prompt` (`user_id`, `prompt_id`),
    INDEX `idx_upa_user` (`user_id`)
) ENGINE=InnoDB;

-- ── Seed spotlight prompts ──────────────────────────────
INSERT IGNORE INTO `spotlight_prompts` (`prompt`, `emoji`, `category`) VALUES
('A Sunday well spent looks like...', '☀️', 'fun'),
('The way to my heart is...', '❤️', 'deep'),
('My most controversial opinion is...', '🔥', 'fun'),
('Two truths and a lie...', '🎭', 'icebreaker'),
('I'm looking for someone who...', '🔍', 'deep'),
('My ideal first date would be...', '🌹', 'icebreaker'),
('The song that describes my life right now...', '🎵', 'creative'),
('If I could travel anywhere tomorrow...', '✈️', 'fun'),
('My hidden talent is...', '🎪', 'creative'),
('I geek out about...', '🤓', 'fun'),
('The best meal I've ever had was...', '🍽️', 'fun'),
('My love language is...', '💕', 'deep'),
('In five years I see myself...', '🔮', 'deep'),
('The most spontaneous thing I've done...', '⚡', 'fun'),
('A perfect night in for me is...', '🏠', 'icebreaker'),
('My proudest accomplishment is...', '🏆', 'deep'),
('I'll know it's love when...', '✨', 'deep'),
('A life goal of mine is...', '🎯', 'creative'),
('I can't stop talking about...', '💬', 'icebreaker'),
('The hill I will die on is...', '⛰️', 'fun');
