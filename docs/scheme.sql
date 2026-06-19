-- 1. ユーザー (認証基盤)
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id VARCHAR(50) NOT NULL UNIQUE,     -- ユーザーが指定する一意のID (英数字)
    mail_address VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,          -- ハッシュ化されたパスワード
    is_active BOOLEAN DEFAULT FALSE,         -- メール認証済みか
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. プロフィール (ユーザー詳細情報)
CREATE TABLE IF NOT EXISTS profiles (
    user_id INT PRIMARY KEY,
    display_name VARCHAR(100) NOT NULL,      -- 表示名
    introduction TEXT,                       -- 自己紹介
    icon_url VARCHAR(255),                   -- アイコン画像のパス
    tags TEXT,                               -- カンマ区切りのタグ
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 3. メール認証一時トークン
CREATE TABLE IF NOT EXISTS mail_temporary (
    user_id INT PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 4. パスワードリセット要求
CREATE TABLE IF NOT EXISTS reset_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 5. ブロックリスト
CREATE TABLE IF NOT EXISTS block_list (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,                    -- ブロックした人
    blocked_user_id INT NOT NULL,            -- ブロックされた人
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (user_id, blocked_user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (blocked_user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 6. グループ
CREATE TABLE IF NOT EXISTS `groups` (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    group_icon_url VARCHAR(255),
    is_public BOOLEAN DEFAULT FALSE,         -- 公開/非公開
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX (name)
);

-- 7. グループメンバー (ロール管理)
CREATE TABLE IF NOT EXISTS group_members (
    group_id INT NOT NULL,
    user_id INT NOT NULL,
    role ENUM('owner', 'manager', 'member') DEFAULT 'member',
    joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (group_id, user_id),
    FOREIGN KEY (group_id) REFERENCES `groups`(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 8. チャットメッセージ
CREATE TABLE IF NOT EXISTS chats (
    id INT PRIMARY KEY AUTO_INCREMENT,
    group_id INT NOT NULL,
    sender_id INT NOT NULL,
    content TEXT NOT NULL,                   -- message から content に変更
    image_url VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP, -- timestamp から名称変更
    FOREIGN KEY (group_id) REFERENCES `groups`(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 9. ブログ
CREATE TABLE IF NOT EXISTS blogs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    author_id INT NOT NULL,                  -- 投稿者ID
    group_id INT NULL,                       -- group公開時の所属グループ
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,                   -- Markdown形式
    tags TEXT,                               -- カンマ区切り
    visibility ENUM('public', 'private', 'group') DEFAULT 'public',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES `groups`(id) ON DELETE SET NULL
);

-- 10. ブログ・いいね (正規化)
CREATE TABLE IF NOT EXISTS blog_likes (
    blog_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (blog_id, user_id),
    FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- 11. ブログ・コメント
CREATE TABLE IF NOT EXISTS blog_comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    blog_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,                   -- message から名称変更
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);