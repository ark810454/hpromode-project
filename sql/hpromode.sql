CREATE DATABASE IF NOT EXISTS hpromode CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hpromode;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS deliveries;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS product_images;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS promotions;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS admins;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    phone VARCHAR(50) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    address VARCHAR(255) DEFAULT NULL,
    city VARCHAR(120) DEFAULT NULL,
    country VARCHAR(120) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    description TEXT DEFAULT NULL
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(180) NOT NULL,
    slug VARCHAR(180) NOT NULL UNIQUE,
    sku VARCHAR(80) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    promo_price DECIMAL(10,2) DEFAULT NULL,
    stock INT NOT NULL DEFAULT 0,
    color_options VARCHAR(255) DEFAULT NULL,
    size_options VARCHAR(255) DEFAULT NULL,
    main_image LONGTEXT NOT NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    is_new TINYINT(1) NOT NULL DEFAULT 1,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
);

CREATE TABLE product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    image_path LONGTEXT NOT NULL,
    alt_text VARCHAR(255) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 1,
    CONSTRAINT fk_product_images_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    size VARCHAR(80) DEFAULT NULL,
    color VARCHAR(80) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_cart_item (user_id, product_id, size, color),
    CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_cart_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE promotions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    code VARCHAR(80) NOT NULL UNIQUE,
    banner_text VARCHAR(255) DEFAULT NULL,
    discount_type ENUM('percent', 'fixed') NOT NULL DEFAULT 'percent',
    discount_value DECIMAL(10,2) NOT NULL,
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_number VARCHAR(80) NOT NULL UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    email VARCHAR(180) NOT NULL,
    address VARCHAR(255) NOT NULL,
    city VARCHAR(120) NOT NULL,
    country VARCHAR(120) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0,
    delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
    discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    delivery_method VARCHAR(120) NOT NULL,
    delivery_zone VARCHAR(120) DEFAULT NULL,
    payment_method VARCHAR(120) NOT NULL,
    payment_status VARCHAR(60) NOT NULL DEFAULT 'en attente',
    delivery_status VARCHAR(60) NOT NULL DEFAULT 'en attente',
    promo_code VARCHAR(80) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT DEFAULT NULL,
    product_name VARCHAR(180) NOT NULL,
    size VARCHAR(80) DEFAULT NULL,
    color VARCHAR(80) DEFAULT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    line_total DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    payment_method VARCHAR(120) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    status VARCHAR(60) NOT NULL DEFAULT 'en attente',
    transaction_reference VARCHAR(120) DEFAULT NULL,
    payment_note VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

CREATE TABLE deliveries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    delivery_method VARCHAR(120) NOT NULL,
    delivery_zone VARCHAR(120) DEFAULT NULL,
    delivery_city VARCHAR(120) DEFAULT NULL,
    fee DECIMAL(10,2) NOT NULL DEFAULT 0,
    status VARCHAR(60) NOT NULL DEFAULT 'en attente',
    tracking_number VARCHAR(120) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_deliveries_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

INSERT INTO admins (name, email, password) VALUES
('Super Admin', 'admin@hpromode.test', '$2y$10$EH2UuBl6ujcYDt8tmaaWve4RMvGTzEFGwz/5cELrBBsWhVUgHwqBm');

INSERT INTO users (first_name, last_name, email, phone, password, address, city, country) VALUES
('Amara', 'Johnson', 'client@hpromode.test', '+234 801 555 7788', '$2y$10$S3LkCUROlKHgYbWt5jlzIenAIIwPEEEtatYnaiZO.gdDiwsjxchDi', '12 Victoria Island', 'Lagos', 'Nigeria');

INSERT INTO categories (name, slug, description) VALUES
('Robes', 'robes', 'Silhouettes féminines, satinées et sophistiquées pour soirées et cérémonies.'),
('Costumes', 'costumes', 'Tailoring premium pour une allure structurée, moderne et distinguée.'),
('Sacs', 'sacs', 'Pièces signature à porter comme accessoires de caractère.'),
('Montres', 'montres', 'Montres élégantes aux finitions luxe et détails dorés.'),
('Lunettes', 'lunettes', 'Lignes graphiques et glamour pour compléter le look premium.'),
('Bijoux', 'bijoux', 'Bijoux raffinés pour illuminer chaque silhouette.');

