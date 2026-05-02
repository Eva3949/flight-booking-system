--Name:- 1, Samuel Tenkir       ID:- UGR/35343/16  --------------
--       2, Michael Gizachew    ID:- UGR/34947/16  --------------
--Section:- 3 ( group-5 )
--Course :- Database System                        --------------
--submitted to :- Mr.Alemayehu Megersa
-- Alert Dear Mr this below code is for MariaDB-compatible because of xampp to impliment the website FOR DATABASE
-- Microsofte Database SQL is found in zip file (BOTH DATABSE sql files are the same differ in compatible )
-- thank you MR!
-- MariaDB-compatible Flight Booking System Script (for XAMPP FOR MICROSOFT .SQL FILE IS FOUND IN THE ZIP FILE)
-- Create database
CREATE DATABASE IF NOT EXISTS flight_booking_system;
USE flight_booking_system;

-- Passenger table
CREATE TABLE passengers (
    passenger_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone_number VARCHAR(20),
    passport_number VARCHAR(50),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Airport table
CREATE TABLE airports (
    airport_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    city VARCHAR(50) NOT NULL,
    country VARCHAR(50) NOT NULL,
    code VARCHAR(10) NOT NULL UNIQUE
);

-- Aircraft table
CREATE TABLE aircraft (
    aircraft_id INT AUTO_INCREMENT PRIMARY KEY,
    model VARCHAR(50) NOT NULL,
    capacity INT NOT NULL,
    manufacturer VARCHAR(50) NOT NULL,
    maintenance_status VARCHAR(20) DEFAULT 'Operational'
);

-- Flight table
CREATE TABLE flights (
    flight_id INT AUTO_INCREMENT PRIMARY KEY,
    airline_name VARCHAR(50) NOT NULL,
    source_airport_id INT NOT NULL,
    destination_airport_id INT NOT NULL,
    departure_time DATETIME NOT NULL,
    arrival_time DATETIME NOT NULL,
    duration INT NOT NULL,
    flight_type ENUM('Domestic', 'International') NOT NULL,
    number_of_seats INT NOT NULL,
    aircraft_id INT NOT NULL,
    base_price DECIMAL(10,2) NOT NULL,
    status ENUM('Scheduled', 'Delayed', 'Cancelled', 'Completed') DEFAULT 'Scheduled',
    FOREIGN KEY (source_airport_id) REFERENCES airports(airport_id),
    FOREIGN KEY (destination_airport_id) REFERENCES airports(airport_id),
    FOREIGN KEY (aircraft_id) REFERENCES aircraft(aircraft_id)
);

-- Staff table
CREATE TABLE staff (
    staff_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(50) NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Flight Staff (many-to-many)
CREATE TABLE flight_staff (
    flight_id INT NOT NULL,
    staff_id INT NOT NULL,
    PRIMARY KEY (flight_id, staff_id),
    FOREIGN KEY (flight_id) REFERENCES flights(flight_id),
    FOREIGN KEY (staff_id) REFERENCES staff(staff_id)
);

-- Booking table
CREATE TABLE bookings (
    booking_id INT AUTO_INCREMENT PRIMARY KEY,
    passenger_id INT NOT NULL,
    flight_id INT NOT NULL,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_status ENUM('Pending', 'Completed', 'Failed', 'Refunded') DEFAULT 'Pending',
    total_amount DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (passenger_id) REFERENCES passengers(passenger_id),
    FOREIGN KEY (flight_id) REFERENCES flights(flight_id)
);

-- Payment table
CREATE TABLE payments (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL UNIQUE,
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_mode ENUM('Credit Card', 'Debit Card', 'PayPal', 'Bank Transfer') NOT NULL,
    payment_amount DECIMAL(10, 2) NOT NULL,
    transaction_status ENUM('Pending', 'Completed', 'Failed', 'Refunded') DEFAULT 'Pending',
    transaction_id VARCHAR(100),
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id)
);

-- Ticket table
CREATE TABLE tickets (
    ticket_id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    seat_number VARCHAR(10) NOT NULL,
    class_type ENUM('Economy', 'Business', 'First Class') NOT NULL,
    ticket_status ENUM('Confirmed', 'Cancelled', 'Checked-in') DEFAULT 'Confirmed',
    passenger_name VARCHAR(100) NOT NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id)
);

-- Insert sample data for airports
INSERT INTO airports (name, city, country, code) VALUES
('Addis Ababa Bole International Airport', 'Addis Ababa', 'Ethiopia', 'ADD'),
('John F. Kennedy International Airport', 'New York', 'USA', 'JFK'),
('Heathrow Airport', 'London', 'UK', 'LHR'),
('Dubai International Airport', 'Dubai', 'UAE', 'DXB'),
('Cairo International Airport', 'Cairo', 'Egypt', 'CAI');

-- Insert sample data for aircraft
INSERT INTO aircraft (model, capacity, manufacturer, maintenance_status) VALUES
('Boeing 737', 180, 'Boeing', 'Operational'),
('Airbus A320', 150, 'Airbus', 'Operational'),
('Boeing 787', 280, 'Boeing', 'Operational'),
('Airbus A350', 300, 'Airbus', 'Operational'),
('Bombardier Q400', 78, 'Bombardier', 'Operational');

