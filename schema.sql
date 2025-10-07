-- Crie o banco antes: CREATE DATABASE autopecas CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
-- USE autopecas;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS suppliers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  phone VARCHAR(40),
  email VARCHAR(120)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  owner_id INT NOT NULL,
  sku VARCHAR(64) UNIQUE NOT NULL,
  name VARCHAR(150) NOT NULL,
  category VARCHAR(80),
  min_qty INT DEFAULT 0,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  supplier_id INT,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (owner_id) REFERENCES users(id),
  FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS stock_movements (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  owner_id INT NOT NULL,
  product_id INT NOT NULL,
  type ENUM('IN','OUT','ADJUST') NOT NULL,
  qty INT NOT NULL,
  reason VARCHAR(200),
  ref_code VARCHAR(80),
  created_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (owner_id) REFERENCES users(id),
  FOREIGN KEY (product_id) REFERENCES products(id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE OR REPLACE VIEW product_stock_owner AS
SELECT owner_id, product_id,
       COALESCE(SUM(CASE WHEN type='IN' THEN qty
                         WHEN type='OUT' THEN -qty
                         WHEN type='ADJUST' THEN qty
                         ELSE 0 END),0) AS stock_qty
FROM stock_movements
GROUP BY owner_id, product_id;

CREATE INDEX IF NOT EXISTS idx_products_owner ON products(owner_id);
CREATE INDEX IF NOT EXISTS idx_mov_owner_prod_date ON stock_movements(owner_id, product_id, created_at);
