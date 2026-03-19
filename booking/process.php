<?php
require_once '../config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $train_id = $_POST['train_id'];
    $date = $_POST['date'];
    $class = $_POST['class'];
    $name = $_POST['name'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $phone = $_POST['phone'];

    try {
        $pdo->beginTransaction();

        // 1. Check Seat Availability
        $stmt_seats = $pdo->prepare("SELECT Total_Seats FROM Train WHERE Train_ID = ?");
        $stmt_seats->execute([$train_id]);
        $total_seats = $stmt_seats->fetchColumn();

        $stmt_booked = $pdo->prepare("SELECT COUNT(*) FROM Ticket WHERE Train_ID = ? AND Travel_Date = ? AND Status != 'Cancelled'");
        $stmt_booked->execute([$train_id, $date]);
        $booked_seats = $stmt_booked->fetchColumn();

        $status = 'Confirmed';
        if ($booked_seats >= $total_seats) {
            $status = 'Waiting list'; // Basic waiting list logic
        }

        // 2. Insert Passenger (with User_ID if logged in)
        $user_id = $_SESSION['user_id'] ?? null;
        $stmt_pass = $pdo->prepare("INSERT INTO Passenger (Name, Age, Gender, Phone, User_ID) VALUES (?, ?, ?, ?, ?)");
        $stmt_pass->execute([$name, $age, $gender, $phone, $user_id]);
        $passenger_id = $pdo->lastInsertId();

        // 3. Calculate Fare (Dynamic formulation based on class)
        $base_fare = 500.00; // Base arbitrary fare
        if ($class === '1A') $base_fare *= 3;
        if ($class === '2A') $base_fare *= 2;
        if ($class === 'SL') $base_fare *= 0.8;
        $fare = $base_fare;

        // 4. Create Ticket
        $stmt_ticket = $pdo->prepare("INSERT INTO Ticket (Passenger_ID, Train_ID, Travel_Date, Class_Type, Status, Fare) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_ticket->execute([$passenger_id, $train_id, $date, $class, $status, $fare]);
        $ticket_id = $pdo->lastInsertId();

        // 5. Create Reservation if Confirmed
        if ($status === 'Confirmed') {
            $seat_num = 'S' . ($booked_seats + 1);
            $stmt_res = $pdo->prepare("INSERT INTO Reservation (Ticket_ID, Coach_Number, Seat_Number) VALUES (?, ?, ?)");
            $stmt_res->execute([$ticket_id, 'C1', $seat_num]);
        }

        $pdo->commit();
        
        // Redirect to payment
        header("Location: " . BASE_URL . "payments/pay.php?ticket_id=$ticket_id");
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Booking failed: " . $e->getMessage());
    }
}
?>
