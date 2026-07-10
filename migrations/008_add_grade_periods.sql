-- Migration: Add Grade Periods Configuration Table
-- This table manages which quarters are open for grade encoding and their deadlines

CREATE TABLE IF NOT EXISTS grade_periods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    quarter VARCHAR(2) NOT NULL COMMENT 'Q1, Q2, Q3, Q4',
    is_enabled TINYINT(1) DEFAULT 0 COMMENT '1 = enabled, 0 = disabled',
    deadline DATETIME DEFAULT NULL COMMENT 'Deadline for grade encoding',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_quarter (quarter)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default grade periods
INSERT INTO grade_periods (quarter, is_enabled, deadline) VALUES
('Q1', 0, NULL),
('Q2', 0, NULL),
('Q3', 0, NULL),
('Q4', 0, NULL);
