-- Create the database
CREATE DATABASE socialmediadb;

-- Use the database
USE socialmediadb;

-- Create 'users' table
CREATE TABLE users (
    user_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(50) NOT NULL,
    password VARCHAR(144) NOT NULL,
    display_name VARCHAR(50) NOT NULL,
    join_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    prof_bio VARCHAR(144),
    prof_pic VARCHAR(512),
    role ENUM('user', 'admin') DEFAULT 'user'
);

-- Create 'posts' table
CREATE TABLE posts (
    post_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    post_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    post VARCHAR(1024) NOT NULL,
    submittedby VARCHAR(50) NOT NULL,
    rating INT(11),
    image_path VARCHAR(255),
    is_admin_post TINYINT(1) DEFAULT 0
);

-- Create 'comments' table
CREATE TABLE comments (
    comment_id INT(11) AUTO_INCREMENT PRIMARY KEY,
    post_id INT(100) NOT NULL,
    submittedby VARCHAR(512) NOT NULL,
    comment VARCHAR(1024) NOT NULL,
    comment_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(post_id) ON DELETE CASCADE
);

-- Create 'likes' table
CREATE TABLE likes (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    post_id INT(11) NOT NULL,
    username VARCHAR(255) NOT NULL,
    FOREIGN KEY (post_id) REFERENCES posts(post_id) ON DELETE CASCADE
);

