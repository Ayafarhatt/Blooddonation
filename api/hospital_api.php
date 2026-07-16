<?php

session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Hospital') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'create_request':
            $hospitalId = $_POST['hospital_id'];
            $bloodType = $_POST['blood_type'];
            $quantity = $_POST['quantity'];
            
            $stmt = $pdo->prepare("CALL CreateRequest(?, ?, ?)");
            $stmt->execute([$hospitalId, $bloodType, $quantity]);
            
            echo json_encode(['success' => true, 'message' => 'Request created successfully']);
            break;
            
        case 'get_requests':
            $hospitalId = $_GET['hospital_id'];
            $stmt = $pdo->prepare("SELECT * FROM DonationRequest WHERE HospitalID = ? ORDER BY RequestID DESC");
            $stmt->execute([$hospitalId]);
            echo json_encode($stmt->fetchAll());
            break;
            
        case 'get_appointments_view':
            $hospitalId = $_GET['hospital_id'];
            $stmt = $pdo->prepare("
                SELECT * 
                FROM DonorAppointments
                WHERE HospitalID = ?
                ORDER BY AppointmentDate DESC
            ");
            $stmt->execute([$hospitalId]);
            echo json_encode($stmt->fetchAll());
            break;
            
        case 'mark_done':
            $appointmentId = $_POST['appointment_id'];
            $donorId = $_POST['donor_id'];
            
            try {
                $pdo->beginTransaction();
            
                $stmt = $pdo->prepare("
                    SELECT da.RequestID, da.Units, dr.BloodType, dr.HospitalID 
                    FROM DonationAppointment da, DonationRequest dr
                    WHERE da.RequestID = dr.RequestID
                      AND da.AppointmentID = ?
                ");
                $stmt->execute([$appointmentId]);
                $appointment = $stmt->fetch();
                
                if (!$appointment) {
                    throw new Exception('Appointment not found');
                }
                
                $requestId = $appointment['RequestID'];
                $units = $appointment['Units'] ?? 1;
                $bloodType = $appointment['BloodType'];
                $hospitalId = $appointment['HospitalID'];
                
                $stmt = $pdo->prepare("UPDATE DonationAppointment SET Status = 'Completed' WHERE AppointmentID = ?");
                $stmt->execute([$appointmentId]);
                
                $stmt = $pdo->prepare("UPDATE DonationRequest SET QuantityNeeded = QuantityNeeded - ? WHERE RequestID = ?");
                $stmt->execute([$units, $requestId]);
            
                $stmt = $pdo->prepare("SELECT StockID FROM BloodStock WHERE HospitalID = ? AND BloodType = ?");
                $stmt->execute([$hospitalId, $bloodType]);
                $stock = $stmt->fetch();
                
                if ($stock) {
                    $stmt = $pdo->prepare("UPDATE BloodStock SET Quantity = Quantity + ?, LastUpdate = CURRENT_DATE() WHERE HospitalID = ? AND BloodType = ?");
                    $stmt->execute([$units, $hospitalId, $bloodType]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO BloodStock (HospitalID, BloodType, Quantity, LastUpdate) VALUES (?, ?, ?, CURRENT_DATE())");
                    $stmt->execute([$hospitalId, $bloodType, $units]);
                }
        
                $stmt = $pdo->prepare("SELECT QuantityNeeded FROM DonationRequest WHERE RequestID = ?");
                $stmt->execute([$requestId]);
                $request = $stmt->fetch();
                
                if ($request['QuantityNeeded'] <= 0) {
                    $stmt = $pdo->prepare("UPDATE DonationRequest SET Status = 'Completed' WHERE RequestID = ?");
                    $stmt->execute([$requestId]);
                }
                
                $pdo->commit();
                
                echo json_encode([
                    'success' => true, 
                    'message' => "Appointment completed! {$units} units of {$bloodType} blood added to stock."
                ]);
            } catch (Exception $e) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;
            
        case 'cancel_request':
            $requestId = $_GET['request_id'];
            
            try {
                $pdo->beginTransaction();
            
                $stmt = $pdo->prepare("DELETE FROM DonationAppointment WHERE RequestID = ?");
                $stmt->execute([$requestId]);
            
                $stmt = $pdo->prepare("DELETE FROM DonationRequest WHERE RequestID = ?");
                $stmt->execute([$requestId]);
                
                $pdo->commit();
                
                echo json_encode(['success' => true, 'message' => 'Request cancelled successfully']);
            } catch (Exception $e) {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;
        
        case 'delete_appointment':
            $appointmentId = $_GET['appointment_id'];
            
            try {
                $stmt = $pdo->prepare("DELETE FROM DonationAppointment WHERE AppointmentID = ?");
                $stmt->execute([$appointmentId]);
                
                echo json_encode(['success' => true, 'message' => 'Appointment deleted successfully']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;
            
        case 'update_stock':
            $hospitalId = $_POST['hospital_id'];
            $bloodType = $_POST['blood_type'];
            $quantity = $_POST['quantity'];
            
            $stmt = $pdo->prepare("SELECT StockID FROM BloodStock WHERE HospitalID = ? AND BloodType = ?");
            $stmt->execute([$hospitalId, $bloodType]);
            $exists = $stmt->fetch();
            
            if ($exists) {
                $stmt = $pdo->prepare("UPDATE BloodStock SET Quantity = ?, LastUpdate = CURRENT_DATE() WHERE HospitalID = ? AND BloodType = ?");
                $stmt->execute([$quantity, $hospitalId, $bloodType]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO BloodStock (HospitalID, BloodType, Quantity, LastUpdate) VALUES (?, ?, ?, CURRENT_DATE())");
                $stmt->execute([$hospitalId, $bloodType, $quantity]);
            }
            
            echo json_encode(['success' => true, 'message' => 'Stock updated successfully']);
            break;
            
        case 'get_stock':
            $hospitalId = $_GET['hospital_id'];
            $stmt = $pdo->prepare("SELECT * FROM BloodStock WHERE HospitalID = ? ORDER BY BloodType");
            $stmt->execute([$hospitalId]);
            echo json_encode($stmt->fetchAll());
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>