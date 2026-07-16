<?php
session_start();
require_once 'db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: redirect.php");
    exit();
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (!empty($username) && !empty($password)) {
        try {
            $stmt = $pdo->prepare("SELECT UserID, Username, PasswordHash, Role, RelatedID FROM User WHERE Username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && $user['PasswordHash'] === $password) {
                $_SESSION['user_id'] = $user['UserID'];
                $_SESSION['username'] = $user['Username'];
                $_SESSION['role'] = $user['Role'];
                $_SESSION['related_id'] = $user['RelatedID'];
                
                header("Location: redirect.php");
                exit();
            } else {
                $error = 'Invalid username or password';
            }
        } catch (PDOException $e) {
            $error = 'Login error: ' . $e->getMessage();
        }
    } else {
        $error = 'Please enter both username and password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Donation System - Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #c0392b 0%, #e74c3c 50%, #d63031 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
            padding: 20px;
        }
        
        body::before,
        body::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            animation: float 8s ease-in-out infinite;
        }
        
        body::before {
            width: 350px;
            height: 350px;
            top: -100px;
            right: -100px;
        }
        
        body::after {
            width: 450px;
            height: 450px;
            bottom: -150px;
            left: -150px;
            animation-delay: 4s;
        }
        
        @keyframes float {
            0%, 100% { 
                transform: translateY(0px) rotate(0deg); 
            }
            50% { 
                transform: translateY(-30px) rotate(180deg); 
            }
        }
        
        .login-container {
            background: white;
            padding: 45px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 1;
            animation: slideUp 0.6s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .logo {
            font-size: 60px;
            animation: pulse 2.5s ease-in-out infinite;
            filter: drop-shadow(0 4px 8px rgba(192, 57, 43, 0.3));
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.15); }
        }
        
        h1 {
            color: #c0392b;
            text-align: center;
            margin-bottom: 10px;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        
        .subtitle {
            text-align: center;
            color: #5a6c7d;
            margin-bottom: 35px;
            font-size: 14px;
            font-weight: 400;
        }
        
        .form-group {
            margin-bottom: 22px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 600;
            font-size: 14px;
            letter-spacing: 0.3px;
        }
        
        .input-wrapper {
            position: relative;
            transition: transform 0.2s;
        }
        
        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #7f8c8d;
            font-size: 18px;
            z-index: 1;
        }
        
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 14px 14px 14px 45px;
            border: 2px solid #e8eef3;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #f8f9fb;
        }
        
        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #c0392b;
            background: white;
            box-shadow: 0 0 0 3px rgba(192, 57, 43, 0.1);
        }
        
        input[type="text"]:focus + .input-icon,
        input[type="password"]:focus + .input-icon {
            color: #c0392b;
        }
        
        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #c0392b 0%, #e74c3c 50%, #d63031 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            position: relative;
            overflow: hidden;
            letter-spacing: 0.5px;
            box-shadow: 0 6px 20px rgba(192, 57, 43, 0.3);
        }
        
        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.15);
            transition: left 0.5s;
        }
        
        .btn:hover::before {
            left: 100%;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(192, 57, 43, 0.4);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .error {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            padding: 14px;
            border-radius: 10px;
            margin-bottom: 25px;
            text-align: center;
            font-size: 14px;
            animation: shake 0.5s;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
            font-weight: 500;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        
        .divider {
            position: relative;
            text-align: center;
            margin: 28px 0 20px;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 1px;
            background: linear-gradient(90deg, transparent, #e8eef3, transparent);
        }
        
        .divider span {
            position: relative;
            background: white;
            padding: 0 12px;
            color: #95a5a6;
            font-size: 13px;
            font-weight: 500;
        }
        
        .register-link {
            text-align: center;
            color: #7f8c8d;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .register-link a {
            color: #c0392b;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            position: relative;
        }
        
        .register-link a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: #c0392b;
            transition: width 0.3s;
        }
        
        .register-link a:hover {
            color: #e74c3c;
        }
        
        .register-link a:hover::after {
            width: 100%;
        }
        
        .footer-text {
            text-align: center;
            margin-top: 25px;
            color: #95a5a6;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-container">
            <div class="logo">🩸</div>
        </div>
        
        <h1>Blood Donation System</h1>
        <p class="subtitle">Sign in to continue to your dashboard</p>
        
        <?php if ($error): ?>
            <div class="error">
                ⚠️ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" id="loginForm">
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-wrapper">
                    <input type="text" id="username" name="username" placeholder="Enter your username" required>
                    <span class="input-icon">👤</span>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <span class="input-icon">🔒</span>
                </div>
            </div>
            
            <button type="submit" class="btn">Sign In</button>
        </form>
        
        <div class="divider">
            <span>New to the system?</span>
        </div>
        
        <div class="register-link">
            Don't have an account? <a href="register.php">Register here</a>
        </div>
        
    </div>
    
    <script>
        // Client-side validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            
            if (!username || !password) {
                e.preventDefault();
                alert('⚠️ Please fill in all fields');
                return false;
            }
            
            if (username.length < 3) {
                e.preventDefault();
                alert('⚠️ Username must be at least 3 characters');
                return false;
            }
        });
        
        // Add focus animation to inputs
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
        
        // Add loading effect on submit
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = this.querySelector('.btn');
            btn.textContent = 'Signing in...';
            btn.style.opacity = '0.8';
        });
    </script>
</body>
</html>