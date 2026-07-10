-- Student clinic/medical profile tables

-- Student medical profiles
CREATE TABLE IF NOT EXISTS student_medical_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL UNIQUE,
    blood_type VARCHAR(10),
    height_cm DECIMAL(5,2),
    weight_kg DECIMAL(5,2),
    bmi DECIMAL(4,2),
    medical_conditions TEXT,
    allergies TEXT,
    medications TEXT,
    emergency_contact_name VARCHAR(100),
    emergency_contact_phone VARCHAR(20),
    emergency_contact_relationship VARCHAR(50),
    physician_name VARCHAR(100),
    physician_phone VARCHAR(20),
    insurance_provider VARCHAR(100),
    insurance_policy_number VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Clinic visit logs
CREATE TABLE IF NOT EXISTS clinic_visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    visit_date DATE NOT NULL,
    visit_time TIME,
    complaint TEXT,
    diagnosis TEXT,
    treatment TEXT,
    medication_given TEXT,
    action_taken ENUM('treated', 'referred', 'sent_home', 'rested', 'other') DEFAULT 'treated',
    attended_by INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (attended_by) REFERENCES users(id)
);

-- Immunization records
CREATE TABLE IF NOT EXISTS immunization_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    vaccine_name VARCHAR(100) NOT NULL,
    date_administered DATE,
    dose_number INT DEFAULT 1,
    administering_facility VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);
