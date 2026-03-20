CREATE DATABASE IF NOT EXISTS train_management_system;
USE train_management_system;

-- Experiment 2: Creating Tables Using DDL Commands (PRIMARY KEY, FOREIGN KEY, UNIQUE, NOT NULL, CHECK)

CREATE TABLE Train (
    Train_ID INT AUTO_INCREMENT PRIMARY KEY,
    Train_Name VARCHAR(100) NOT NULL,
    Train_Type VARCHAR(50) NOT NULL,
    Total_Seats INT NOT NULL CHECK (Total_Seats > 0)
);

CREATE TABLE Station (
    Station_ID INT AUTO_INCREMENT PRIMARY KEY,
    Station_Name VARCHAR(100) NOT NULL,
    Station_Code VARCHAR(10) NOT NULL UNIQUE,
    Location VARCHAR(100) NOT NULL
);

CREATE TABLE Route (
    Route_ID INT AUTO_INCREMENT PRIMARY KEY,
    Train_ID INT NOT NULL,
    FOREIGN KEY (Train_ID) REFERENCES Train(Train_ID) ON DELETE CASCADE
);

CREATE TABLE Route_Station (
    RouteStation_ID INT AUTO_INCREMENT PRIMARY KEY,
    Route_ID INT NOT NULL,
    Station_ID INT NOT NULL,
    Stop_Number INT NOT NULL CHECK (Stop_Number > 0),
    FOREIGN KEY (Route_ID) REFERENCES Route(Route_ID) ON DELETE CASCADE,
    FOREIGN KEY (Station_ID) REFERENCES Station(Station_ID) ON DELETE CASCADE
);

CREATE TABLE Schedule (
    Schedule_ID INT AUTO_INCREMENT PRIMARY KEY,
    Train_ID INT NOT NULL,
    Station_ID INT NOT NULL,
    Arrival_Time TIME,
    Departure_Time TIME,
    Travel_Date DATE NOT NULL,
    FOREIGN KEY (Train_ID) REFERENCES Train(Train_ID) ON DELETE CASCADE,
    FOREIGN KEY (Station_ID) REFERENCES Station(Station_ID) ON DELETE CASCADE
);

