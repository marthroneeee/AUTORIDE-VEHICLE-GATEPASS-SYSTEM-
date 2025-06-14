
CREATE DATABASE IF NOT EXISTS autoride_db;
USE autoride_db;

CREATE TABLE IF NOT EXISTS vehicle_registration (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  id_number VARCHAR(50) NOT NULL,
  mobile_number VARCHAR(20) NOT NULL,
  course_year_section VARCHAR(100) NOT NULL,
  vehicle_type VARCHAR(50) NOT NULL,
  license_file VARCHAR(255),
  orcr_file VARCHAR(255),
  parent_id_file VARCHAR(255),
  proof_of_purchase_file VARCHAR(255),
  registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
