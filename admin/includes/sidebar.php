
<div class="admin-sidebar bg-dark">
    <div class="sidebar-header " style="background-color:grey">
        <a href="index.php" class="sidebar-brand">
           
            <img src="../assets/logo.png" alt="Logo" class="sidebar-logo" style="width:150px; height: 100px;">
           
        </a>
        <button class="sidebar-toggle d-lg-none" type="button">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    
    <div class="sidebar-menu">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>" href="index.php">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= in_array(basename($_SERVER['PHP_SELF']), ['products.php', 'add-product.php', 'edit-product.php']) ? 'active' : '' ?>" href="products.php">
                    <i class="fas fa-tshirt me-2"></i>
                    Products
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= in_array(basename($_SERVER['PHP_SELF']), ['orders.php', 'order-details.php']) ? 'active' : '' ?>" href="orders.php">
                    <i class="fas fa-shopping-bag me-2"></i>
                    Orders
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= in_array(basename($_SERVER['PHP_SELF']), ['categories.php', 'add-category.php', 'edit-category.php']) ? 'active' : '' ?>" href="categories.php">
                    <i class="fas fa-folder me-2"></i>
                    Categories
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= in_array(basename($_SERVER['PHP_SELF']), ['teams.php', 'add-team.php', 'edit-team.php']) ? 'active' : '' ?>" href="teams.php">
                    <i class="fas fa-users me-2"></i>
                    Teams
                </a>
            
            <li class="nav-item">
                <a class="nav-link <?= in_array(basename($_SERVER['PHP_SELF']), ['users.php', 'add-admin.php', 'edit-admin.php']) ? 'active' : '' ?>" href="users.php">
                    <i class="fas fa-users me-2"></i>
                    Admin Users
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i>
                    Logout
                </a>
            </li>
        </ul>
    </div>
</div>