CREATE TABLE User (
    User_ID INT AUTO_INCREMENT PRIMARY KEY,
    Username VARCHAR(50) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    Role ENUM('Admin', 'Passenger') DEFAULT 'Passenger',
    Created_At TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE Passenger (
    Passenger_ID INT AUTO_INCREMENT PRIMARY KEY,
    User_ID INT DEFAULT NULL,
    Name VARCHAR(100) NOT NULL,
    Age INT NOT NULL CHECK (Age >= 0 AND Age <= 120),
    Gender ENUM('Male', 'Female', 'Other') NOT NULL,
    Phone VARCHAR(15) NOT NULL,
    FOREIGN KEY (User_ID) REFERENCES User(User_ID) ON DELETE SET NULL
);

CREATE TABLE Ticket (
    Ticket_ID INT AUTO_INCREMENT PRIMARY KEY,
    Passenger_ID INT NOT NULL,
    Train_ID INT NOT NULL,
    Travel_Date DATE NOT NULL,
    Class_Type VARCHAR(50) NOT NULL,
    Status ENUM('Confirmed', 'RAC', 'Waiting list', 'Cancelled') DEFAULT 'Confirmed',
    Fare DECIMAL(10, 2) NOT NULL CHECK (Fare >= 0),
    Hidden_By_User TINYINT(1) DEFAULT 0,
    FOREIGN KEY (Passenger_ID) REFERENCES Passenger(Passenger_ID) ON DELETE CASCADE,
    FOREIGN KEY (Train_ID) REFERENCES Train(Train_ID) ON DELETE CASCADE
);

CREATE TABLE Reservation (
    Reservation_ID INT AUTO_INCREMENT PRIMARY KEY,
    Ticket_ID INT NOT NULL UNIQUE,
    Coach_Number VARCHAR(10),
    Seat_Number VARCHAR(10),
    FOREIGN KEY (Ticket_ID) REFERENCES Ticket(Ticket_ID) ON DELETE CASCADE
);

CREATE TABLE Payment (
    Payment_ID INT AUTO_INCREMENT PRIMARY KEY,
    Ticket_ID INT NOT NULL,
    Payment_Date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    Amount DECIMAL(10, 2) NOT NULL CHECK (Amount >= 0),
    Payment_Mode VARCHAR(50) NOT NULL,
    Payment_Status ENUM('Success', 'Failed', 'Pending') DEFAULT 'Success',
    FOREIGN KEY (Ticket_ID) REFERENCES Ticket(Ticket_ID) ON DELETE CASCADE
);

-- Experiment 3: Data Manipulation (INSERT, UPDATE, DELETE) & Basic Queries
-- (Data will be manipulated via the PHP application)

-- Experiment 7: Creating and Managing Views
-- Experiment 5: Querying Multiple Tables Using Joins (INNER, LEFT)
-- Experiment 4: Advanced SQL Expressions (CASE, COALESCE)
CREATE VIEW vw_ticket_details AS
SELECT 
    t.Ticket_ID,
    p.Name AS Passenger_Name,
    tr.Train_Name,
    t.Travel_Date,
    t.Status,
    t.Fare,
    COALESCE(r.Coach_Number, 'Unassigned') AS Coach_Number,
    COALESCE(r.Seat_Number, 'Unassigned') AS Seat_Number,
    CASE 
        WHEN t.Status = 'Confirmed' THEN 'Ready for Travel'
        WHEN t.Status = 'Cancelled' THEN 'Ticket Void'
        ELSE 'Awaiting Confirmation'
    END AS Journey_Status
FROM Ticket t
INNER JOIN Passenger p ON t.Passenger_ID = p.Passenger_ID
INNER JOIN Train tr ON t.Train_ID = tr.Train_ID
LEFT JOIN Reservation r ON t.Ticket_ID = r.Ticket_ID;

-- Experiment 8: Subqueries 
CREATE VIEW vw_passengers_without_tickets AS
SELECT p.Passenger_ID, p.Name
FROM Passenger p
WHERE p.Passenger_ID NOT IN (SELECT Passenger_ID FROM Ticket);

-- Experiment 6: Set Operations (UNION)
CREATE VIEW vw_all_locations AS
SELECT Station_Name AS Location_Name, 'Station' AS Location_Type FROM Station
UNION
SELECT Location AS Location_Name, 'City' AS Location_Type FROM Station;

-- Experiment 10: Stored Procedures and Triggers
-- Experiment 9: Transactions and Error Handling
DELIMITER //

CREATE PROCEDURE sp_book_ticket(
    IN p_name VARCHAR(100),
    IN p_age INT,
    IN p_gender ENUM('Male', 'Female', 'Other'),
    IN p_phone VARCHAR(15),
    IN p_train_id INT,
    IN p_date DATE,
    IN p_class VARCHAR(50),
    IN p_fare DECIMAL(10,2)
)
BEGIN
    DECLARE v_passenger_id INT;
    DECLARE v_ticket_id INT;
    DECLARE v_total_seats INT;
    DECLARE v_booked_seats INT;
    DECLARE v_status ENUM('Confirmed', 'RAC', 'Waiting list', 'Cancelled');
    
    -- Error handling declaration (Experiment 9)
    DECLARE exit handler for sqlexception
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION; -- Transaction Begin (Experiment 9)

    -- Insert Passenger
    INSERT INTO Passenger (Name, Age, Gender, Phone) VALUES (p_name, p_age, p_gender, p_phone);
    SET v_passenger_id = LAST_INSERT_ID();

    -- Check availability (Experiment 4: IF clause)
    SELECT Total_Seats INTO v_total_seats FROM Train WHERE Train_ID = p_train_id;
    SELECT COUNT(*) INTO v_booked_seats FROM Ticket WHERE Train_ID = p_train_id AND Travel_Date = p_date AND Status != 'Cancelled';

    IF v_booked_seats >= v_total_seats THEN
        SET v_status = 'Waiting list';
    ELSE
        SET v_status = 'Confirmed';
    END IF;

    -- Insert Ticket
    INSERT INTO Ticket (Passenger_ID, Train_ID, Travel_Date, Class_Type, Status, Fare) 
    VALUES (v_passenger_id, p_train_id, p_date, p_class, v_status, p_fare);
    
    SET v_ticket_id = LAST_INSERT_ID();

    -- Conditional reservation
    IF v_status = 'Confirmed' THEN
        INSERT INTO Reservation (Ticket_ID, Coach_Number, Seat_Number) 
        VALUES (v_ticket_id, 'C1', CONCAT('S', v_booked_seats + 1));
    END IF;

    COMMIT; -- Transaction Commit
END //

-- Trigger: Automatically delete reservation if a Ticket is marked as Cancelled
CREATE TRIGGER trg_after_ticket_cancel 
AFTER UPDATE ON Ticket
FOR EACH ROW
BEGIN
    IF NEW.Status = 'Cancelled' AND OLD.Status != 'Cancelled' THEN
        DELETE FROM Reservation WHERE Ticket_ID = NEW.Ticket_ID;
    END IF;
END //

DELIMITER ;
