<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Donor') {
    header("Location: login.php");
    exit();
}

$donor_id = $_SESSION['related_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
        }
        
        .navbar {
            background: #e74c3c;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .navbar h1 {
            font-size: 24px;
        }
        
        .logout-btn {
            background: #c0392b;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .tab {
            padding: 12px 24px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            color: #7f8c8d;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .tab.active {
            color: #e74c3c;
            border-bottom-color: #e74c3c;
        }
        
        .tab-content {
            display: none;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .tab-content.active {
            display: block;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-success {
            background: #2ecc71;
            color: white;
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background: white;
            margin: 100px auto;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .close {
            font-size: 28px;
            cursor: pointer;
            color: #7f8c8d;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #2c3e50;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 2px solid #ecf0f1;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 5px;
            color: white;
            font-size: 13px;
            font-weight: 500;
        }
        
        .status-completed {
            background: #2ecc71;
        }
        
        .status-scheduled {
            background: #f39c12;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🩸 Donor Dashboard</h1>
        <div>
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <div class="tabs">
            <button class="tab active" onclick="showTab('available')">Available Requests</button>
            <button class="tab" onclick="showTab('myappointments')">My Appointments</button>
        </div>
        
        <div id="available" class="tab-content active">
            <h2>Available Donation Requests</h2>
            <div id="availableRequestsTable"></div>
        </div>
        
        <div id="myappointments" class="tab-content">
            <h2>My Scheduled Appointments</h2>
            <div id="myAppointmentsTable"></div>
        </div>
    </div>
    
    <div id="scheduleModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Schedule Appointment</h2>
            <span class="close" onclick="closeModal()">&times;</span>
        </div>
        <form id="scheduleForm">
            <input type="hidden" id="requestId" name="request_id">
            <input type="hidden" id="requestBloodType" name="request_blood_type">
            <div class="form-group">
                <label>Appointment Date *</label>
                <input type="date" name="appointment_date" required>
            </div>
            <div class="form-group">
                <label>Units to Donate *</label>
                <input type="number" name="units" min="1" max="3" value="1" required>
                <small style="color: #7f8c8d; font-size: 12px;">You can donate 1-3 units per appointment</small>
            </div>
            <button type="submit" class="btn btn-success">Schedule</button>
        </form>
    </div>
</div>
    
    <script>
        const donorId = <?php echo $donor_id; ?>;
        
        // Tab switching
        function showTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
            
            if (tabName === 'available') loadAvailableRequests();
            else if (tabName === 'myappointments') loadMyAppointments();
        }
        
        // Load Available Requests
        async function loadAvailableRequests() {
           const response = await fetch(`api/donor_api.php?action=get_available_requests&donor_id=${donorId}`);
           const data = await response.json();
    
           let html = '<table><thead><tr><th>Hospital</th><th>Blood Type</th><th>Quantity Needed</th><th>Request Date</th><th>Action</th></tr></thead><tbody>';
    
           if (data.error) {
               html += `<tr><td colspan="5" style="text-align: center; color: #e74c3c;">${data.error}</td></tr>`;
            } else {
               data.requests.forEach(req => {
                  html += `<tr>
                    <td>${req.HospitalName}</td>
                    <td>${req.BloodType}</td>
                    <td>${req.QuantityNeeded}</td>
                    <td>${req.RequestDate}</td>
                    <td>`;
            
                  if (req.CanDonate) {
                     html += `<button class="btn btn-primary" onclick="showScheduleModal(${req.RequestID}, '${req.BloodType}')">Schedule</button>`;
                   } else {
                     html += `<span style="color: #e74c3c;">Blood type mismatch</span>`;
                   }
            
                   html += `</td></tr>`;
            });
        }
    
       html += '</tbody></table>';
       document.getElementById('availableRequestsTable').innerHTML = html;
    }
        
        // Load My Appointments
        async function loadMyAppointments() {
          const response = await fetch(`api/donor_api.php?action=get_my_appointments&donor_id=${donorId}`);
          const appointments = await response.json();
    
          let html = '<table><thead><tr><th>Hospital</th><th>Blood Type</th><th>Units</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
           appointments.forEach(apt => {
             const statusClass = apt.Status === 'Completed' ? 'status-completed' : 'status-scheduled';
             const units = apt.Units || 1;
             html += `<tr>
               <td>${apt.HospitalName}</td>
               <td>${apt.BloodType}</td>
               <td>${units}</td>
               <td>${apt.AppointmentDate}</td>
               <td><span class="status-badge ${statusClass}">${apt.Status}</span></td>
               <td>`;
        
           if (apt.Status === 'Scheduled') {
               html += `<button class="btn btn-danger" onclick="cancelAppointment(${apt.AppointmentID})">Cancel</button>`;
            } else {
               html += `<span style="color: #7f8c8d;">Completed</span>`;
            }
        
            html += `</td></tr>`;
        });
        html += '</tbody></table>';
        document.getElementById('myAppointmentsTable').innerHTML = html;
    }
        
        // Show Schedule Modal
        function showScheduleModal(requestId, bloodType) {
           document.getElementById('requestId').value = requestId;
           document.getElementById('requestBloodType').value = bloodType;
           document.getElementById('scheduleModal').style.display = 'block';
        }
        
        // Close Modal
        function closeModal() {
            document.getElementById('scheduleModal').style.display = 'none';
        }
        
        // Schedule Appointment
        document.getElementById('scheduleForm').addEventListener('submit', async (e) => {
           e.preventDefault();
           const formData = new FormData(e.target);
           formData.append('donor_id', donorId);
    
           const response = await fetch('api/donor_api.php?action=schedule_appointment', {
              method: 'POST',
              body: formData
            });
    
           const result = await response.json();
           if (result.success) {
               alert('Appointment scheduled successfully!');
               closeModal();
               e.target.reset();
               loadAvailableRequests();
                loadMyAppointments();
           } else {
               alert('Error: ' + result.message);
            }
      });
        
        // Cancel Appointment
        async function cancelAppointment(appointmentId) {
            if (!confirm('Are you sure you want to cancel this appointment?')) return;
            
            const response = await fetch(`api/donor_api.php?action=cancel_appointment&appointment_id=${appointmentId}`, {
                method: 'DELETE'
            });
            
            const result = await response.json();
            if (result.success) {
                alert('Appointment cancelled successfully!');
                loadMyAppointments();
                loadAvailableRequests();
            } else {
                alert('Error: ' + result.message);
            }
        }
        
        // Load initial data
        loadAvailableRequests();
    </script>
</body>
</html>