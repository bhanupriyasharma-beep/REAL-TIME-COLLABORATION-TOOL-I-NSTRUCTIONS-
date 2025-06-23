-- Drop the existing database (if it exists)
DROP DATABASE IF EXISTS collab_editor;

-- Create the database
CREATE DATABASE collab_editor;
USE collab_editor;

-- Create the 'docs' table
CREATE TABLE docs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  content LONGTEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
