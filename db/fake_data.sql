
INSERT INTO Hospital (Name, Location, Phone) VALUES
('Rafic Hariri University Hospital', 'Beirut - Jnah', '01-820000'),
('AUB Medical Center', 'Beirut - Hamra', '01-350000'),
('Saida Governmental Hospital', 'Saida', '07-720000');

INSERT INTO Donor (FullName, BirthDate, Gender, BloodType, Phone, LastDonationDate, MedicalConditions) VALUES
('Ali Mansour', '1995-04-12', 'Male', 'A+', '71-900111', '2024-06-10', 'None'),
('Sara Khalil', '1999-11-03', 'Female', 'O-', '76-555222', NULL, 'Anemia History'),
('Rami Chahine', '1988-02-20', 'Male', 'B+', '70-333444', '2024-01-15', 'High Blood Pressure'),
('Yasmin Darwish', '2001-07-30', 'Female', 'AB+', '78-111999', NULL, 'None');

INSERT INTO BloodStock (HospitalID, BloodType, Quantity, LastUpdate) VALUES
(1, 'A+', 12, '2024-12-10'),
(1, 'O-', 5, '2024-12-09'),
(2, 'B+', 7, '2024-12-08'),
(2, 'AB+', 3, '2024-12-07'),
(3, 'O-', 9, '2024-12-11');

INSERT INTO DonationRequest (HospitalID, BloodType, QuantityNeeded, RequestDate, Status) VALUES
(1, 'O-', 5, '2024-12-05', 'Pending'),
(2, 'B+', 3, '2024-12-01', 'Pending'),
(3, 'A+', 4, '2024-12-03', 'Pending');

INSERT INTO DonationAppointment (DonorID, RequestID, AppointmentDate, Status) VALUES
(1, 1, '2024-12-12', 'Scheduled'),
(2, 1, '2024-12-13', 'Scheduled'),
(3, 2, '2024-12-14', 'Scheduled');

INSERT INTO User (Username, PasswordHash, Role, RelatedID) VALUES
('admin1', 'hashed_pass_admin', 'Admin', NULL),
('donor1', 'hashed_pass_donor', 'Donor', 1),
('hospital1', 'hashed_pass_hosp', 'Hospital', 1);
