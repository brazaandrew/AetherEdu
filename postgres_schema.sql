-- PostgreSQL Schema for AetherEdu (Neon DB compatible)

-- 1. Users Table
CREATE TABLE IF NOT EXISTS users (
  id SERIAL PRIMARY KEY,
  empidno VARCHAR(50) UNIQUE,
  name VARCHAR(255),
  email VARCHAR(255) UNIQUE,
  password_hash VARCHAR(255),
  role VARCHAR(50) NOT NULL,
  image VARCHAR(255) DEFAULT NULL,
  archived INT DEFAULT 0,
  tab_protection INT DEFAULT 1,
  grade_level VARCHAR(50) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Subjects Table
CREATE TABLE IF NOT EXISTS subjects (
  id SERIAL PRIMARY KEY,
  code VARCHAR(50),
  name VARCHAR(255),
  description TEXT,
  enrollment_key VARCHAR(10) DEFAULT NULL,
  grade_level VARCHAR(50) DEFAULT NULL,
  archived INT DEFAULT 0,
  created_by INT REFERENCES users(id) ON DELETE SET NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Folder Teacher Assignment Table
CREATE TABLE IF NOT EXISTS folder_teacher (
  id SERIAL PRIMARY KEY,
  subject_id INT REFERENCES subjects(id) ON DELETE CASCADE,
  teacher_empidno VARCHAR(50),
  assigned_by INT REFERENCES users(id) ON DELETE SET NULL,
  assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Files Table
CREATE TABLE IF NOT EXISTS files (
  id SERIAL PRIMARY KEY,
  subject_id INT REFERENCES subjects(id) ON DELETE CASCADE,
  original_filename VARCHAR(255),
  stored_filename VARCHAR(255),
  mime_type VARCHAR(100),
  size INT,
  uploaded_by INT REFERENCES users(id) ON DELETE SET NULL,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. Activities Table
CREATE TABLE IF NOT EXISTS activities (
  id SERIAL PRIMARY KEY,
  subject_id INT REFERENCES subjects(id) ON DELETE CASCADE,
  title VARCHAR(255) NOT NULL,
  description TEXT DEFAULT NULL,
  max_score INT NOT NULL DEFAULT 100,
  deadline TIMESTAMP DEFAULT NULL,
  created_by INT REFERENCES users(id) ON DELETE CASCADE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 6. Activity Submissions Table
CREATE TABLE IF NOT EXISTS activity_submissions (
  id SERIAL PRIMARY KEY,
  activity_id INT REFERENCES activities(id) ON DELETE CASCADE,
  student_id INT REFERENCES users(id) ON DELETE CASCADE,
  answer_text TEXT,
  file_path VARCHAR(500),
  score INT DEFAULT NULL,
  graded_by INT REFERENCES users(id) ON DELETE SET NULL,
  graded_at TIMESTAMP DEFAULT NULL,
  submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 7. Quizzes Table
CREATE TABLE IF NOT EXISTS quizzes (
  id SERIAL PRIMARY KEY,
  subject_id INT REFERENCES subjects(id) ON DELETE CASCADE,
  title VARCHAR(255),
  instructions TEXT,
  time_limit_minutes INT DEFAULT 0,
  max_score INT DEFAULT 100,
  created_by INT REFERENCES users(id) ON DELETE CASCADE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 8. Quiz Questions Table
CREATE TABLE IF NOT EXISTS quiz_questions (
  id SERIAL PRIMARY KEY,
  quiz_id INT REFERENCES quizzes(id) ON DELETE CASCADE,
  question_text TEXT,
  question_type VARCHAR(50) NOT NULL, -- mcq, truefalse, id
  choices_json TEXT,
  correct_answer TEXT,
  points INT DEFAULT 1
);

-- 9. Quiz Attempts Table
CREATE TABLE IF NOT EXISTS quiz_attempts (
  id SERIAL PRIMARY KEY,
  quiz_id INT REFERENCES quizzes(id) ON DELETE CASCADE,
  student_id INT REFERENCES users(id) ON DELETE CASCADE,
  score INT DEFAULT 0,
  auto_graded_score INT DEFAULT 0,
  needs_manual_grading INT DEFAULT 0,
  submitted_at TIMESTAMP DEFAULT NULL,
  graded_at TIMESTAMP DEFAULT NULL
);

-- 10. Quiz Answers Table
CREATE TABLE IF NOT EXISTS quiz_answers (
  id SERIAL PRIMARY KEY,
  attempt_id INT REFERENCES quiz_attempts(id) ON DELETE CASCADE,
  question_id INT REFERENCES quiz_questions(id) ON DELETE CASCADE,
  answer_text TEXT,
  is_correct INT DEFAULT 0,
  requires_manual_grading INT DEFAULT 0,
  manual_points_awarded INT DEFAULT NULL
);

-- 11. Grades Table
CREATE TABLE IF NOT EXISTS grades (
  id SERIAL PRIMARY KEY,
  student_id INT REFERENCES users(id) ON DELETE CASCADE,
  subject_id INT REFERENCES subjects(id) ON DELETE CASCADE,
  activity_grade_total INT DEFAULT 0,
  quiz_grade_total INT DEFAULT 0,
  final_grade INT DEFAULT 0,
  computed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 12. Audit Logs Table
CREATE TABLE IF NOT EXISTS audit_logs (
  id SERIAL PRIMARY KEY,
  user_id INT REFERENCES users(id) ON DELETE SET NULL,
  action VARCHAR(50),
  target_type VARCHAR(50),
  target_id INT,
  details TEXT,
  timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 13. Enrollments Table
CREATE TABLE IF NOT EXISTS enrollments (
  id SERIAL PRIMARY KEY,
  student_id INT REFERENCES users(id) ON DELETE CASCADE,
  subject_id INT REFERENCES subjects(id) ON DELETE CASCADE,
  enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  retention_status VARCHAR(50) DEFAULT 'Promoted',
  retention_updated_by INT REFERENCES users(id) ON DELETE SET NULL,
  retention_updated_at TIMESTAMP DEFAULT NULL,
  CONSTRAINT unique_student_subject_enrollment UNIQUE (student_id, subject_id)
);

-- 14. Student Enrollment Documents Table
CREATE TABLE IF NOT EXISTS student_enrollment_documents (
  id SERIAL PRIMARY KEY,
  student_id INT REFERENCES users(id) ON DELETE CASCADE,
  birth_certificate VARCHAR(255),
  psa_nso VARCHAR(255),
  sf10_form137 VARCHAR(255),
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 15. Settings Table
CREATE TABLE IF NOT EXISTS settings (
  id SERIAL PRIMARY KEY,
  setting_key VARCHAR(100) UNIQUE,
  setting_value TEXT,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 16. Library Books Table
CREATE TABLE IF NOT EXISTS library_books (
  id SERIAL PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  author VARCHAR(255),
  isbn VARCHAR(50),
  category VARCHAR(100),
  quantity INT DEFAULT 1,
  available INT DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 17. Library Transactions Table
CREATE TABLE IF NOT EXISTS library_transactions (
  id SERIAL PRIMARY KEY,
  book_id INT REFERENCES library_books(id) ON DELETE CASCADE,
  user_id INT REFERENCES users(id) ON DELETE CASCADE,
  borrowed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  due_date TIMESTAMP NOT NULL,
  returned_at TIMESTAMP DEFAULT NULL,
  status VARCHAR(50) DEFAULT 'Borrowed'
);

-- 18. Clinic Records Table
CREATE TABLE IF NOT EXISTS student_clinic_records (
  id SERIAL PRIMARY KEY,
  student_id INT REFERENCES users(id) ON DELETE CASCADE,
  medical_history TEXT,
  allergies TEXT,
  blood_type VARCHAR(10),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 19. Clinic Visits Table
CREATE TABLE IF NOT EXISTS clinic_visits (
  id SERIAL PRIMARY KEY,
  student_id INT REFERENCES users(id) ON DELETE CASCADE,
  complaint TEXT,
  treatment TEXT,
  visited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  handled_by INT REFERENCES users(id) ON DELETE SET NULL
);

-- 20. Finance Fees Table
CREATE TABLE IF NOT EXISTS finance_fees (
  id SERIAL PRIMARY KEY,
  student_id INT REFERENCES users(id) ON DELETE CASCADE,
  title VARCHAR(255) NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  paid_amount DECIMAL(10,2) DEFAULT '0.00',
  status VARCHAR(50) DEFAULT 'Unpaid',
  due_date TIMESTAMP NOT NULL
);

-- 21. Finance Transactions Table
CREATE TABLE IF NOT EXISTS finance_transactions (
  id SERIAL PRIMARY KEY,
  fee_id INT REFERENCES finance_fees(id) ON DELETE CASCADE,
  amount DECIMAL(10,2) NOT NULL,
  payment_method VARCHAR(50),
  transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  received_by INT REFERENCES users(id) ON DELETE SET NULL
);

-- 22. Finance Discounts Table
CREATE TABLE IF NOT EXISTS finance_discounts_grants (
  id SERIAL PRIMARY KEY,
  student_id INT REFERENCES users(id) ON DELETE CASCADE,
  title VARCHAR(255) NOT NULL,
  type VARCHAR(50) NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 23. Finance Negative Credits Table
CREATE TABLE IF NOT EXISTS finance_negative_credits (
  id SERIAL PRIMARY KEY,
  student_id INT REFERENCES users(id) ON DELETE CASCADE,
  amount DECIMAL(10,2) NOT NULL,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 24. HR Employee Records Table
CREATE TABLE IF NOT EXISTS hr_employee_records (
  id SERIAL PRIMARY KEY,
  user_id INT REFERENCES users(id) ON DELETE CASCADE,
  date_hired DATE NOT NULL,
  department VARCHAR(100),
  position VARCHAR(100),
  salary DECIMAL(10,2),
  status VARCHAR(50) DEFAULT 'Active'
);

-- 25. HR 201 Files Table
CREATE TABLE IF NOT EXISTS hr_201_files (
  id SERIAL PRIMARY KEY,
  user_id INT REFERENCES users(id) ON DELETE CASCADE,
  document_name VARCHAR(255) NOT NULL,
  file_path VARCHAR(255) NOT NULL,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 26. HR Leaves Table
CREATE TABLE IF NOT EXISTS hr_leaves (
  id SERIAL PRIMARY KEY,
  user_id INT REFERENCES users(id) ON DELETE CASCADE,
  leave_type VARCHAR(50) NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  reason TEXT,
  status VARCHAR(50) DEFAULT 'Pending',
  approved_by INT REFERENCES users(id) ON DELETE SET NULL
);

-- 27. Attendance Sessions Table
CREATE TABLE IF NOT EXISTS attendance_sessions (
  id SERIAL PRIMARY KEY,
  subject_id INT REFERENCES subjects(id) ON DELETE CASCADE,
  session_date DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 28. Student Attendance Records Table
CREATE TABLE IF NOT EXISTS student_attendance (
  id SERIAL PRIMARY KEY,
  student_id INT REFERENCES users(id) ON DELETE CASCADE,
  subject_id INT REFERENCES subjects(id) ON DELETE CASCADE,
  status VARCHAR(50) NOT NULL, -- Present, Absent, Late
  remarks TEXT,
  date DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default Admin User Seed
INSERT INTO users (empidno, name, email, password_hash, role) 
VALUES ('ADMIN001', 'TLCA Admin', 'admin@tlca.edu', '$2y$10$wzW4bO2Uj9wE/uC6N2XFRe4Zt9kZJdY73hMocq1v6Hn3zE4DkF7Zq', 'admin')
ON CONFLICT (empidno) DO NOTHING;