INSERT INTO products (
    category_id, name, slug, sku, description, price, promo_price, stock,
    color_options, size_options, main_image, is_featured, is_new, is_active
) VALUES
(1, 'Robe Bordeaux Prestige', 'robe-bordeaux-prestige', 'ROB-1001', 'Robe de soirée élégante à finition satinée, pensée pour les occasions spéciales et les vitrines premium.', 185.00, 159.00, 8, 'Bordeaux, Noir', 'S, M, L', 'assets/images/robe-bordeaux.svg', 1, 1, 1),
(1, 'Robe Rose Poudré Élégance', 'robe-rose-poudre-elegance', 'ROB-1002', 'Une coupe féminine, douce et éditoriale, parfaite pour une allure glamour et raffinée.', 215.00, NULL, 6, 'Rose poudré, Ivoire', 'S, M, L', 'assets/images/robe-rose.svg', 1, 1, 1),
(2, 'Costume Bleu Royal Signature', 'costume-bleu-royal-signature', 'COS-2001', 'Costume trois pièces premium à l’esprit cérémonie et business luxe.', 260.00, 229.00, 5, 'Bleu royal, Noir', 'M, L, XL', 'assets/images/costume-bleu.svg', 1, 1, 1),
(2, 'Costume Rosé Modern Chic', 'costume-rose-modern-chic', 'COS-2002', 'Tailoring audacieux et contemporain pour une présence élégante.', 245.00, NULL, 4, 'Rose poudré, Beige', 'M, L, XL', 'assets/images/costume-rose.svg', 0, 1, 1),
(3, 'Sac Bordeaux Luxe', 'sac-bordeaux-luxe', 'SAC-3001', 'Sac structuré avec détails dorés et finition premium pour accompagner une garde-robe de prestige.', 125.00, 99.00, 12, 'Bordeaux, Noir', 'Unique', 'assets/images/sac-bordeaux.svg', 1, 1, 1),
(3, 'Sac Bleu Royal Iconic', 'sac-bleu-royal-iconic', 'SAC-3002', 'Format compact et silhouette chic pour un usage de jour comme de soirée.', 135.00, NULL, 9, 'Bleu royal, Crème', 'Unique', 'assets/images/sac-bleu.svg', 0, 1, 1),
(4, 'Montre Élégance Dorée', 'montre-elegance-doree', 'MON-4001', 'Montre minimaliste haut de gamme avec bracelet métal et cadran lumineux.', 168.00, 144.00, 11, 'Or, Argent', 'Unique', 'assets/images/montre.svg', 1, 1, 1),
(5, 'Lunettes Glamour Signature', 'lunettes-glamour-signature', 'LUN-5001', 'Monture luxueuse à forte présence visuelle, pensée pour une allure couture.', 82.00, NULL, 14, 'Noir, Bordeaux', 'Unique', 'assets/images/lunettes.svg', 0, 1, 1),
(6, 'Bracelet Lumière', 'bracelet-lumiere', 'BIJ-6001', 'Bracelet délicat, brillant et cérémoniel, avec accents lumineux discrets.', 96.00, NULL, 15, 'Or rose, Doré', 'Unique', 'assets/images/bracelet.svg', 1, 1, 1),
(6, 'Parure Cristal Couture', 'parure-cristal-couture', 'BIJ-6002', 'Bijou de soirée inspiré des vitrines couture et de la joaillerie moderne.', 140.00, 118.00, 3, 'Doré, Argent', 'Unique', 'assets/images/bracelet.svg', 0, 1, 1);

INSERT INTO product_images (product_id, image_path, alt_text, sort_order) VALUES
(1, 'assets/images/robe-bordeaux.svg', 'Robe Bordeaux Prestige vue principale', 1),
(1, 'assets/images/hero.svg', 'Inspiration éditoriale robe bordeaux', 2),
(2, 'assets/images/robe-rose.svg', 'Robe Rose Poudré Élégance', 1),
(3, 'assets/images/costume-bleu.svg', 'Costume Bleu Royal Signature', 1),
(3, 'assets/images/hero.svg', 'Inspiration éditoriale costume bleu', 2),
(4, 'assets/images/costume-rose.svg', 'Costume Rosé Modern Chic', 1),
(5, 'assets/images/sac-bordeaux.svg', 'Sac Bordeaux Luxe', 1),
(6, 'assets/images/sac-bleu.svg', 'Sac Bleu Royal Iconic', 1),
(7, 'assets/images/montre.svg', 'Montre Élégance Dorée', 1),
(8, 'assets/images/lunettes.svg', 'Lunettes Glamour Signature', 1),
(9, 'assets/images/bracelet.svg', 'Bracelet Lumière', 1),
(10, 'assets/images/bracelet.svg', 'Parure Cristal Couture', 1);

INSERT INTO promotions (title, code, banner_text, discount_type, discount_value, start_date, end_date, is_active) VALUES
('Promo de lancement HPROMODE', 'HPRO10', 'Bénéficiez de 10% sur votre première commande premium.', 'percent', 10.00, '2026-01-01', '2026-12-31', 1),
('Accessoires Signature', 'LUXE15', '15 $ de réduction sur les accessoires sélectionnés.', 'fixed', 15.00, '2026-02-01', '2026-12-31', 1);

INSERT INTO orders (
    user_id, order_number, first_name, last_name, phone, email, address, city, country,
    subtotal, delivery_fee, discount_amount, total_amount, delivery_method, delivery_zone,
    payment_method, payment_status, delivery_status, promo_code, notes
) VALUES
(1, 'HPR-20260325-10001', 'Amara', 'Johnson', '+234 801 555 7788', 'client@hpromode.test', '12 Victoria Island', 'Lagos', 'Nigeria', 258.00, 12.00, 15.00, 255.00, 'livraison standard', 'centre-ville', 'Mobile Money', 'en attente', 'en préparation', 'LUXE15', 'Commande de démonstration pour les tests.');

INSERT INTO order_items (order_id, product_id, product_name, size, color, quantity, unit_price, line_total) VALUES
(1, 5, 'Sac Bordeaux Luxe', 'Unique', 'Bordeaux', 1, 99.00, 99.00),
(1, 7, 'Montre Élégance Dorée', 'Unique', 'Or', 1, 144.00, 144.00),
(1, 9, 'Bracelet Lumière', 'Unique', 'Doré', 1, 15.00, 15.00);

INSERT INTO payments (order_id, payment_method, amount, status, transaction_reference, payment_note) VALUES
(1, 'Mobile Money', 255.00, 'en attente', 'PAY-DEMO1001', 'Paiement en attente de confirmation.');

INSERT INTO deliveries (order_id, delivery_method, delivery_zone, delivery_city, fee, status, tracking_number) VALUES
(1, 'livraison standard', 'centre-ville', 'Lagos', 12.00, 'en préparation', 'TRK-DEMO1001');
