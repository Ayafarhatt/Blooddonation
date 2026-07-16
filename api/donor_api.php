<?php

session_start();
require_once '../db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Donor') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'get_available_requests':
            $donorId = $_GET['donor_id'];
            
            $stmt = $pdo->prepare("SELECT BloodType FROM Donor WHERE DonorID = ?");
            $stmt->execute([$donorId]);
            $donor = $stmt->fetch();
            
            if (!$donor) {
                echo json_encode(['error' => 'Donor not found']);
                exit();
            }
            
            $donorBloodType = $donor['BloodType'];

            $stmt = $pdo->query("
                SELECT dr.*, h.Name as HospitalName 
                FROM DonationRequest dr, Hospital h
                WHERE dr.HospitalID = h.HospitalID 
                  AND dr.Status = 'Pending'
                ORDER BY dr.RequestDate DESC
            ");
            $requests = $stmt->fetchAll();
        
            foreach ($requests as &$request) {
                $request['CanDonate'] = ($request['BloodType'] === $donorBloodType);
            }
            
            echo json_encode(['requests' => $requests, 'donorBloodType' => $donorBloodType]);
            break;
            
        case 'schedule_appointment':
            $donorId = $_POST['donor_id'];
            $requestId = $_POST['request_id'];
            $appointmentDate = $_POST['appointment_date'];
            $units = intval($_POST['units'] ?? 1);
            $requestBloodType = $_POST['request_blood_type'];
            
            try {
                $stmt = $pdo->prepare("SELECT BloodType, LastDonationDate FROM Donor WHERE DonorID = ?");
                $stmt->execute([$donorId]);
                $donor = $stmt->fetch();
                
                if (!$donor) {
                    echo json_encode(['success' => false, 'message' => 'Donor not found']);
                    exit();
                }
                
                if ($donor['BloodType'] !== $requestBloodType) {
                    echo json_encode(['success' => false, 'message' => 'Blood type mismatch!']);
                    exit();
                }
                
                if ($donor['LastDonationDate']) {
                    $lastDonation = new DateTime($donor['LastDonationDate']);
                    $appointmentDateTime = new DateTime($appointmentDate);
                    $interval = $lastDonation->diff($appointmentDateTime);
                    $monthsDiff = ($interval->y * 12) + $interval->m;
                    
                    if ($monthsDiff < 3) {
                        $nextAllowedDate = clone $lastDonation;
                        $nextAllowedDate->modify('+3 months');
                        
                        echo json_encode([
                            'success' => false, 
                            'message' => "You must wait 3 months between donations.\n\nLast donation: {$donor['LastDonationDate']}\nEarliest date: {$nextAllowedDate->format('Y-m-d')}"
                        ]);
                        exit();
                    }
                }
                
                $stmt = $pdo->prepare("SELECT QuantityNeeded FROM DonationRequest WHERE RequestID = ?");
                $stmt->execute([$requestId]);
                $request = $stmt->fetch();
                
                if ($request['QuantityNeeded'] < $units) {
                    echo json_encode(['success' => false, 'message' => "Request only needs {$request['QuantityNeeded']} units."]);
                    exit();
                }
                
                $appointmentDateTime = new DateTime($appointmentDate);
                $today = new DateTime();
                $today->setTime(0, 0, 0);
                
                if ($appointmentDateTime < $today) {
                    echo json_encode(['success' => false, 'message' => 'Appointment must be in the future.']);
                    exit();
                }
                
                $stmt = $pdo->prepare("CALL CreateAppointment(?, ?, ?, ?)");
                $stmt->execute([$donorId, $requestId, $appointmentDate, $units]);
                
                echo json_encode(['success' => true, 'message' => 'Appointment scheduled successfully']);
                
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;
            
        case 'get_my_appointments':
            $donorId = $_GET['donor_id'];
            $stmt = $pdo->prepare("
                SELECT da.*, h.Name as HospitalName, dr.BloodType 
                FROM DonationAppointment da, DonationRequest dr, Hospital h
                WHERE da.RequestID = dr.RequestID
                  AND dr.HospitalID = h.HospitalID
                  AND da.DonorID = ?
                ORDER BY da.AppointmentDate DESC
            ");
            $stmt->execute([$donorId]);
            echo json_encode($stmt->fetchAll());
            break;
            
        case 'cancel_appointment':
            $appointmentId = $_GET['appointment_id'];
            
            try {
                $stmt = $pdo->prepare("DELETE FROM DonationAppointment WHERE AppointmentID = ?");
                $stmt->execute([$appointmentId]);
                
                echo json_encode(['success' => true, 'message' => 'Appointment cancelled successfully']);
            } catch (Exception $e) {
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