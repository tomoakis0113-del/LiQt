-- users
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    mail_addr VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT FALSE
);

-- mail_temporary
CREATE TABLE IF NOT EXISTS mail_temporary (
    id INT PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    FOREIGN KEY (id) REFERENCES users(id) ON DELETE CASCADE
);

-- reset_requests
CREATE TABLE IF NOT EXISTS reset_requests (
    id INT PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    FOREIGN KEY (id) REFERENCES users(id) ON DELETE CASCADE
);

-- profiles
CREATE TABLE IF NOT EXISTS profiles (
    id INT PRIMARY KEY,
    name VARCHAR(255),
    icon_url TEXT,
    tags TEXT,
    FOREIGN KEY (id) REFERENCES users(id) ON DELETE CASCADE
);

-- block_list
CREATE TABLE IF NOT EXISTS block_list (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    blocked_user_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (blocked_user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- `groups`
CREATE TABLE IF NOT EXISTS `groups` (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    owner_id INT NOT NULL,
    group_icon_url TEXT,
    is_public BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (owner_id) REFERENCES users(id)
);

-- group_members
CREATE TABLE IF NOT EXISTS group_members (
    group_id INT NOT NULL,
    user_id INT NOT NULL,
    role VARCHAR(50),
    PRIMARY KEY (group_id, user_id),
    FOREIGN KEY (group_id) REFERENCES `groups`(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- invites
CREATE TABLE IF NOT EXISTS invites (
    id INT PRIMARY KEY AUTO_INCREMENT,
    group_id INT NOT NULL,
    inviter_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    FOREIGN KEY (group_id) REFERENCES `groups`(id) ON DELETE CASCADE,
    FOREIGN KEY (inviter_id) REFERENCES users(id) ON DELETE CASCADE
);

-- chats
CREATE TABLE IF NOT EXISTS chats (
    id INT PRIMARY KEY AUTO_INCREMENT,
    message TEXT,
    group_id INT NOT NULL,
    sender_id INT NOT NULL,
    timestamp DATETIME,
    image_url TEXT,
    FOREIGN KEY (group_id) REFERENCES `groups`(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
);

-- blogs
CREATE TABLE IF NOT EXISTS blogs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tag VARCHAR(255),
    title VARCHAR(255),
    content TEXT,
    owner_id INT NOT NULL,
    good_count INT DEFAULT 0,
    view_count INT DEFAULT 0,
    created_at DATETIME,
    updated_at DATETIME DEFAULT NULL,
    is_public BOOLEAN DEFAULT TRUE,
    group_id INT NULL,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES `groups`(id) ON DELETE CASCADE
);

-- blog_comments
CREATE TABLE IF NOT EXISTS blog_comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    blog_id INT NOT NULL,
    user_id INT NOT NULL,
    message TEXT,
    FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
 