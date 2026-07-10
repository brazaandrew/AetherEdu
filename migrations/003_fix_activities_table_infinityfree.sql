-- Fix activities table structure for InfinityFree
-- Without foreign key constraints to avoid import errors

DROP TABLE IF EXISTS `activities`;

CREATE TABLE `activities` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `subject_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `max_score` INT NOT NULL DEFAULT 100,
  `deadline` DATETIME DEFAULT NULL,
  `created_by` INT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_subject_id` (`subject_id`),
  INDEX `idx_created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
