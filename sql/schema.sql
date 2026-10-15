CREATE DATABASE IF NOT EXISTS gametrade
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE gametrade;

CREATE TABLE users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '내부 식별자',
  username VARCHAR(20) NOT NULL COMMENT '로그인 아이디',
  password_hash VARCHAR(255) NOT NULL COMMENT 'password_hash() 결과',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '가입 시각',
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정 시각',
  PRIMARY KEY (id),
  UNIQUE KEY uk_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='회원';

CREATE TABLE games (
  id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '게임 식별자',
  name VARCHAR(50) NOT NULL COMMENT '게임명',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '등록 시각',
  PRIMARY KEY (id),
  UNIQUE KEY uk_games_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='게임';

CREATE TABLE game_servers (
  id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '서버 식별자',
  game_id SMALLINT UNSIGNED NOT NULL COMMENT '소속 게임',
  name VARCHAR(50) NOT NULL COMMENT '서버명',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '등록 시각',
  PRIMARY KEY (id),
  UNIQUE KEY uk_game_servers_game_name (game_id, name),
  CONSTRAINT fk_game_servers_game
    FOREIGN KEY (game_id) REFERENCES games (id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='게임 서버';

CREATE TABLE items (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '상품 식별자',
  user_id BIGINT UNSIGNED NOT NULL COMMENT '판매자',
  server_id SMALLINT UNSIGNED NOT NULL COMMENT '판매 서버',
  item_name VARCHAR(100) NOT NULL COMMENT '아이템명',
  price INT UNSIGNED NOT NULL COMMENT '판매 가격(원)',
  quantity INT UNSIGNED NOT NULL COMMENT '판매 수량',
  description TEXT NULL COMMENT '아이템 설명',
  status ENUM('ON_SALE', 'IN_PROGRESS', 'COMPLETED') NOT NULL DEFAULT 'ON_SALE' COMMENT '거래 상태',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '등록 시각',
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '수정 시각',
  PRIMARY KEY (id),
  KEY idx_items_user_created (user_id, created_at),
  KEY idx_items_server_id (server_id),
  KEY idx_items_created_at (created_at),
  KEY idx_items_price (price),
  KEY idx_items_status (status),
  CONSTRAINT fk_items_user
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT fk_items_server
    FOREIGN KEY (server_id) REFERENCES game_servers (id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT chk_items_price CHECK (price >= 1 AND price <= 100000000),
  CONSTRAINT chk_items_quantity CHECK (quantity >= 1 AND quantity <= 9999)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='판매 상품';
