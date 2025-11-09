-- iSCSS schema (MySQL)
CREATE TABLE IF NOT EXISTS faculties (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(128) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS departments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(128) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  role ENUM('admin','student','teacher','staff') NOT NULL,
  login VARCHAR(64) NULL UNIQUE,
  reg_no VARCHAR(64) NULL UNIQUE,
  name VARCHAR(128) NOT NULL,
  email VARCHAR(128) NULL,
  faculty_id INT NULL,
  department_id INT NULL,
  password_hash VARCHAR(255) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_users_faculty FOREIGN KEY (faculty_id) REFERENCES faculties(id) ON DELETE SET NULL,
  CONSTRAINT fk_users_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
  CONSTRAINT chk_login_reg CHECK ((role='student' AND reg_no IS NOT NULL) OR (role IN ('admin','teacher','staff') AND login IS NOT NULL))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS threads (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type ENUM('inquiry','claim') NOT NULL,
  subject VARCHAR(255) NOT NULL,
  created_by INT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  is_closed TINYINT(1) NOT NULL DEFAULT 0,
  CONSTRAINT fk_threads_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS thread_participants (
  thread_id INT NOT NULL,
  user_id INT NOT NULL,
  PRIMARY KEY (thread_id, user_id),
  CONSTRAINT fk_tp_thread FOREIGN KEY (thread_id) REFERENCES threads(id) ON DELETE CASCADE,
  CONSTRAINT fk_tp_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  thread_id INT NOT NULL,
  sender_id INT NOT NULL,
  body TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_messages_thread FOREIGN KEY (thread_id) REFERENCES threads(id) ON DELETE CASCADE,
  CONSTRAINT fk_messages_user FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed admin if not exists (example username admin / password admin123 changed afterward)
INSERT INTO users (role, login, reg_no, name, email, password_hash, is_active)
SELECT 'admin','admin',NULL,'Administrator','admin@example.com',
       '$2y$10$yqZxIY2V0kq3mY7M3J1sMu4G1wAVgWr1X0Jt1JQ4V9bI1Kk9yYz0i',1
WHERE NOT EXISTS (SELECT 1 FROM users WHERE role='admin' AND login='admin');
-- The above hash corresponds to password: admin123
