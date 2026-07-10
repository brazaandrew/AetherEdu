-- =====================================================
-- TLCA Complete Database Setup for InfinityFree
-- This script creates ALL tables and applies ALL migrations
-- Run this in phpMyAdmin SQL tab
-- =====================================================

-- =====================================================
-- 1. INITIAL TABLES (001_init.sql)
-- =====================================================

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  empidno VARCHAR(50) UNIQUE,
  name VARCHAR(255),
  email VARCHAR(255) UNIQUE,
  password_hash VARCHAR(255),
  role ENUM('admin','teacher','student','it_personnel','registrar','librarian') NOT NULL DEFAULT 'student',
  grade_level VARCHAR(20) NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  image VARCHAR(255) DEFAULT NULL,
  archived TINYINT(1) DEFAULT 0,
  middle_name VARCHAR(100) NULL,
  date_of_birth DATE NULL,
  gender ENUM('Male', 'Female') NULL,
  age INT NULL,
  place_of_birth VARCHAR(255) NULL,
  nationality VARCHAR(100) NULL,
  religion VARCHAR(100) NULL,
  home_address TEXT NULL,
  contact_number VARCHAR(20) NULL,
  father_name VARCHAR(255) NULL,
  father_occupation VARCHAR(100) NULL,
  father_contact VARCHAR(20) NULL,
  mother_name VARCHAR(255) NULL,
  mother_occupation VARCHAR(100) NULL,
  mother_contact VARCHAR(20) NULL,
  guardian_name VARCHAR(255) NULL,
  guardian_contact VARCHAR(20) NULL,
  guardian_relationship VARCHAR(50) NULL,
  last_school_attended VARCHAR(255) NULL,
  last_school_address TEXT NULL,
  school_year_completed VARCHAR(20) NULL,
  general_average VARCHAR(10) NULL,
  has_lrn BOOLEAN DEFAULT FALSE,
  lrn_number VARCHAR(20) NULL,
  is_returnee BOOLEAN DEFAULT FALSE,
  is_transfer_in BOOLEAN DEFAULT FALSE,
  has_special_needs BOOLEAN DEFAULT FALSE,
  special_needs_type VARCHAR(255) NULL,
  is_4ps_beneficiary BOOLEAN DEFAULT FALSE,
  is_indigenous BOOLEAN DEFAULT FALSE,
  indigenous_group VARCHAR(100) NULL,
  mother_tongue VARCHAR(100) NULL,
  INDEX idx_users_role (role),
  INDEX idx_users_grade_level (grade_level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subjects (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(50),
  name VARCHAR(255),
  description TEXT,
  enrollment_key VARCHAR(10) DEFAULT NULL,
  created_by INT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_enrollment_key (enrollment_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS folder_teacher (
  id INT AUTO_INCREMENT PRIMARY KEY,
  subject_id INT,
  teacher_empidno VARCHAR(50),
  assigned_by INT,
  assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS files (
  id INT AUTO_INCREMENT PRIMARY KEY,
  subject_id INT,
  original_filename VARCHAR(255),
  stored_filename VARCHAR(255),
  mime_type VARCHAR(100),
  size INT,
  uploaded_by INT,
  uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS activities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  subject_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT DEFAULT NULL,
  max_score INT NOT NULL DEFAULT 100,
  deadline DATETIME DEFAULT NULL,
  created_by INT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS activity_submissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  activity_id INT,
  student_id INT,
  answer_text TEXT,
  file_path VARCHAR(500),
  submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  score INT NULL,
  graded_by INT NULL,
  graded_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quizzes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  subject_id INT,
  title VARCHAR(255),
  instructions TEXT,
  time_limit_minutes INT DEFAULT 0,
  max_score INT DEFAULT 100,
  tab_protection_enabled TINYINT(1) DEFAULT 0,
  created_by INT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quiz_questions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  quiz_id INT,
  question_text TEXT,
  question_type ENUM('mcq','truefalse','id'),
  choices_json TEXT,
  correct_answer TEXT,
  points INT DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quiz_attempts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  quiz_id INT,
  student_id INT,
  score INT DEFAULT 0,
  auto_graded_score INT DEFAULT 0,
  needs_manual_grading TINYINT(1) DEFAULT 0,
  submitted_at DATETIME NULL,
  graded_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS quiz_answers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  attempt_id INT,
  question_id INT,
  answer_text TEXT,
  is_correct TINYINT(1) DEFAULT 0,
  requires_manual_grading TINYINT(1) DEFAULT 0,
  manual_points_awarded INT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS grades (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT,
  subject_id INT,
  activity_grade_total INT DEFAULT 0,
  quiz_grade_total INT DEFAULT 0,
  final_grade INT DEFAULT 0,
  q1_grade DECIMAL(5,2) DEFAULT NULL,
  q2_grade DECIMAL(5,2) DEFAULT NULL,
  q3_grade DECIMAL(5,2) DEFAULT NULL,
  q4_grade DECIMAL(5,2) DEFAULT NULL,
  average_grade DECIMAL(5,2) DEFAULT NULL,
  computed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT NULL,
  INDEX idx_quarterly_grades (student_id, subject_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  action VARCHAR(50),
  target_type VARCHAR(50),
  target_id INT,
  details TEXT,
  timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. ENROLLMENTS TABLE (004_add_enrollments_table.sql)
-- =====================================================

CREATE TABLE IF NOT EXISTS enrollments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  subject_id INT NOT NULL,
  enrolled_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_student_id (student_id),
  INDEX idx_subject_id (subject_id),
  UNIQUE KEY unique_student_subject_enrollment (student_id, subject_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. GRADE PERIODS TABLE (008_add_grade_periods.sql)
-- =====================================================

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
INSERT IGNORE INTO grade_periods (quarter, is_enabled, deadline) VALUES
('Q1', 0, NULL),
('Q2', 0, NULL),
('Q3', 0, NULL),
('Q4', 0, NULL);

-- =====================================================
-- 4. LIBRARY TABLES (014_add_library_tables.sql)
-- =====================================================

CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    isbn VARCHAR(20) UNIQUE,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    publisher VARCHAR(255),
    publication_year INT,
    category VARCHAR(100),
    description TEXT,
    total_copies INT DEFAULT 1,
    available_copies INT DEFAULT 1,
    shelf_location VARCHAR(50),
    status ENUM('available', 'unavailable', 'damaged', 'lost') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS book_borrowings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    student_id INT NOT NULL,
    borrowed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    due_date DATE NOT NULL,
    returned_at TIMESTAMP NULL,
    status ENUM('borrowed', 'returned', 'overdue') DEFAULT 'borrowed',
    notes TEXT,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DONE! All tables created.
-- =====================================================
