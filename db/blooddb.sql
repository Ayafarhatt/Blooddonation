CREATE DATABASE IF NOT EXISTS BloodDB;
USE BloodDB;

CREATE TABLE Hospital (
    HospitalID INT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(100) NOT NULL,
    Location VARCHAR(150) NOT NULL,
    Phone VARCHAR(20)
);

CREATE TABLE Donor (
    DonorID INT PRIMARY KEY AUTO_INCREMENT,
    FullName VARCHAR(120) NOT NULL,
    BirthDate DATE NOT NULL,
    Gender VARCHAR(10) NOT NULL,
    BloodType VARCHAR(5) NOT NULL,
    Phone VARCHAR(20),
    LastDonationDate DATE,
    MedicalConditions VARCHAR(255)
);

CREATE TABLE DonationRequest (
    RequestID INT PRIMARY KEY AUTO_INCREMENT,
    HospitalID INT NOT NULL,
    BloodType VARCHAR(5) NOT NULL,
    QuantityNeeded INT NOT NULL,
    RequestDate DATE,
    Status VARCHAR(20),
    FOREIGN KEY (HospitalID) REFERENCES Hospital(HospitalID)
);

CREATE TABLE DonationAppointment (
    AppointmentID INT PRIMARY KEY AUTO_INCREMENT,
    DonorID INT NOT NULL,
    RequestID INT NOT NULL,
    AppointmentDate DATE NOT NULL,
    Status VARCHAR(20) DEFAULT 'Scheduled',
    FOREIGN KEY (DonorID) REFERENCES Donor(DonorID),
    FOREIGN KEY (RequestID) REFERENCES DonationRequest(RequestID)
);

CREATE TABLE BloodStock (
    StockID INT PRIMARY KEY AUTO_INCREMENT,
    HospitalID INT NOT NULL,
    BloodType VARCHAR(5) NOT NULL,
    Quantity INT NOT NULL,
    LastUpdate DATE,
    FOREIGN KEY (HospitalID) REFERENCES Hospital(HospitalID)
);

CREATE INDEX idx_donationrequest_hospital
    ON DonationRequest(HospitalID);

CREATE INDEX idx_appointment_donor
    ON DonationAppointment(DonorID);

CREATE INDEX idx_appointment_request
    ON DonationAppointment(RequestID);

CREATE INDEX idx_bloodstock_hospital
    ON BloodStock(HospitalID);

CREATE INDEX idx_donor_bloodtype
    ON Donor(BloodType);

CREATE INDEX idx_request_bloodtype
    ON DonationRequest(BloodType);

CREATE INDEX idx_bloodstock_bloodtype
    ON BloodStock(BloodType);

DELIMITER //

CREATE PROCEDURE CreateRequest(
    IN p_HospitalID INT,
    IN p_BloodType VARCHAR(5),
    IN p_QuantityNeeded INT
)
BEGIN
    INSERT INTO DonationRequest(HospitalID, BloodType, QuantityNeeded, RequestDate, Status)
    VALUES (p_HospitalID, p_BloodType, p_QuantityNeeded, CURRENT_DATE(), 'Pending');
END//

CREATE PROCEDURE CreateAppointment(
    IN p_DonorID INT,
    IN p_RequestID INT,
    IN p_Date DATE,
    IN p_Units INT
)
BEGIN
    INSERT INTO DonationAppointment(DonorID, RequestID, AppointmentDate, Status, Units)
    VALUES (p_DonorID, p_RequestID, p_Date, 'Scheduled', p_Units);
END//

DELIMITER ;

DELIMITER //

CREATE FUNCTION GetDonorAge(p_DonorID INT)
RETURNS INT
DETERMINISTIC
BEGIN
    DECLARE age INT;
    SELECT TIMESTAMPDIFF(YEAR, BirthDate, CURDATE()) INTO age
    FROM Donor
    WHERE DonorID = p_DonorID;
    RETURN age;
END//

DELIMITER ;

DELIMITER //

CREATE TRIGGER update_last_donation_date
AFTER UPDATE ON DonationAppointment
FOR EACH ROW
BEGIN
    IF NEW.Status = 'Completed' AND OLD.Status != 'Completed' THEN
        UPDATE Donor
        SET LastDonationDate = NEW.AppointmentDate
        WHERE DonorID = NEW.DonorID;
    END IF;
END//

DELIMITER ;

CREATE VIEW DonorAppointments AS
SELECT
    da.AppointmentID,
    d.DonorID,
    d.FullName AS DonorName,
    d.BloodType,
    h.HospitalID,
    h.Name AS HospitalName,
    da.AppointmentDate,
    da.Units,
    da.Status AS AppointmentStatus,
    dr.Status AS RequestStatus
FROM DonationAppointment da, Donor d, DonationRequest dr, Hospital h
WHERE da.DonorID = d.DonorID
  AND da.RequestID = dr.RequestID
  AND dr.HospitalID = h.HospitalID;