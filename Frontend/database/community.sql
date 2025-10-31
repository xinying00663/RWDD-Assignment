-- Community posts table for gardening projects and tips
CREATE TABLE IF NOT EXISTS community (
    Community_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    Community_title VARCHAR(255) NOT NULL,
    Community_category ENUM('projects', 'tips') NOT NULL,
    Community_contributor VARCHAR(255),
    Community_location VARCHAR(255),
    Community_media VARCHAR(500),
    Community_summary TEXT,
    Community_link VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(UserID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
