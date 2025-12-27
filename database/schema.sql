-- Create database
CREATE DATABASE IF NOT EXISTS dolphin_crm
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE dolphin_crm;

-- Creating the user table 
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(255) NOT NULL,
    lastname VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    role VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Creating the contacts table 
CREATE TABLE contacts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100),
    firstname VARCHAR(255) NOT NULL,
    lastname VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    telephone VARCHAR(50),
    company VARCHAR(150),
    type VARCHAR(50) NOT NULL,  
    assigned_to INT,            
    created_by INT NOT NULL,    
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP 
        ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_assigned_to_user
        FOREIGN KEY (assigned_to) REFERENCES users(id)
        ON DELETE SET NULL,

    CONSTRAINT fk_created_by_user
        FOREIGN KEY (created_by) REFERENCES users(id)
        ON DELETE CASCADE
);

-- Creating the notes table 
CREATE TABLE notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contact_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_by INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_notes_contact
        FOREIGN KEY (contact_id) REFERENCES contacts(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_notes_user
        FOREIGN KEY (created_by) REFERENCES users(id)
        ON DELETE CASCADE
);

-- Insert default Admin user
-- Email: admin@project2.com
-- Password: password123
INSERT INTO users (firstname, lastname, email, password, role)
SELECT
    'Admin',
    'User',
    'admin@project2.com',
    '$2y$10$YcDIrz0uKeLx5mxanycP8uI19TNhFq4THaVXmyUP17Wud8IyCu16O',
    'Admin'
WHERE NOT EXISTS (
    SELECT 1 FROM users WHERE email = 'admin@project2.com'
);