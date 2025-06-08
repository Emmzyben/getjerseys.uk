-- Create database
CREATE DATABASE IF NOT EXISTS getjerseys_db;
USE getjerseys_db;

-- Create admins table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin (username: admin, password: admin123)
INSERT INTO admins (username, email, password) VALUES 
('admin', 'admin@getjerseys.com', '$2y$10$uKF0HrRmAv5eiSZi1ZiQneE4AY0a9VnAgxfWI2CKuKCYqcSF0TIpy');

-- Create categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('national', 'club') NOT NULL,
    parent_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Insert sample categories
INSERT INTO categories (name, type) VALUES 
('Europe', 'national'),
('South America', 'national'),
('North America', 'national'),
('Africa', 'national'),
('Asia', 'national'),
('Oceania', 'national'),
('Premier League', 'club'),
('La Liga', 'club'),
('Bundesliga', 'club'),
('Serie A', 'club'),
('Ligue 1', 'club'),
('Other Leagues', 'club');

-- Create teams table
CREATE TABLE IF NOT EXISTS teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category_id INT NOT NULL,
    category_type ENUM('national', 'club') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Insert sample teams
INSERT INTO teams (name, category_id, category_type) VALUES 
-- National Teams (Europe)
('France', 1, 'national'),
('Germany', 1, 'national'),
('England', 1, 'national'),
('Spain', 1, 'national'),
('Italy', 1, 'national'),
-- National Teams (South America)
('Brazil', 2, 'national'),
('Argentina', 2, 'national'),
('Uruguay', 2, 'national'),
('Colombia', 2, 'national'),
('Chile', 2, 'national'),
-- Club Teams (Premier League)
('Manchester United', 7, 'club'),
('Liverpool', 7, 'club'),
('Chelsea', 7, 'club'),
('Manchester City', 7, 'club'),
('Arsenal', 7, 'club'),
-- Club Teams (La Liga)
('Real Madrid', 8, 'club'),
('Barcelona', 8, 'club'),
('Atletico Madrid', 8, 'club'),
('Sevilla', 8, 'club'),
('Valencia', 8, 'club');

-- Create jerseys table
CREATE TABLE IF NOT EXISTS jerseys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    team_id INT NOT NULL,
    jersey_type ENUM('home', 'away', 'third', 'goalkeeper') NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    sizes VARCHAR(50) DEFAULT 'S,M,L,XL,XXL',
    stock INT NOT NULL DEFAULT 50,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
);

-- Insert sample jerseys
INSERT INTO jerseys (name, team_id, jersey_type, description, price, image_url, stock) VALUES 
('France Home Jersey 2023', 1, 'home', 'Official France national team home jersey for the 2023 season. Features the traditional blue color with elegant design elements.', 89.99, 'assets/images/sample/france-home.jpg', 100),
('France Away Jersey 2023', 1, 'away', 'Official France national team away jersey for the 2023 season. Features a striking white design with blue and red accents.', 89.99, 'assets/images/sample/france-away.jpg', 80),
('Germany Home Jersey 2023', 2, 'home', 'Official Germany national team home jersey for the 2023 season. Classic white design with black details.', 89.99, 'assets/images/sample/germany-home.jpg', 90),
('Germany Away Jersey 2023', 2, 'away', 'Official Germany national team away jersey for the 2023 season. Bold black design with colorful accents.', 89.99, 'assets/images/sample/germany-away.jpg', 75),
('Brazil Home Jersey 2023', 6, 'home', 'Official Brazil national team home jersey for the 2023 season. Iconic yellow with green details.', 89.99, 'assets/images/sample/brazil-home.jpg', 120),
('Brazil Away Jersey 2023', 6, 'away', 'Official Brazil national team away jersey for the 2023 season. Elegant blue design with yellow accents.', 89.99, 'assets/images/sample/brazil-away.jpg', 95),
('Manchester United Home Jersey 2023/24', 11, 'home', 'Official Manchester United home jersey for the 2023/24 season. Classic red design with subtle patterns.', 99.99, 'assets/images/sample/man-utd-home.jpg', 150),
('Manchester United Away Jersey 2023/24', 11, 'away', 'Official Manchester United away jersey for the 2023/24 season. Modern white design with red details.', 99.99, 'assets/images/sample/man-utd-away.jpg', 130),
('Real Madrid Home Jersey 2023/24', 16, 'home', 'Official Real Madrid home jersey for the 2023/24 season. Elegant white design with subtle gold accents.', 94.99, 'assets/images/sample/real-madrid-home.jpg', 180),
('Real Madrid Away Jersey 2023/24', 16, 'away', 'Official Real Madrid away jersey for the 2023/24 season. Bold black design with club details.', 94.99, 'assets/images/sample/real-madrid-away.jpg', 150),
('Barcelona Home Jersey 2023/24', 17, 'home', 'Official FC Barcelona home jersey for the 2023/24 season. Traditional blue and red stripes.', 94.99, 'assets/images/sample/barcelona-home.jpg', 170),
('Barcelona Away Jersey 2023/24', 17, 'away', 'Official FC Barcelona away jersey for the 2023/24 season. Striking gold design with club emblem.', 94.99, 'assets/images/sample/barcelona-away.jpg', 140);

-- Create orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    shipping_address TEXT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create order_items table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    size VARCHAR(10) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES jerseys(id) ON DELETE CASCADE
);

-- Insert sample orders
INSERT INTO orders (customer_name, customer_email, customer_phone, shipping_address, total_amount, status, created_at) VALUES 
('John Doe', 'john.doe@example.com', '555-123-4567', '123 Main St, Anytown, USA', 189.98, 'delivered', DATE_SUB(NOW(), INTERVAL 15 DAY)),
('Jane Smith', 'jane.smith@example.com', '555-987-6543', '456 Elm St, Somewhere, USA', 279.97, 'shipped', DATE_SUB(NOW(), INTERVAL 7 DAY)),
('Robert Johnson', 'robert.j@example.com', '555-456-7890', '789 Oak St, Nowhere, USA', 94.99, 'processing', DATE_SUB(NOW(), INTERVAL 3 DAY)),
('Emily Wilson', 'emily.w@example.com', '555-321-6547', '321 Pine St, Elsewhere, USA', 189.98, 'pending', DATE_SUB(NOW(), INTERVAL 1 DAY)),
('Michael Brown', 'michael.b@example.com', '555-789-1234', '654 Maple St, Anywhere, USA', 99.99, 'pending', CURRENT_TIMESTAMP);

-- Insert sample order items
INSERT INTO order_items (order_id, product_id, quantity, size, price) VALUES 
(1, 1, 1, 'M', 89.99),
(1, 3, 1, 'L', 89.99),
(2, 7, 1, 'XL', 99.99),
(2, 9, 1, 'L', 94.99),
(2, 5, 1, 'M', 89.99),
(3, 10, 1, 'M', 94.99),
(4, 2, 1, 'S', 89.99),
(4, 4, 1, 'M', 89.99),
(5, 7, 1, 'L', 99.99);