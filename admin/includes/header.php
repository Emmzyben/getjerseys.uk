
<header class="admin-header">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <button class="sidebar-toggle d-lg-none me-3" type="button">
                    <i class="fas fa-bars"></i>
                </button>
             
            </div>
            
            <div class="d-flex align-items-center">
                <div class="dropdown user-dropdown ms-3">
                    <button class="btn d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="../assets/user.png" alt="Admin" class="rounded-circle me-2" width="32" height="32">
                        <span class="d-none d-md-inline"><?= $_SESSION['admin_username'] ?? 'Admin' ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="users.php"><i class="fas fa-cog me-2"></i> Accounts</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</header>