-- Insert sample flights
INSERT INTO flights (airline_name, source_airport_id, destination_airport_id, departure_time, arrival_time, duration, flight_type, number_of_seats, aircraft_id, base_price, status) VALUES
('Ethiopian Airlines', 1, 2, '2023-06-15 08:00:00', '2023-06-15 16:00:00', 480, 'International', 150, 3, 850.00, 'Scheduled'),
('Ethiopian Airlines', 1, 3, '2023-06-16 10:30:00', '2023-06-16 17:30:00', 420, 'International', 180, 1, 750.00, 'Scheduled'),
('Emirates', 4, 1, '2023-06-17 14:00:00', '2023-06-17 18:00:00', 240, 'International', 280, 4, 650.00, 'Scheduled'),
('EgyptAir', 5, 1, '2023-06-18 09:15:00', '2023-06-18 12:15:00', 180, 'International', 150, 2, 450.00, 'Scheduled'),
('Ethiopian Airlines', 1, 5, '2023-06-19 07:30:00', '2023-06-19 10:30:00', 180, 'International', 78, 5, 350.00, 'Scheduled');

-- Insert sample staff
INSERT INTO staff (name, role, contact_number, email, password) VALUES
('John Doe', 'Pilot', '+251911234567', 'john.doe@airline.com', '$2y$10$abcdefghijklmnopqrstuv'),
('Jane Smith', 'Co-Pilot', '+251922345678', 'jane.smith@airline.com', '$2y$10$abcdefghijklmnopqrstuv'),
('Michael Brown', 'Flight Attendant', '+251933456789', 'michael.brown@airline.com', '$2y$10$abcdefghijklmnopqrstuv'),
('Sarah Johnson', 'Flight Attendant', '+251944567890', 'sarah.johnson@airline.com', '$2y$10$abcdefghijklmnopqrstuv'),
('David Wilson', 'Ground Staff', '+251955678901', 'david.wilson@airline.com', '$2y$10$abcdefghijklmnopqrstuv');

-- Assign staff to flights
INSERT INTO flight_staff (flight_id, staff_id) VALUES
(1, 1), (1, 2), (1, 3),
(2, 1), (2, 3), (2, 4),
(3, 2), (3, 3), (3, 5),
(4, 1), (4, 4), (4, 5),
(5, 2), (5, 3), (5, 4);




-- Stored procedure equivalents in MariaDB use DELIMITER
DELIMITER //
CREATE PROCEDURE CreateBookingWithPayment(
    IN PassengerID INT,
    IN FlightID INT,
    IN TotalAmount DECIMAL(10,2),
    IN PaymentMode VARCHAR(50)
)
BEGIN
    DECLARE BookingID INT;
    START TRANSACTION;
    INSERT INTO bookings (passenger_id, flight_id, total_amount)
    VALUES (PassengerID, FlightID, TotalAmount);
    SET BookingID = LAST_INSERT_ID();
    INSERT INTO payments (booking_id, payment_mode, payment_amount)
    VALUES (BookingID, PaymentMode, TotalAmount);
    COMMIT;
END;//
DELIMITER ;

DELIMITER //
CREATE PROCEDURE GetPassengerBookings(
    IN PassengerID INT
)
BEGIN
    SELECT 
        b.booking_id, 
        f.airline_name, 
        f.departure_time, 
        f.arrival_time, 
        b.booking_date, 
        b.payment_status
    FROM bookings b
    INNER JOIN flights f ON b.flight_id = f.flight_id
    WHERE b.passenger_id = PassengerID
    ORDER BY b.booking_date DESC;
END;//
DELIMITER ;

-- Index creation (MariaDB doesn't support IF NOT EXISTS for indexes directly)
CREATE INDEX idx_passengers_email ON passengers(email);
CREATE INDEX idx_flights_source_airport ON flights(source_airport_id);
CREATE INDEX idx_flights_destination_airport ON flights(destination_airport_id);
CREATE INDEX idx_bookings_passenger_id ON bookings(passenger_id);
CREATE INDEX idx_payments_booking_id ON payments(booking_id);
CREATE INDEX idx_tickets_booking_id ON tickets(booking_id);

-- Check constraints
ALTER TABLE aircraft ADD CONSTRAINT chk_capacity_positive CHECK (capacity > 0);
ALTER TABLE flights ADD CONSTRAINT chk_duration_positive CHECK (duration > 0);
ALTER TABLE flights ADD CONSTRAINT chk_base_price_positive CHECK (base_price >= 0);

-- MariaDB triggers use DELIMITER
DELIMITER //
CREATE TRIGGER trg_UpdateFlightStatusAfterTicketUpdate
AFTER UPDATE ON tickets
FOR EACH ROW
BEGIN
    DECLARE FlightID INT;
    SELECT b.flight_id INTO FlightID FROM bookings b WHERE b.booking_id = NEW.booking_id LIMIT 1;
    IF NOT EXISTS (
        SELECT 1 FROM tickets t 
        JOIN bookings b ON t.booking_id = b.booking_id
        WHERE b.flight_id = FlightID AND t.ticket_status <> 'Checked-in'
    ) THEN
        UPDATE flights SET status = 'Completed' WHERE flight_id = FlightID;
    END IF;
END;//
DELIMITER ;

