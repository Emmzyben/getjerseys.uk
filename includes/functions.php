<?php
// Get latest jerseys for homepage
function getLatestJerseys($conn, $limit = 8) {
    try {
        $stmt = $conn->prepare("SELECT j.*, t.name as team_name 
                               FROM jerseys j 
                               JOIN teams t ON j.team_id = t.id 
                               ORDER BY j.created_at DESC 
                               LIMIT :limit");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        // For demonstration, return sample data
        return getSampleJerseys($limit);
    }
}

// Get jersey details by ID
function getJerseyById($conn, $id) {
    try {
        $stmt = $conn->prepare("SELECT j.*, t.name as team_name, 
                               t.category_type, c.name as category_name 
                               FROM jerseys j 
                               JOIN teams t ON j.team_id = t.id 
                               LEFT JOIN categories c ON t.category_id = c.id 
                               WHERE j.id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    } catch(PDOException $e) {
        return null;
    }
}

// Get all categories by type
function getCategoriesByType($conn, $type) {
    try {
        $stmt = $conn->prepare("SELECT * FROM categories WHERE type = :type AND parent_id IS NULL");
        $stmt->bindParam(':type', $type, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

// Get subcategories by parent ID
function getSubcategories($conn, $parentId) {
    try {
        $stmt = $conn->prepare("SELECT * FROM categories WHERE parent_id = :parent_id");
        $stmt->bindParam(':parent_id', $parentId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

// Get teams by category ID
function getTeamsByCategory($conn, $categoryId) {
    try {
        $stmt = $conn->prepare("SELECT * FROM teams WHERE category_id = :category_id");
        $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

// Get jerseys by team ID
function getJerseysByTeam($conn, $teamId) {
    try {
        $stmt = $conn->prepare("SELECT * FROM jerseys WHERE team_id = :team_id");
        $stmt->bindParam(':team_id', $teamId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

// Get jerseys by jersey type
function getJerseysByType($conn, $type) {
    try {
        $stmt = $conn->prepare("SELECT j.*, t.name as team_name 
                               FROM jerseys j 
                               JOIN teams t ON j.team_id = t.id 
                               WHERE j.jersey_type = :type 
                               ORDER BY j.created_at DESC");
        $stmt->bindParam(':type', $type, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

// Add item to cart
function addToCart($productId, $quantity, $size) {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    $itemKey = $productId . '-' . $size;
    
    if (isset($_SESSION['cart'][$itemKey])) {
        $_SESSION['cart'][$itemKey]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$itemKey] = [
            'product_id' => $productId,
            'quantity' => $quantity,
            'size' => $size
        ];
    }
}

// Get cart items with details
function getCartItems($conn) {
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return [];
    }
    
    $cartItems = [];
    
    foreach ($_SESSION['cart'] as $item) {
        $jersey = getJerseyById($conn, $item['product_id']);
        if ($jersey) {
            $cartItems[] = [
                'product_id' => $item['product_id'],
                'name' => $jersey['name'],
                'team_name' => $jersey['team_name'],
                'price' => $jersey['price'],
                'image_url' => $jersey['image_url'],
                'quantity' => $item['quantity'],
                'size' => $item['size'],
                'subtotal' => $jersey['price'] * $item['quantity']
            ];
        }
    }
    
    return $cartItems;
}

// Calculate cart total
function getCartTotal($cartItems) {
    $total = 0;
    foreach ($cartItems as $item) {
        $total += $item['subtotal'];
    }
    return $total;
}

// Create order
function createOrder($conn, $customerData, $cartItems, $total) {
    try {
        $conn->beginTransaction();
        
        // Insert order
        $stmt = $conn->prepare("INSERT INTO orders (customer_name, customer_email, customer_phone, 
                               shipping_address, total_amount, status, created_at) 
                               VALUES (:name, :email, :phone, :address, :total, 'pending', NOW())");
        
        $stmt->bindParam(':name', $customerData['name'], PDO::PARAM_STR);
        $stmt->bindParam(':email', $customerData['email'], PDO::PARAM_STR);
        $stmt->bindParam(':phone', $customerData['phone'], PDO::PARAM_STR);
        $stmt->bindParam(':address', $customerData['address'], PDO::PARAM_STR);
        $stmt->bindParam(':total', $total, PDO::PARAM_STR);
        $stmt->execute();
        
        $orderId = $conn->lastInsertId();
        
        // Insert order items
        foreach ($cartItems as $item) {
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, size, price) 
                                   VALUES (:order_id, :product_id, :quantity, :size, :price)");
            
            $stmt->bindParam(':order_id', $orderId, PDO::PARAM_INT);
            $stmt->bindParam(':product_id', $item['product_id'], PDO::PARAM_INT);
            $stmt->bindParam(':quantity', $item['quantity'], PDO::PARAM_INT);
            $stmt->bindParam(':size', $item['size'], PDO::PARAM_STR);
            $stmt->bindParam(':price', $item['price'], PDO::PARAM_STR);
            $stmt->execute();
        }
        
        $conn->commit();
        return $orderId;
    } catch(PDOException $e) {
        $conn->rollBack();
        return false;
    }
}

// Admin functions

// Admin login
function adminLogin($conn, $username, $password) {
    try {
        $stmt = $conn->prepare("SELECT * FROM admins WHERE username = :username");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            return true;
        }
        
        return false;
    } catch(PDOException $e) {
        return false;
    }
}

// Check if admin is logged in
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

// Get admin by ID
function getAdminById($conn, $id) {
    try {
        $stmt = $conn->prepare("SELECT id, username, email, created_at FROM admins WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    } catch(PDOException $e) {
        return null;
    }
}

// Get all products with pagination
function getAllProducts($conn, $page = 1, $perPage = 10) {
    try {
        $offset = ($page - 1) * $perPage;
        
        $stmt = $conn->prepare("SELECT j.*, t.name as team_name 
                               FROM jerseys j 
                               JOIN teams t ON j.team_id = t.id 
                               ORDER BY j.created_at DESC 
                               LIMIT :limit OFFSET :offset");
        
        $stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

// Count total products
function countProducts($conn) {
    try {
        $stmt = $conn->query("SELECT COUNT(*) FROM jerseys");
        return $stmt->fetchColumn();
    } catch(PDOException $e) {
        return 0;
    }
}

// Get all orders with pagination
function getAllOrders($conn, $page = 1, $perPage = 10) {
    try {
        $offset = ($page - 1) * $perPage;
        
        $stmt = $conn->prepare("SELECT * FROM orders ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        $stmt->bindParam(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

// Count total orders
function countOrders($conn) {
    try {
        $stmt = $conn->query("SELECT COUNT(*) FROM orders");
        return $stmt->fetchColumn();
    } catch(PDOException $e) {
        return 0;
    }
}

// Get recent orders
function getRecentOrders($conn, $limit = 5) {
    try {
        $stmt = $conn->prepare("SELECT * FROM orders ORDER BY created_at DESC LIMIT :limit");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

// Update order status
function updateOrderStatus($conn, $orderId, $status) {
    try {
        $stmt = $conn->prepare("UPDATE orders SET status = :status WHERE id = :id");
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':id', $orderId, PDO::PARAM_INT);
        return $stmt->execute();
    } catch(PDOException $e) {
        return false;
    }
}

// Add new admin
function addAdmin($conn, $username, $email, $password) {
    try {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO admins (username, email, password, created_at) 
                               VALUES (:username, :email, :password, NOW())");
        
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        
        return $stmt->execute();
    } catch(PDOException $e) {
        return false;
    }
}

// Add new category
function addCategory($conn, $name, $type, $parentId = null) {
    try {
        $stmt = $conn->prepare("INSERT INTO categories (name, type, parent_id) 
                               VALUES (:name, :type, :parent_id)");
        
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':type', $type, PDO::PARAM_STR);
        $stmt->bindParam(':parent_id', $parentId, $parentId ? PDO::PARAM_INT : PDO::PARAM_NULL);
        
        return $stmt->execute();
    } catch(PDOException $e) {
        return false;
    }
}

// Add new team
function addTeam($conn, $name, $categoryId, $categoryType) {
    try {
        $stmt = $conn->prepare("INSERT INTO teams (name, category_id, category_type) 
                               VALUES (:name, :category_id, :category_type)");
        
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindParam(':category_type', $categoryType, PDO::PARAM_STR);
        
        return $stmt->execute();
    } catch(PDOException $e) {
        return false;
    }
}

// Add new jersey
function addJersey($conn, $data) {
    try {
        $stmt = $conn->prepare("INSERT INTO jerseys (name, team_id, jersey_type, description, 
                               price, sizes, stock, created_at) 
                               VALUES (:name, :team_id, :jersey_type, :description, 
                               :price, :sizes, :stock, NOW())");
        
        $stmt->bindParam(':name', $data['name'], PDO::PARAM_STR);
        $stmt->bindParam(':team_id', $data['team_id'], PDO::PARAM_INT);
        $stmt->bindParam(':jersey_type', $data['jersey_type'], PDO::PARAM_STR);
        $stmt->bindParam(':description', $data['description'], PDO::PARAM_STR);
        $stmt->bindParam(':price', $data['price'], PDO::PARAM_STR);
        $stmt->bindParam(':sizes', $data['sizes'], PDO::PARAM_STR);
        $stmt->bindParam(':stock', $data['stock'], PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            return $conn->lastInsertId(); // Return inserted jersey ID
        } else {
            return false;
        }
    } catch(PDOException $e) {
        error_log("Add Jersey Error: " . $e->getMessage());
        return false;
    }
}

function getTeamsByCategoryId($conn, $categoryId) {
    try {
        $stmt = $conn->prepare("SELECT id, name FROM teams WHERE category_id = :category_id");
        $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        return [];
    }
}

// Get jerseys by continent
function getJerseysByContinent($conn, $continent) {
    try {
        $stmt = $conn->prepare("SELECT j.*, t.name as team_name, t.continent 
                               FROM jerseys j 
                               JOIN teams t ON j.team_id = t.id 
                               WHERE t.continent = :continent 
                               ORDER BY j.created_at DESC");
        $stmt->bindParam(':continent', $continent, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}

// Get jerseys by league
function getJerseysByLeague($conn, $league) {
    try {
        $stmt = $conn->prepare("SELECT j.*, t.name as team_name, t.league 
                               FROM jerseys j 
                               JOIN teams t ON j.team_id = t.id 
                               WHERE t.league = :league 
                               ORDER BY j.created_at DESC");
        $stmt->bindParam(':league', $league, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch(PDOException $e) {
        return [];
    }
}
// Sample data for demonstration

// function filterJerseys(
//     $jerseys,
//     $sortBy = null,
//     $priceRange = null,
//     $jerseyType = null,
//     $teamId = null,
//     $categoryType = null
// ) {
//     // Filter by price range
//     if ($priceRange && is_array($priceRange) && count($priceRange) === 2) {
//         [$min, $max] = $priceRange;
//         $jerseys = array_filter($jerseys, fn($j) => $j['price'] >= $min && $j['price'] <= $max);
//     }

//     // Filter by jersey type
//     if ($jerseyType) {
//         $jerseys = array_filter($jerseys, fn($j) => strtolower($j['jersey_type']) === strtolower($jerseyType));
//     }

//     // Filter by team
//     if ($teamId) {
//         $jerseys = array_filter($jerseys, fn($j) => $j['team_id'] == $teamId);
//     }

//     // Filter by category type (national/club)
//     if ($categoryType) {
//         $jerseys = array_filter($jerseys, fn($j) => strtolower($j['category_type']) === strtolower($categoryType));
//     }

//     // Sorting
//     if ($sortBy) {
//         usort($jerseys, function($a, $b) use ($sortBy) {
//             switch ($sortBy) {
//                 case 'price_asc': return $a['price'] <=> $b['price'];
//                 case 'price_desc': return $b['price'] <=> $a['price'];
//                 case 'newest': return strtotime($b['created_at']) <=> strtotime($a['created_at']);
//                 case 'oldest': return strtotime($a['created_at']) <=> strtotime($b['created_at']);
//                 default: return 0;
//             }
//         });
//     }

//     return array_values($jerseys); // Reset keys
// }



// function getAllJerseys($conn) {
//     try {
//         $stmt = $conn->prepare("SELECT j.*, t.name as team_name 
//                                FROM jerseys j 
//                                JOIN teams t ON j.team_id = t.id 
//                                ORDER BY j.created_at DESC");
//         $stmt->execute();
//         return $stmt->fetchAll();
//     } catch(PDOException $e) {
//         return [];
//     }
// }

function countPendingOrders($conn) {
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
        $stmt->execute();
        return $stmt->fetchColumn();
    } catch(PDOException $e) {
        return 0;
    }
}
function countDeliveredOrders($conn) {
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE status = 'delivered'");
        $stmt->execute();
        return $stmt->fetchColumn();
    } catch(PDOException $e) {
        return 0;
    }
}
$deliveredOrders = countDeliveredOrders($conn);
function getSampleJerseys($limit = 8) {
    $jerseys = [
        [
            'id' => 1,
            'name' => 'France Home Jersey 2023',
            'team_name' => 'France',
            'jersey_type' => 'home',
            'price' => 89.99,
            'image_url' => 'assets/images/sample/france-home.jpg',
            'created_at' => '2023-09-15'
        ],
        [
            'id' => 2,
            'name' => 'Barcelona Away Jersey 2023/24',
            'team_name' => 'FC Barcelona',
            'jersey_type' => 'away',
            'price' => 94.99,
            'image_url' => 'assets/images/sample/barcelona-away.jpg',
            'created_at' => '2023-09-10'
        ],
        [
            'id' => 3,
            'name' => 'Brazil Home Jersey 2023',
            'team_name' => 'Brazil',
            'jersey_type' => 'home',
            'price' => 89.99,
            'image_url' => 'assets/images/sample/brazil-home.jpg',
            'created_at' => '2023-09-05'
        ],
        [
            'id' => 4,
            'name' => 'Manchester United Home Jersey 2023/24',
            'team_name' => 'Manchester United',
            'jersey_type' => 'home',
            'price' => 99.99,
            'image_url' => 'assets/images/sample/man-utd-home.jpg',
            'created_at' => '2023-09-01'
        ],
        [
            'id' => 5,
            'name' => 'Germany Away Jersey 2023',
            'team_name' => 'Germany',
            'jersey_type' => 'away',
            'price' => 89.99,
            'image_url' => 'assets/images/sample/germany-away.jpg',
            'created_at' => '2023-08-25'
        ],
        [
            'id' => 6,
            'name' => 'Real Madrid Third Jersey 2023/24',
            'team_name' => 'Real Madrid',
            'jersey_type' => 'third',
            'price' => 94.99,
            'image_url' => 'assets/images/sample/real-madrid-third.jpg',
            'created_at' => '2023-08-20'
        ],
        [
            'id' => 7,
            'name' => 'Argentina Home Jersey 2023',
            'team_name' => 'Argentina',
            'jersey_type' => 'home',
            'price' => 89.99,
            'image_url' => 'assets/images/sample/argentina-home.jpg',
            'created_at' => '2023-08-15'
        ],
        [
            'id' => 8,
            'name' => 'Liverpool Away Jersey 2023/24',
            'team_name' => 'Liverpool',
            'jersey_type' => 'away',
            'price' => 94.99,
            'image_url' => 'assets/images/sample/liverpool-away.jpg',
            'created_at' => '2023-08-10'
        ]
    ];
    
    return array_slice($jerseys, 0, $limit);
}