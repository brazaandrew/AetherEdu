-- Settings table for school configuration
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default school GPS coordinates (admin can update these)
INSERT INTO settings (setting_key, setting_value) VALUES 
    ('school_latitude', '14.1000'),
    ('school_longitude', '120.9500'),
    ('gps_radius_meters', '100')
ON DUPLICATE KEY UPDATE setting_value = setting_value;
