<?php

session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get_users':
            $stmt = $pdo->query("
                SELECT 
                    u.UserID, 
                    u.Username, 
                    u.Role, 
                    u.RelatedID,
                    u.CreatedAt,
                    d.FullName as DonorName,
                    h.Name as HospitalName,
                    CASE 
                        WHEN u.Role = 'Donor' THEN d.FullName
                        WHEN u.Role = 'Hospital' THEN h.Name
                        ELSE NULL
                    END as RelatedName
                FROM User u
                LEFT JOIN Donor d ON u.Role = 'Donor' AND u.RelatedID = d.DonorID
                LEFT JOIN Hospital h ON u.Role = 'Hospital' AND u.RelatedID = h.HospitalID
                ORDER BY u.UserID DESC
            ");
            echo json_encode($stmt->fetchAll());
            break;
            
        case 'get_donors':
            $stmt = $pdo->query("
                SELECT 
                    d.*,
                    GetDonorAge(d.DonorID) AS Age,
                    u.UserID,
                    u.Username
                FROM Donor d, User u
                WHERE u.RelatedID = d.DonorID 
                  AND u.Role = 'Donor'
                ORDER BY d.DonorID DESC
            ");
            echo json_encode($stmt->fetchAll());
            break;
            
        case 'get_hospitals':
            $stmt = $pdo->query("
                SELECT 
                    h.*,
                    u.UserID,
                    u.Username
                FROM Hospital h, User u
                WHERE u.RelatedID = h.HospitalID 
                  AND u.Role = 'Hospital'
                ORDER BY h.HospitalID DESC
            ");
            echo json_encode($stmt->fetchAll());
            break;
            
        case 'get_requests':
            $stmt = $pdo->query("
                SELECT dr.*, h.Name as HospitalName 
                FROM DonationRequest dr, Hospital h
                WHERE dr.HospitalID = h.HospitalID 
                ORDER BY dr.RequestID DESC
            ");
            echo json_encode($stmt->fetchAll());
            break;
            
        case 'add_user':
            $username = $_POST['username'];
            $password = $_POST['password'];
            $role = $_POST['role'];
            $relatedId = null;
            
            try {
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("SELECT UserID FROM User WHERE Username = ?");
                $stmt->execute([$username]);
                if ($stmt->fetch()) {
                    throw new Exception('Username already exists');
                }
                
                if ($role === 'Donor') {
                    $fullName = $_POST['full_name'] ?? ucfirst($username);
                    $bloodType = $_POST['blood_type'] ?? 'O+';
                    $phone = $_POST['phone'] ?? null;
                    $birthDate = $_POST['birth_date'] ?? '1990-01-01';
                    $gender = $_POST['gender'] ?? 'Male';
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO Donor (FullName, BirthDate, Gender, BloodType, Phone, MedicalConditions) 
                        VALUES (?, ?, ?, ?, ?, 'None')
                    ");
                    $stmt->execute([$fullName, $birthDate, $gender, $bloodType, $phone]);
                    $relatedId = $pdo->lastInsertId();
                }
                
                elseif ($role === 'Hospital') {
                    $hospitalName = $_POST['hospital_name'] ?? (ucfirst($username) . ' Hospital');
                    $location = $_POST['location'] ?? 'Default Location';
                    $hospitalPhone = $_POST['hospital_phone'] ?? null;
                    
                    $stmt = $pdo->prepare("
                        INSERT INTO Hospital (Name, Location, Phone) 
                        VALUES (?, ?, ?)
                    ");
                    $stmt->execute([$hospitalName, $location, $hospitalPhone]);
                    $relatedId = $pdo->lastInsertId();
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO User (Username, PasswordHash, Role, RelatedID) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$username, $password, $role, $relatedId]);
                
                $pdo->commit();
                
                echo json_encode(['success' => true, 'message' => 'User added successfully']);
                
            } catch (Exception $e) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;
            
        case 'edit_user':
            $userId = $_POST['user_id'];
            $username = $_POST['username'];
            $password = $_POST['password'] ?? '';
            
            try {
                if (!empty($password)) {
                    $stmt = $pdo->prepare("UPDATE User SET Username = ?, PasswordHash = ? WHERE UserID = ?");
                    $stmt->execute([$username, $password, $userId]);
                } else {
                    $stmt = $pdo->prepare("UPDATE User SET Username = ? WHERE UserID = ?");
                    $stmt->execute([$username, $userId]);
                }
                
                echo json_encode(['success' => true, 'message' => 'User updated successfully']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;
            
        case 'edit_donor':
            $donorId = $_POST['donor_id'];
            $fullName = $_POST['full_name'];
            $bloodType = $_POST['blood_type'];
            $gender = $_POST['gender'];
            $birthDate = $_POST['birth_date'];
            $phone = $_POST['phone'];
            $medicalConditions = $_POST['medical_conditions'];
            
            try {
                $stmt = $pdo->prepare("
                    UPDATE Donor 
                    SET FullName = ?, BloodType = ?, Gender = ?, BirthDate = ?, Phone = ?, MedicalConditions = ?
                    WHERE DonorID = ?
                ");
                $stmt->execute([$fullName, $bloodType, $gender, $birthDate, $phone, $medicalConditions, $donorId]);
                
                echo json_encode(['success' => true, 'message' => 'Donor updated successfully']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;
        
        case 'edit_hospital':
            $hospitalId = $_POST['hospital_id'];
            $name = $_POST['name'];
            $location = $_POST['location'];
            $phone = $_POST['phone'];
            
            try {
                $stmt = $pdo->prepare("
                    UPDATE Hospital 
                    SET Name = ?, Location = ?, Phone = ?
                    WHERE HospitalID = ?
                ");
                $stmt->execute([$name, $location, $phone, $hospitalId]);
                
                echo json_encode(['success' => true, 'message' => 'Hospital updated successfully']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;
            
        case 'delete_user':
            $userId = $_GET['id'];
            
            try {
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("SELECT Role, RelatedID FROM User WHERE UserID = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch();
                
                if ($user) {
                    $role = $user['Role'];
                    $relatedId = $user['RelatedID'];
                    
                    if ($role === 'Donor' && $relatedId) {
                        $stmt = $pdo->prepare("DELETE FROM DonationAppointment WHERE DonorID = ?");
                        $stmt->execute([$relatedId]);
                        $stmt = $pdo->prepare("DELETE FROM Donor WHERE DonorID = ?");
                        $stmt->execute([$relatedId]);
                    }
                    
                    elseif ($role === 'Hospital' && $relatedId) {
                        $stmt = $pdo->prepare("SELECT RequestID FROM DonationRequest WHERE HospitalID = ?");
                        $stmt->execute([$relatedId]);
                        $requests = $stmt->fetchAll();
                        
                        foreach ($requests as $request) {
                            $stmt = $pdo->prepare("DELETE FROM DonationAppointment WHERE RequestID = ?");
                            $stmt->execute([$request['RequestID']]);
                        }
                        
                        $stmt = $pdo->prepare("DELETE FROM DonationRequest WHERE HospitalID = ?");
                        $stmt->execute([$relatedId]);
                        
                        $stmt = $pdo->prepare("DELETE FROM BloodStock WHERE HospitalID = ?");
                        $stmt->execute([$relatedId]);
                        
                        $stmt = $pdo->prepare("DELETE FROM Hospital WHERE HospitalID = ?");
                        $stmt->execute([$relatedId]);
                    }
                    
                    $stmt = $pdo->prepare("DELETE FROM User WHERE UserID = ?");
                    $stmt->execute([$userId]);
                }
                
                $pdo->commit();
                
                echo json_encode(['success' => true, 'message' => 'User and all related data deleted successfully']);
            } catch (Exception $e) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;
            
        case 'delete_donor':
            $donorId = $_GET['id'];
            
            try {
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("DELETE FROM DonationAppointment WHERE DonorID = ?");
                $stmt->execute([$donorId]);
                
                $stmt = $pdo->prepare("DELETE FROM User WHERE RelatedID = ? AND Role = 'Donor'");
                $stmt->execute([$donorId]);
                
                $stmt = $pdo->prepare("DELETE FROM Donor WHERE DonorID = ?");
                $stmt->execute([$donorId]);
                
                $pdo->commit();
                
                echo json_encode(['success' => true, 'message' => 'Donor and related user deleted successfully']);
            } catch (Exception $e) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;
            
        case 'delete_hospital':
            $hospitalId = $_GET['id'];
            
            try {
                $pdo->beginTransaction();
                
                $stmt = $pdo->prepare("SELECT RequestID FROM DonationRequest WHERE HospitalID = ?");
                $stmt->execute([$hospitalId]);
                $requests = $stmt->fetchAll();
                
                foreach ($requests as $request) {
                    $stmt = $pdo->prepare("DELETE FROM DonationAppointment WHERE RequestID = ?");
                    $stmt->execute([$request['RequestID']]);
                }
                
                $stmt = $pdo->prepare("DELETE FROM DonationRequest WHERE HospitalID = ?");
                $stmt->execute([$hospitalId]);
                
                $stmt = $pdo->prepare("DELETE FROM BloodStock WHERE HospitalID = ?");
                $stmt->execute([$hospitalId]);
                
                $stmt = $pdo->prepare("DELETE FROM User WHERE RelatedID = ? AND Role = 'Hospital'");
                $stmt->execute([$hospitalId]);
                
                $stmt = $pdo->prepare("DELETE FROM Hospital WHERE HospitalID = ?");
                $stmt->execute([$hospitalId]);
                
                $pdo->commit();
                
                echo json_encode(['success' => true, 'message' => 'Hospital and related user deleted successfully']);
            } catch (Exception $e) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>