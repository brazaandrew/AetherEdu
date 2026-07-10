-- Add HR role to users table
ALTER TABLE users 
MODIFY COLUMN role ENUM('admin', 'teacher', 'student', 'it_personnel', 'registrar', 'librarian', 'cashier', 'nurse', 'hr') 
NOT NULL DEFAULT 'student';

-- Employee 201 File (Personal Data Sheet)
CREATE TABLE IF NOT EXISTS employee_201_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL UNIQUE,
    -- Personal Information
    date_of_birth DATE,
    place_of_birth VARCHAR(100),
    sex ENUM('male', 'female'),
    civil_status ENUM('single', 'married', 'widowed', 'separated', 'annulled'),
    citizenship VARCHAR(50),
    height_cm DECIMAL(5,2),
    weight_kg DECIMAL(5,2),
    blood_type VARCHAR(5),
    gsis_no VARCHAR(50),
    pagibig_no VARCHAR(50),
    philhealth_no VARCHAR(50),
    sss_no VARCHAR(50),
    tin_no VARCHAR(50),
    agency_employee_no VARCHAR(50),
    -- Contact
    residential_address TEXT,
    permanent_address TEXT,
    telephone_no VARCHAR(20),
    mobile_no VARCHAR(20),
    email VARCHAR(100),
    -- Family Background
    spouse_name VARCHAR(100),
    spouse_occupation VARCHAR(50),
    spouse_employer VARCHAR(100),
    spouse_business_address TEXT,
    father_name VARCHAR(100),
    father_occupation VARCHAR(50),
    mother_name VARCHAR(100),
    mother_occupation VARCHAR(50),
    -- Employment Details
    date_hired DATE,
    employment_status ENUM('permanent', 'temporary', 'contractual', 'substitute', 'part_time') DEFAULT 'permanent',
    position_title VARCHAR(100),
    department VARCHAR(50),
    salary_grade VARCHAR(20),
    monthly_salary DECIMAL(12,2),
    -- Other
    skills TEXT,
    recognitions TEXT,
    organizations TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Employee Education Background
CREATE TABLE IF NOT EXISTS employee_education (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    level ENUM('elementary', 'secondary', 'vocational', 'college', 'graduate_studies') NOT NULL,
    school_name VARCHAR(150),
    degree_course VARCHAR(100),
    year_graduated VARCHAR(10),
    highest_level VARCHAR(50),
    year_attended_from VARCHAR(10),
    year_attended_to VARCHAR(10),
    honors_received VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Employee Work Experience
CREATE TABLE IF NOT EXISTS employee_work_experience (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    date_from DATE,
    date_to DATE,
    position_title VARCHAR(100),
    department_office VARCHAR(100),
    monthly_salary DECIMAL(12,2),
    salary_grade VARCHAR(20),
    status_of_appointment ENUM('permanent', 'temporary', 'contractual', 'substitute', 'part_time'),
    gov_service TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Employee Training Programs
CREATE TABLE IF NOT EXISTS employee_trainings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    title VARCHAR(150),
    date_from DATE,
    date_to DATE,
    hours INT,
    type_of_ld ENUM('managerial', 'supervisory', 'technical', 'others'),
    conducted_by VARCHAR(150),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Employee Documents (Uploads)
CREATE TABLE IF NOT EXISTS employee_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    document_name VARCHAR(100),
    document_type ENUM('pds', 'certificate', 'transcript', 'clearance', 'contract', 'appointment', 'evaluation', 'others'),
    file_path VARCHAR(255),
    uploaded_by INT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);
