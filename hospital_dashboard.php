<?php

session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Hospital') {
    header("Location: login.php");
    exit();
}

$hospital_id = $_SESSION['related_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Dashboard</title>
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
            background: #16a085;
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
            background: #e74c3c;
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
            color: #16a085;
            border-bottom-color: #16a085;
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
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 5px;
            transition: all 0.3s;
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
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-1px);
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
        
        .form-group input,
        .form-group select {
            width: 100%;
            max-width: 400px;
            padding: 10px;
            border: 2px solid #ecf0f1;
            border-radius: 5px;
            font-size: 14px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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
        
        .status-pending {
            background: #3498db;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
        }
        
        .empty-state-icon {
            font-size: 60px;
            margin-bottom: 15px;
            opacity: 0.5;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🏥 Hospital Dashboard</h1>
        <div>
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <div class="tabs">
            <button class="tab active" onclick="showTab('requests')">My Requests</button>
            <button class="tab" onclick="showTab('appointments')">Appointments</button>
            <button class="tab" onclick="showTab('stock')">Blood Stock</button>
        </div>
        
        <div id="requests" class="tab-content active">
            <h2>Create Donation Request</h2>
            <form id="createRequestForm">
                <div class="form-group">
                    <label>Blood Type</label>
                    <select name="blood_type" required>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Quantity Needed</label>
                    <input type="number" name="quantity" min="1" required>
                </div>
                <button type="submit" class="btn btn-success">Create Request</button>
            </form>
            
            <h2 style="margin-top: 30px;">Active Donation Requests</h2>
            <div id="myRequestsTable"></div>
        </div>
        
        <div id="appointments" class="tab-content">
            <h2>Scheduled Appointments</h2>
            <div id="appointmentsTable"></div>
        </div>
        
        <div id="stock" class="tab-content">
            <h2>Update Blood Stock</h2>
            <form id="updateStockForm">
                <div class="form-group">
                    <label>Blood Type</label>
                    <select name="blood_type" required>
                        <option value="A+">A+</option>
                        <option value="A-">A-</option>
                        <option value="B+">B+</option>
                        <option value="B-">B-</option>
                        <option value="AB+">AB+</option>
                        <option value="AB-">AB-</option>
                        <option value="O+">O+</option>
                        <option value="O-">O-</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" name="quantity" min="0" required>
                </div>
                <button type="submit" class="btn btn-success">Update Stock</button>
            </form>
            
            <h2 style="margin-top: 30px;">Current Blood Stock</h2>
            <div id="stockTable"></div>
        </div>
    </div>
    
    <script>
        const hospitalId = <?php echo $hospital_id; ?>;
        
        // Tab switching
        function showTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
            
            if (tabName === 'requests') loadMyRequests();
            else if (tabName === 'appointments') loadAppointments();
            else if (tabName === 'stock') loadStock();
        }
        
        // Create Request
        document.getElementById('createRequestForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            formData.append('hospital_id', hospitalId);
            
            const response = await fetch('api/hospital_api.php?action=create_request', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            if (result.success) {
                alert('Request created successfully!');
                e.target.reset();
                loadMyRequests();
            } else {
                alert('Error: ' + result.message);
            }
        });
        
        // Load My Requests 
        async function loadMyRequests() {
            const response = await fetch(`api/hospital_api.php?action=get_requests&hospital_id=${hospitalId}`);
            const allRequests = await response.json();
            
            // Filter only Pending requests
            const requests = allRequests.filter(req => req.Status === 'Pending');
            
            let html = '';
            
            if (requests.length === 0) {
                html = `<div class="empty-state">
                    <div class="empty-state-icon">📋</div>
                    <p>No active requests. Create a new request to get started.</p>
                </div>`;
            } else {
                html = '<table><thead><tr><th>ID</th><th>Blood Type</th><th>Quantity</th><th>Status</th><th>Request Date</th><th>Actions</th></tr></thead><tbody>';
                requests.forEach(req => {
                    html += `<tr>
                        <td>${req.RequestID}</td>
                        <td>${req.BloodType}</td>
                        <td>${req.QuantityNeeded}</td>
                        <td><span class="status-badge status-pending">${req.Status}</span></td>
                        <td>${req.RequestDate}</td>
                        <td>
                            <button class="btn btn-danger" onclick="cancelRequest(${req.RequestID})">Cancel</button>
                        </td>
                    </tr>`;
                });
                html += '</tbody></table>';
            }
            
            document.getElementById('myRequestsTable').innerHTML = html;
        }
        
        // Load Appointments - Using VIEW
        async function loadAppointments() {
            const response = await fetch(`api/hospital_api.php?action=get_appointments_view&hospital_id=${hospitalId}`);
            const allAppointments = await response.json();
            
            // Filter only Scheduled appointments
            const appointments = allAppointments.filter(apt => apt.AppointmentStatus === 'Scheduled');
            
            let html = '';
            
            if (appointments.length === 0) {
                html = `<div class="empty-state">
                    <div class="empty-state-icon">📅</div>
                    <p>No scheduled appointments at the moment.</p>
                </div>`;
            } else {
                html = '<table><thead><tr><th>ID</th><th>Donor</th><th>Blood Type</th><th>Units</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
                appointments.forEach(apt => {
                    const units = apt.Units || 1;
                    html += `<tr>
                        <td>${apt.AppointmentID}</td>
                        <td>${apt.DonorName}</td>
                        <td>${apt.BloodType}</td>
                        <td>${units}</td>
                        <td>${apt.AppointmentDate}</td>
                        <td><span class="status-badge status-scheduled">${apt.AppointmentStatus}</span></td>
                        <td>
                            <button class="btn btn-success" onclick="markAsDone(${apt.AppointmentID}, ${apt.DonorID})">Mark as Done</button>
                            <button class="btn btn-danger" onclick="deleteAppointment(${apt.AppointmentID})">Delete</button>
                        </td>
                    </tr>`;
                });
                html += '</tbody></table>';
            }
            
            document.getElementById('appointmentsTable').innerHTML = html;
        }
        // Mark Appointment as Done
        async function markAsDone(appointmentId, donorId) {
            if (!confirm('Mark this appointment as completed? This will update the donor\'s last donation date and increase blood stock.')) return;
            
            const formData = new FormData();
            formData.append('appointment_id', appointmentId);
            formData.append('donor_id', donorId);
            
            const response = await fetch('api/hospital_api.php?action=mark_done', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            if (result.success) {
                alert(result.message);
                loadAppointments();
                loadMyRequests();
                loadStock();
            } else {
                alert('Error: ' + result.message);
            }
        }
        
        // Cancel Request
        async function cancelRequest(requestId) {
            if (!confirm('Are you sure you want to cancel this request? All related appointments will be deleted.')) return;
            
            const response = await fetch(`api/hospital_api.php?action=cancel_request&request_id=${requestId}`, {
                method: 'DELETE'
            });
            
            const result = await response.json();
            if (result.success) {
                alert('Request cancelled successfully!');
                loadMyRequests();
                loadAppointments();
            } else {
                alert('Error: ' + result.message);
            }
        }
        
        // Delete Appointment
        async function deleteAppointment(appointmentId) {
            if (!confirm('Are you sure you want to delete this appointment?')) return;
            
            const response = await fetch(`api/hospital_api.php?action=delete_appointment&appointment_id=${appointmentId}`, {
                method: 'DELETE'
            });
            
            const result = await response.json();
            if (result.success) {
                alert('Appointment deleted successfully!');
                loadAppointments();
                loadMyRequests();
            } else {
                alert('Error: ' + result.message);
            }
        }
        
        // Update Stock
        document.getElementById('updateStockForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            formData.append('hospital_id', hospitalId);
            
            const response = await fetch('api/hospital_api.php?action=update_stock', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            if (result.success) {
                alert('Stock updated successfully!');
                e.target.reset();
                loadStock();
            } else {
                alert('Error: ' + result.message);
            }
        });
        
        // Load Stock
        async function loadStock() {
            const response = await fetch(`api/hospital_api.php?action=get_stock&hospital_id=${hospitalId}`);
            const stock = await response.json();
            
            let html = '';
            
            if (stock.length === 0) {
                html = `<div class="empty-state">
                    <div class="empty-state-icon">🩸</div>
                    <p>No blood stock data available. Update stock to add entries.</p>
                </div>`;
            } else {
                html = '<table><thead><tr><th>Blood Type</th><th>Quantity</th><th>Last Update</th></tr></thead><tbody>';
                stock.forEach(item => {
                    html += `<tr>
                        <td>${item.BloodType}</td>
                        <td>${item.Quantity}</td>
                        <td>${item.LastUpdate}</td>
                    </tr>`;
                });
                html += '</tbody></table>';
            }
            
            document.getElementById('stockTable').innerHTML = html;
        }
        
        // Load initial data
        loadMyRequests();
    </script>
</body>
</html>