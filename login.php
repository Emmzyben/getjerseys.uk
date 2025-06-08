<?php
require_once './config/database.php';
require_once './includes/functions.php';
session_start();

if (isAdminLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        if (adminLogin($conn, $username, $password)) {
            header('Location: ./admin/index.php ');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - GetJerseys</title>
     <link rel="stylesheet" href="style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
    background: linear-gradient(135deg, #e0e7ff 0%, #f8fafc 100%);
    min-height: 100vh;
    position: relative;
    z-index: 1;
}
body::before {
    content: "";
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: 
        linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)),
        url('./assets/logo.png') center center no-repeat;
    background-size: cover;
    z-index: -1;
    opacity: 1;
    pointer-events: none;
}

        .login-card {
            max-width: 900px;
            margin: 80px auto;
            border: none;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
            background: #fff;
            padding: 0;
            position: relative;
        }
        .login-row {
            display: flex;
            flex-direction: column;
        }
        @media (min-width: 992px) {
            .login-row {
                flex-direction: row;
            }
            .logo-side {
                border-right: 1px solid #e5e7eb;
            }
        }
        .logo-side {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 32px;
            background: #f1f5f9;
            border-radius: 18px 18px 0 0;
        }
        @media (min-width: 992px) {
            .logo-side {
                border-radius: 18px 0 0 18px;
                min-width: 320px;
                max-width: 350px;
                border-radius: 18px 0 0 18px;
            }
        }
        .logo-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 10px;
        }
        .logo-container img {
            max-width: 170px;
            height: auto;
        }
        .login-title {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 0;
            text-align: center;
            color: #1e293b;
            letter-spacing: 1px;
        }
        .form-side {
            flex: 1;
            padding: 40px 32px 32px 32px;
        }
        .form-label {
            font-weight: 500;
            color: #334155;
        }
        .form-control {
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            background: #f1f5f9;
        }
        .form-control:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 0.2rem rgba(99,102,241,.15);
            background: #fff;
        }
        .btn-primary {
            width: 100%;
            background: linear-gradient(90deg, #6366f1 0%, #3b82f6 100%);
            border: none;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: background 0.2s;
        }
        .btn-primary:hover {
            background: linear-gradient(90deg, #3b82f6 0%, #6366f1 100%);
        }
        .alert {
            font-size: 1rem;
            margin-bottom: 18px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-row">
            <div class="logo-side">
                <div class="logo-container">
                    <img src="./assets/logo.png" alt="GetJerseys Logo" >
                </div>
                <div class="login-title">Admin Login</div>
            </div>
            <div class="form-side">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form method="post" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="username" 
                            name="username" 
                            required 
                            autofocus
                        >
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input 
                            type="password" 
                            class="form-control" 
                            id="password" 
                            name="password" 
                            required
                        >
                    </div>
                    <button type="submit" class="btn btn-primary">Login</button>
                    <div class="mt-3 text-center">
                        <a href="index.php" class="text-decoration-none">Back to Home</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
      <button id="scrollToTopBtn" onclick="scrollToTop()">↑</button>
    <a href="https://wa.me/447341157876" target="_blank" id="whatsapp-icon-container">
      <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp" />
      Chat with us
    </a>
    <script src="assets/js/main.js"></script>
     <script src="script.js"></script>
</body>
</html>