
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Donation System</title>
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
            width: 400px;
            height: 400px;
            top: -150px;
            right: -150px;
        }
        
        body::after {
            width: 500px;
            height: 500px;
            bottom: -200px;
            left: -200px;
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
        
        .welcome-container {
            background: white;
            padding: 60px 50px;
            border-radius: 25px;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.4);
            text-align: center;
            max-width: 550px;
            position: relative;
            z-index: 1;
            animation: slideUp 0.8s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .logo {
            font-size: 100px;
            margin-bottom: 30px;
            animation: pulse 2s ease-in-out infinite;
            filter: drop-shadow(0 10px 20px rgba(192, 57, 43, 0.3));
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        h1 {
            color: #c0392b;
            font-size: 42px;
            margin-bottom: 20px;
            font-weight: 700;
            letter-spacing: -1px;
        }
        
        .tagline {
            color: #7f8c8d;
            font-size: 18px;
            margin-bottom: 40px;
            line-height: 1.6;
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .feature {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
            transition: all 0.3s;
        }
        
        .feature:hover {
            background: #fff5f5;
            transform: translateY(-5px);
        }
        
        .feature-icon {
            font-size: 35px;
            margin-bottom: 10px;
        }
        
        .feature-text {
            color: #2c3e50;
            font-size: 13px;
            font-weight: 600;
        }
        
        .button-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            padding: 16px 30px;
            border: none;
            border-radius: 12px;
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%);
            color: white;
            box-shadow: 0 8px 25px rgba(192, 57, 43, 0.4);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(192, 57, 43, 0.5);
        }
        
        .btn-secondary {
            background: white;
            color: #c0392b;
            border: 3px solid #c0392b;
        }
        
        .btn-secondary:hover {
            background: #c0392b;
            color: white;
            transform: translateY(-3px);
        }
        
        .footer {
            margin-top: 35px;
            color: #95a5a6;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="welcome-container">
        <div class="logo">🩸</div>
        <h1>Blood Donation System</h1>
        <p class="tagline">Save lives by donating blood.<br>Connect donors with those in need.</p>
        
        <div class="features">
            <div class="feature">
                <div class="feature-icon">🏥</div>
                <div class="feature-text">For Hospitals</div>
            </div>
            <div class="feature">
                <div class="feature-icon">💉</div>
                <div class="feature-text">For Donors</div>
            </div>
            <div class="feature">
                <div class="feature-icon">⚡</div>
                <div class="feature-text">Fast & Easy</div>
            </div>
        </div>
        
        <div class="button-group">
            <a href="login.php" class="btn btn-primary">Login</a>
            <a href="register.php" class="btn btn-secondary">Register</a>
        </div>
        
        
    </div>
</body>
</html>