-- Table: admins
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    role ENUM('master', 'regular') NOT NULL DEFAULT 'regular',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);
CREATE TABLE admin_category_access (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    category_id INT NOT NULL,
    FOREIGN KEY (admin_id) REFERENCES admins(id),
    FOREIGN KEY (category_id) REFERENCES categories(id)
);
-- Table: categories
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_en VARCHAR(100) NOT NULL,
    name_hi VARCHAR(100),
    name_gu VARCHAR(100),
    icon VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- Table: notices
CREATE TABLE notices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title_en VARCHAR(255) NOT NULL,
    title_hi VARCHAR(255),
    title_gu VARCHAR(255),
    description_en TEXT NOT NULL,
    description_hi TEXT,
    description_gu TEXT,
    category_id INT NOT NULL,
    admin_id INT NOT NULL,
    published_at DATETIME NOT NULL,
    expiry_at DATETIME NOT NULL,
    is_deleted BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (admin_id) REFERENCES admins(id)
);

-- Table: settings
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    village_name VARCHAR(255) NOT NULL,
    theme_color VARCHAR(20) DEFAULT '#22c55e',
    default_language ENUM('en', 'hi', 'gu') DEFAULT 'en',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table: languages
CREATE TABLE languages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(5) NOT NULL UNIQUE, -- en, hi, gu
    name VARCHAR(50) NOT NULL,
    translations JSON NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table: logs
CREATE TABLE logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    action VARCHAR(255) NOT NULL,
    details TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id)
); 