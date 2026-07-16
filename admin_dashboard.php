<?php

session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit();
}

// Fetch statistics
$stats = [];
$stats['total_donors'] = $pdo->query("SELECT COUNT(*) FROM Donor")->fetchColumn();
$stats['total_hospitals'] = $pdo->query("SELECT COUNT(*) FROM Hospital")->fetchColumn();
$stats['pending_requests'] = $pdo->query("SELECT COUNT(*) FROM DonationRequest WHERE Status = 'Pending'")->fetchColumn();
$stats['total_appointments'] = $pdo->query("SELECT COUNT(*) FROM DonationAppointment")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
            background: #2c3e50;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .navbar h1 {
            font-size: 24px;
        }
        
        .navbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .stat-card .number {
            font-size: 36px;
            font-weight: bold;
            color: #2c3e50;
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
            color: #3498db;
            border-bottom-color: #3498db;
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
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 5px;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-success {
            background: #2ecc71;
            color: white;
        }
        
        .btn-warning {
            background: #f39c12;
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
            overflow-y: auto;
        }
        
        .modal-content {
            background: white;
            margin: 30px auto;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            max-height: 85vh;
            overflow-y: auto;
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
        
        .close:hover {
            color: #2c3e50;
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
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 2px solid #ecf0f1;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .form-group small {
            color: #7f8c8d;
            font-size: 12px;
        }
        
        hr {
            margin: 20px 0;
            border: none;
            border-top: 2px solid #ecf0f1;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>🩸 Admin Dashboard</h1>
        <div class="navbar-right">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
    
    <div class="container">

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Donors</h3>
                <div class="number"><?php echo $stats['total_donors']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Hospitals</h3>
                <div class="number"><?php echo $stats['total_hospitals']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Pending Requests</h3>
                <div class="number"><?php echo $stats['pending_requests']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Appointments</h3>
                <div class="number"><?php echo $stats['total_appointments']; ?></div>
            </div>
        </div>
        
        <div class="tabs">
            <button class="tab active" onclick="showTab('users')">User Management</button>
            <button class="tab" onclick="showTab('donors')">Donors</button>
            <button class="tab" onclick="showTab('hospitals')">Hospitals</button>
            <button class="tab" onclick="showTab('requests')">Donation Requests</button>
        </div>
        
        <div id="users" class="tab-content active">
            <h2>User Management</h2>
            <button class="btn btn-primary" onclick="showAddUserModal()">Add New User</button>
            <div id="usersTable"></div>
        </div>
        
        <div id="donors" class="tab-content">
            <h2>Donor Management</h2>
            <div id="donorsTable"></div>
        </div>
        
        <div id="hospitals" class="tab-content">
            <h2>Hospital Management</h2>
            <div id="hospitalsTable"></div>
        </div>
        
        <div id="requests" class="tab-content">
            <h2>Donation Requests</h2>
            <div id="requestsTable"></div>
        </div>
    </div>
    
    <div id="addUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New User</h2>
                <span class="close" onclick="closeModal('addUserModal')">&times;</span>
            </div>
            <form id="addUserForm">
                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Role *</label>
                    <select name="role" id="roleSelect" required>
                        <option value="">-- Select Role --</option>
                        <option value="Admin">Admin</option>
                        <option value="Hospital">Hospital</option>
                        <option value="Donor">Donor</option>
                    </select>
                </div>
                
                <div id="donorFields" style="display:none;">
                    <hr>
                    <h3 style="color: #2c3e50; margin-bottom: 15px;">Donor Information</h3>
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="full_name" placeholder="Enter full name">
                    </div>
                    <div class="form-group">
                        <label>Blood Type</label>
                        <select name="blood_type">
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
                        <label>Gender</label>
                        <select name="gender">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Birth Date</label>
                        <input type="date" name="birth_date" value="1990-01-01">
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" placeholder="e.g., 71-123456">
                    </div>
                </div>
                
                <div id="hospitalFields" style="display:none;">
                    <hr>
                    <h3 style="color: #2c3e50; margin-bottom: 15px;">Hospital Information</h3>
                    <div class="form-group">
                        <label>Hospital Name</label>
                        <input type="text" name="hospital_name" placeholder="Enter hospital name">
                    </div>
                    <div class="form-group">
                        <label>Location</label>
                        <input type="text" name="location" placeholder="Enter address/location">
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="hospital_phone" placeholder="e.g., 01-123456">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-success" style="margin-top: 20px;">Add User</button>
            </form>
        </div>
    </div>
    
    <div id="editUserModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit User</h2>
                <span class="close" onclick="closeModal('editUserModal')">&times;</span>
            </div>
            <form id="editUserForm">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username" id="edit_username" required>
                </div>
                <div class="form-group">
                    <label>New Password (leave empty to keep current)</label>
                    <input type="password" name="password" id="edit_password">
                </div>
                <div class="form-group">
                    <label>Role *</label>
                    <select name="role" id="edit_role" disabled>
                        <option value="Admin">Admin</option>
                        <option value="Hospital">Hospital</option>
                        <option value="Donor">Donor</option>
                    </select>
                    <small>Role cannot be changed after creation</small>
                </div>
                
                <button type="submit" class="btn btn-success" style="margin-top: 20px;">Update User</button>
            </form>
        </div>
    </div>
    
    <div id="editDonorModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Donor</h2>
                <span class="close" onclick="closeModal('editDonorModal')">&times;</span>
            </div>
            <form id="editDonorForm">
                <input type="hidden" name="donor_id" id="edit_donor_id">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" id="edit_donor_name" required>
                </div>
                <div class="form-group">
                    <label>Blood Type *</label>
                    <select name="blood_type" id="edit_donor_blood" required>
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
                    <select name="gender" id="edit_donor_gender" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Birth Date *</label>
                    <input type="date" name="birth_date" id="edit_donor_birth" required>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" id="edit_donor_phone">
                </div>
                <div class="form-group">
                    <label>Medical Conditions</label>
                    <input type="text" name="medical_conditions" id="edit_donor_medical">
                </div>
                <button type="submit" class="btn btn-success" style="margin-top: 20px;">Update Donor</button>
            </form>
        </div>
    </div>
    
    <div id="editHospitalModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Hospital</h2>
                <span class="close" onclick="closeModal('editHospitalModal')">&times;</span>
            </div>
            <form id="editHospitalForm">
                <input type="hidden" name="hospital_id" id="edit_hospital_id">
                <div class="form-group">
                    <label>Hospital Name *</label>
                    <input type="text" name="name" id="edit_hospital_name" required>
                </div>
                <div class="form-group">
                    <label>Location *</label>
                    <input type="text" name="location" id="edit_hospital_location" required>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" id="edit_hospital_phone">
                </div>
                <button type="submit" class="btn btn-success" style="margin-top: 20px;">Update Hospital</button>
            </form>
        </div>
    </div>
    
    <script>
        // Tab switching
        function showTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
            
            if (tabName === 'users') loadUsers();
            else if (tabName === 'donors') loadDonors();
            else if (tabName === 'hospitals') loadHospitals();
            else if (tabName === 'requests') loadRequests();
        }
        
        // Modal functions
        function showAddUserModal() {
            document.getElementById('addUserModal').style.display = 'block';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Show/Hide fields based on role
        document.getElementById('roleSelect').addEventListener('change', function() {
            const role = this.value;
            document.getElementById('donorFields').style.display = role === 'Donor' ? 'block' : 'none';
            document.getElementById('hospitalFields').style.display = role === 'Hospital' ? 'block' : 'none';
        });
        
        // Load Users
        async function loadUsers() {
            const response = await fetch('api/admin_api.php?action=get_users');
            const users = await response.json();
            
            let html = '<table><thead><tr><th>ID</th><th>Username</th><th>Role</th><th>Created At</th><th>Actions</th></tr></thead><tbody>';
            users.forEach(user => {
                html += `<tr>
                    <td>${user.UserID}</td>
                    <td>${user.Username}</td>
                    <td>${user.Role}</td>
                    <td>${user.CreatedAt}</td>
                    <td>
                        <button class="btn btn-warning" onclick="showEditUserModal(${user.UserID}, '${user.Username}', '${user.Role}')">Edit</button>
                        <button class="btn btn-danger" onclick="deleteUser(${user.UserID})">Delete</button>
                    </td>
                </tr>`;
            });
            html += '</tbody></table>';
            document.getElementById('usersTable').innerHTML = html;
        }
        
        // Show Edit User Modal
        function showEditUserModal(userId, username, role) {
            document.getElementById('edit_user_id').value = userId;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_role').value = role;
            document.getElementById('edit_password').value = '';
            document.getElementById('editUserModal').style.display = 'block';
        }
        
        // Load Donors
        async function loadDonors() {
            const response = await fetch('api/admin_api.php?action=get_donors');
            const donors = await response.json();
            
            let html = '<table><thead><tr><th>ID</th><th>Name</th><th>Blood Type</th><th>Gender</th><th>Birth Date</th><th>Phone</th><th>Last Donation</th><th>Actions</th></tr></thead><tbody>';
            donors.forEach(donor => {
                html += `<tr>
                    <td>${donor.DonorID}</td>
                    <td>${donor.FullName}</td>
                    <td>${donor.BloodType}</td>
                    <td>${donor.Gender}</td>
                    <td>${donor.BirthDate}</td>
                    <td>${donor.Phone || 'N/A'}</td>
                    <td>${donor.LastDonationDate || 'Never'}</td>
                    <td>
                        <button class="btn btn-warning" onclick='showEditDonorModal(${JSON.stringify(donor)})'>Edit</button>
                    </td>
                </tr>`;
            });
            html += '</tbody></table>';
            document.getElementById('donorsTable').innerHTML = html;
        }
        
        // Show Edit Donor Modal
        function showEditDonorModal(donor) {
            document.getElementById('edit_donor_id').value = donor.DonorID;
            document.getElementById('edit_donor_name').value = donor.FullName;
            document.getElementById('edit_donor_blood').value = donor.BloodType;
            document.getElementById('edit_donor_gender').value = donor.Gender;
            document.getElementById('edit_donor_birth').value = donor.BirthDate;
            document.getElementById('edit_donor_phone').value = donor.Phone || '';
            document.getElementById('edit_donor_medical').value = donor.MedicalConditions || '';
            document.getElementById('editDonorModal').style.display = 'block';
        }
        
        // Load Hospitals
        async function loadHospitals() {
            const response = await fetch('api/admin_api.php?action=get_hospitals');
            const hospitals = await response.json();
            
            let html = '<table><thead><tr><th>ID</th><th>Name</th><th>Location</th><th>Phone</th><th>Actions</th></tr></thead><tbody>';
            hospitals.forEach(hospital => {
                html += `<tr>
                    <td>${hospital.HospitalID}</td>
                    <td>${hospital.Name}</td>
                    <td>${hospital.Location}</td>
                    <td>${hospital.Phone || 'N/A'}</td>
                    <td>
                        <button class="btn btn-warning" onclick='showEditHospitalModal(${JSON.stringify(hospital)})'>Edit</button>
                    </td>
                </tr>`;
            });
            html += '</tbody></table>';
            document.getElementById('hospitalsTable').innerHTML = html;
        }
        
        // Show Edit Hospital Modal
        function showEditHospitalModal(hospital) {
            document.getElementById('edit_hospital_id').value = hospital.HospitalID;
            document.getElementById('edit_hospital_name').value = hospital.Name;
            document.getElementById('edit_hospital_location').value = hospital.Location;
            document.getElementById('edit_hospital_phone').value = hospital.Phone || '';
            document.getElementById('editHospitalModal').style.display = 'block';
        }
        
        // Load Requests
        async function loadRequests() {
            const response = await fetch('api/admin_api.php?action=get_requests');
            const requests = await response.json();
            
            let html = '<table><thead><tr><th>ID</th><th>Hospital</th><th>Blood Type</th><th>Quantity</th><th>Status</th><th>Request Date</th></tr></thead><tbody>';
            requests.forEach(req => {
                html += `<tr>
                    <td>${req.RequestID}</td>
                    <td>${req.HospitalName}</td>
                    <td>${req.BloodType}</td>
                    <td>${req.QuantityNeeded}</td>
                    <td>${req.Status}</td>
                    <td>${req.RequestDate}</td>
                </tr>`;
            });
            html += '</tbody></table>';
            document.getElementById('requestsTable').innerHTML = html;
        }
        
        // Add User Form Submit
        document.getElementById('addUserForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            const response = await fetch('api/admin_api.php?action=add_user', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            if (result.success) {
                alert('User added successfully!');
                closeModal('addUserModal');
                loadUsers();
                e.target.reset();
                document.getElementById('donorFields').style.display = 'none';
                document.getElementById('hospitalFields').style.display = 'none';
            } else {
                alert('Error: ' + result.message);
            }
        });
        
        // Edit User Form Submit
        document.getElementById('editUserForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            const response = await fetch('api/admin_api.php?action=edit_user', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            if (result.success) {
                alert('User updated successfully!');
                closeModal('editUserModal');
                loadUsers();
            } else {
                alert('Error: ' + result.message);
            }
        });
        
        // Edit Donor Form Submit
        document.getElementById('editDonorForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            const response = await fetch('api/admin_api.php?action=edit_donor', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            if (result.success) {
                alert('Donor updated successfully!');
                closeModal('editDonorModal');
                loadDonors();
            } else {
                alert('Error: ' + result.message);
            }
        });
        
        // Edit Hospital Form Submit
        document.getElementById('editHospitalForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            const response = await fetch('api/admin_api.php?action=edit_hospital', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            if (result.success) {
                alert('Hospital updated successfully!');
                closeModal('editHospitalModal');
                loadHospitals();
            } else {
                alert('Error: ' + result.message);
            }
        });
        
        // Delete User
        async function deleteUser(userId) {
            if (!confirm('Are you sure you want to delete this user? All related data will be deleted.')) return;
            
            const response = await fetch(`api/admin_api.php?action=delete_user&id=${userId}`, {
                method: 'DELETE'
            });
            
            const result = await response.json();
            if (result.success) {
                alert('User deleted successfully!');
                loadUsers();
            } else {
                alert('Error: ' + result.message);
            }
        }
        // Load Donors
        
        async function loadDonors() {
            const response = await fetch('api/admin_api.php?action=get_donors');
            const donors = await response.json();
            
            let html = '<table><thead><tr><th>ID</th><th>Name</th><th>Age</th><th>Blood Type</th><th>Gender</th><th>Phone</th><th>Last Donation</th><th>Actions</th></tr></thead><tbody>';
            donors.forEach(donor => {
                html += `<tr>
                    <td>${donor.DonorID}</td>
                    <td>${donor.FullName}</td>
                    <td>${donor.Age || 'N/A'}</td>
                    <td>${donor.BloodType}</td>
                    <td>${donor.Gender}</td>
                    <td>${donor.Phone || 'N/A'}</td>
                    <td>${donor.LastDonationDate || 'Never'}</td>
                    <td>
                        <button class="btn btn-warning" onclick='showEditDonorModal(${JSON.stringify(donor)})'>Edit</button>
                        <button class="btn btn-danger" onclick="deleteDonor(${donor.DonorID})">Delete</button>
                    </td>
                </tr>`;
            });
            html += '</tbody></table>';
            document.getElementById('donorsTable').innerHTML = html;
        }

        // Load Hospitals
        async function loadHospitals() {
            const response = await fetch('api/admin_api.php?action=get_hospitals');
            const hospitals = await response.json();
            
            let html = '<table><thead><tr><th>ID</th><th>Name</th><th>Username</th><th>Location</th><th>Phone</th><th>Actions</th></tr></thead><tbody>';
            hospitals.forEach(hospital => {
                html += `<tr>
                    <td>${hospital.HospitalID}</td>
                    <td>${hospital.Name}</td>
                    <td>${hospital.Username || 'No User'}</td>
                    <td>${hospital.Location}</td>
                    <td>${hospital.Phone || 'N/A'}</td>
                    <td>
                        <button class="btn btn-warning" onclick='showEditHospitalModal(${JSON.stringify(hospital)})'>Edit</button>
                        <button class="btn btn-danger" onclick="deleteHospital(${hospital.HospitalID})">Delete</button>
                    </td>
                </tr>`;
            });
            html += '</tbody></table>';
            document.getElementById('hospitalsTable').innerHTML = html;
        }

        // Delete Donor
        async function deleteDonor(donorId) {
            if (!confirm('Delete this donor? Their user account will also be deleted.')) return;
            
            const response = await fetch(`api/admin_api.php?action=delete_donor&id=${donorId}`, {
                method: 'DELETE'
            });
            
            const result = await response.json();
            if (result.success) {
                alert('Donor deleted successfully!');
                loadDonors();
                loadUsers();
            } else {
                alert('Error: ' + result.message);
            }
        }

        // Delete Hospital
        async function deleteHospital(hospitalId) {
            if (!confirm('Delete this hospital? Their user account will also be deleted.')) return;
            
            const response = await fetch(`api/admin_api.php?action=delete_hospital&id=${hospitalId}`, {
                method: 'DELETE'
            });
            
            const result = await response.json();
            if (result.success) {
                alert('Hospital deleted successfully!');
                loadHospitals();
                loadUsers();
            } else {
                alert('Error: ' + result.message);
            }
        }
        // Load initial data
        loadUsers();
    </script>
</body>
</html>