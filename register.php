<?php

session_start();
require_once 'db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: redirect.php");
    exit();
}

$error = '';
$success = false;

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userType = $_POST['user_type'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($username) || empty($password) || empty($userType)) {
        $error = 'Please fill all required fields';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        try {
            $pdo->beginTransaction();
            
            // Check if username exists
            $stmt = $pdo->prepare("SELECT UserID FROM User WHERE Username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $error = 'Username already exists';
            } else {
                $relatedId = null;
                
                // Register as Donor
                if ($userType === 'Donor') {
                    $fullName = $_POST['full_name'];
                    $bloodType = $_POST['blood_type'];
                    $gender = $_POST['gender'];
                    $birthDate = $_POST['birth_date'];
                    $phone = $_POST['phone'] ?? null;
                    $medicalConditions = $_POST['medical_conditions'] ?? 'None';
                    
                    // If empty, set to 'None'
                    if (empty(trim($medicalConditions))) {
                        $medicalConditions = 'None';
                    }
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO Donor (FullName, BirthDate, Gender, BloodType, Phone, MedicalConditions) 
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$fullName, $birthDate, $gender, $bloodType, $phone, $medicalConditions]);
                    $relatedId = $pdo->lastInsertId();
                }
                // Register as Hospital
                elseif ($userType === 'Hospital') {
                    $hospitalName = $_POST['hospital_name'];
                    $location = $_POST['location'];
                    $phone = $_POST['hospital_phone'] ?? null;
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO Hospital (Name, Location, Phone) 
                        VALUES (?, ?, ?)
                    ");
                    $stmt->execute([$hospitalName, $location, $phone]);
                    $relatedId = $pdo->lastInsertId();
                }
                
                // Create User account
                $stmt = $pdo->prepare("
                    INSERT INTO User (Username, PasswordHash, Role, RelatedID) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$username, $password, $userType, $relatedId]);
                
                $pdo->commit();
                $success = true;
            }
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = 'Registration error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Blood Donation System</title>
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
            padding: 30px 20px;
        }
        
        .register-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 520px;
            max-height: 90vh;
            overflow-y: auto;
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
        
        .logo {
            text-align: center;
            font-size: 60px;
            margin-bottom: 20px;
        }
        
        h1 {
            color: #c0392b;
            text-align: center;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .subtitle {
            text-align: center;
            color: #7f8c8d;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        /* Steps */
        .steps {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 30px;
        }
        
        .step {
            width: 40px;
            height: 4px;
            background: #ecf0f1;
            border-radius: 2px;
            transition: all 0.3s;
        }
        
        .step.active {
            background: #c0392b;
        }
        
        /* Type Selection */
        .type-selector {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .type-option {
            padding: 25px 20px;
            border: 3px solid #ecf0f1;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
        }
        
        .type-option:hover {
            border-color: #c0392b;
            background: #fff5f5;
            transform: translateY(-3px);
        }
        
        .type-option.active {
            border-color: #c0392b;
            background: #c0392b;
            color: white;
        }
        
        .type-option input[type="radio"] {
            display: none;
        }
        
        .type-icon {
            font-size: 45px;
            margin-bottom: 10px;
        }
        
        .type-label {
            font-weight: 600;
            font-size: 16px;
        }
        
        /* Forms */
        .form-step {
            display: none;
        }
        
        .form-step.active {
            display: block;
            animation: fadeIn 0.4s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .form-group {
            margin-bottom: 18px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #2c3e50;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ecf0f1;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #c0392b;
            box-shadow: 0 0 0 3px rgba(192, 57, 43, 0.1);
        }
        
        .form-group small {
            color: #7f8c8d;
            font-size: 12px;
        }
        
        .btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%);
            color: white;
            box-shadow: 0 6px 20px rgba(192, 57, 43, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(192, 57, 43, 0.4);
        }
        
        .btn-secondary {
            background: #95a5a6;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #7f8c8d;
        }
        
        .button-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 20px;
        }
        
        .error {
            background: #e74c3c;
            color: white;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
        }
        
        .success-screen {
            display: none;
            text-align: center;
            padding: 20px 0;
        }
        
        .success-screen.active {
            display: block;
            animation: fadeIn 0.6s;
        }
        
        .success-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        
        .success-screen h2 {
            color: #2ecc71;
            margin-bottom: 15px;
            font-size: 26px;
        }
        
        .success-screen p {
            color: #7f8c8d;
            margin-bottom: 30px;
            font-size: 15px;
            line-height: 1.6;
        }
        
        .login-link {
            text-align: center;
            margin-top: 25px;
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .login-link a {
            color: #c0392b;
            text-decoration: none;
            font-weight: 600;
        }
        
        hr {
            margin: 20px 0;
            border: none;
            border-top: 2px solid #ecf0f1;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <?php if ($success): ?>
            <div class="success-screen active">
                <div class="success-icon">✅</div>
                <h2>Registration Successful!</h2>
                <p>Your account has been created successfully.<br>You can now login with your credentials.</p>
                <a href="login.php" class="btn btn-primary">Go to Login</a>
            </div>
        <?php else: ?>
            <div class="logo">🩸</div>
            <h1>Create Account</h1>
            <p class="subtitle">Register as a Donor or Hospital</p>
            
            <div class="steps">
                <div class="step active" id="step1"></div>
                <div class="step" id="step2"></div>
            </div>
            
            <?php if ($error): ?>
                <div class="error">⚠️ <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" id="registerForm">
                <div class="form-step active" id="stepSelectType">
                    <h3 style="text-align: center; margin-bottom: 20px; color: #2c3e50;">Select Account Type</h3>
                    <div class="type-selector">
                        <label class="type-option" id="donorOption">
                            <input type="radio" name="user_type" value="Donor" required>
                            <div class="type-icon">🩸</div>
                            <div class="type-label">Donor</div>
                        </label>
                        <label class="type-option" id="hospitalOption">
                            <input type="radio" name="user_type" value="Hospital" required>
                            <div class="type-icon">🏥</div>
                            <div class="type-label">Hospital</div>
                        </label>
                    </div>
                    <button type="button" class="btn btn-primary" onclick="goToStep2()">Continue</button>
                </div>
                
                <div class="form-step" id="stepFillInfo">
                    <div class="form-group">
                        <label>Username *</label>
                        <input type="text" name="username" id="username">
                    </div>
                    
                    <div class="form-group">
                        <label>Password *</label>
                        <input type="password" name="password" id="password" minlength="6">
                        <small>At least 6 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Confirm Password *</label>
                        <input type="password" name="confirm_password" id="confirm_password">
                    </div>
                    
                    <div id="donorFields" style="display:none;">
                        <hr>
                        <h3 style="margin-bottom: 15px; color: #2c3e50;">Donor Information</h3>
                        
                        <div class="form-group">
                            <label>Full Name *</label>
                            <input type="text" name="full_name" id="full_name">
                        </div>
                        
                        <div class="form-group">
                            <label>Blood Type *</label>
                            <select name="blood_type" id="blood_type">
                                <option value="">Select Blood Type</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Gender *</label>
                            <select name="gender" id="gender">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Birth Date *</label>
                            <input type="date" name="birth_date" id="birth_date">
                        </div>
                        
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" name="phone" placeholder="e.g., 71-123456">
                        </div>
                        
                        <div class="form-group">
                            <label>Medical Conditions</label>
                            <textarea name="medical_conditions" rows="3" placeholder="Any medical conditions or allergies (optional)" style="width: 100%; padding: 10px; border: 2px solid #ecf0f1; border-radius: 10px; font-size: 14px; font-family: inherit;"></textarea>
                            <small style="color: #7f8c8d; font-size: 12px;">Leave empty if none</small>
                        </div>
                    </div>
                    
                    <div id="hospitalFields" style="display:none;">
                        <hr>
                        <h3 style="margin-bottom: 15px; color: #2c3e50;">Hospital Information</h3>
                        
                        <div class="form-group">
                            <label>Hospital Name *</label>
                            <input type="text" name="hospital_name" id="hospital_name">
                        </div>
                        
                        <div class="form-group">
                            <label>Location *</label>
                            <input type="text" name="location" id="location" placeholder="City, Street">
                        </div>
                        
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" name="hospital_phone" placeholder="e.g., 01-123456">
                        </div>
                    </div>
                    
                    <div class="button-group">
                        <button type="button" class="btn btn-secondary" onclick="goToStep1()">Back</button>
                        <button type="submit" class="btn btn-primary">Register</button>
                    </div>
                </div>
            </form>
            
            <div class="login-link">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        let selectedType = '';
        
        // Handle user type selection
        document.querySelectorAll('input[name="user_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                selectedType = this.value;
                document.querySelectorAll('.type-option').forEach(opt => opt.classList.remove('active'));
                this.parentElement.classList.add('active');
            });
        });
        
        function goToStep2() {
            if (!selectedType) {
                alert('Please select an account type');
                return;
            }
            
            document.getElementById('stepSelectType').classList.remove('active');
            document.getElementById('stepFillInfo').classList.add('active');
            document.getElementById('step2').classList.add('active');
            
            // Show relevant fields
            const donorFields = document.getElementById('donorFields');
            const hospitalFields = document.getElementById('hospitalFields');
            
            if (selectedType === 'Donor') {
                donorFields.style.display = 'block';
                hospitalFields.style.display = 'none';
                // Make donor fields required
                document.getElementById('full_name').required = true;
                document.getElementById('blood_type').required = true;
                document.getElementById('birth_date').required = true;
                document.getElementById('hospital_name').required = false;
                document.getElementById('location').required = false;
            } else {
                hospitalFields.style.display = 'block';
                donorFields.style.display = 'none';
                // Make hospital fields required
                document.getElementById('hospital_name').required = true;
                document.getElementById('location').required = true;
                document.getElementById('full_name').required = false;
                document.getElementById('blood_type').required = false;
                document.getElementById('birth_date').required = false;
            }
            
            // Make common fields required
            document.getElementById('username').required = true;
            document.getElementById('password').required = true;
            document.getElementById('confirm_password').required = true;
        }
        
        // Go back to Step 1
        function goToStep1() {
            document.getElementById('stepFillInfo').classList.remove('active');
            document.getElementById('stepSelectType').classList.add('active');
            document.getElementById('step2').classList.remove('active');
        }
        
        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters!');
                return false;
            }
        });
    </script>
</body>
</html>