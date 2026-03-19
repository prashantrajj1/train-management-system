<?php
require_once 'config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /tms/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$ticket_id = $_GET['id'] ?? null;

if ($ticket_id) {
    try {
        // Verify the ticket belongs to the logged-in user
        $stmt_verify = $pdo->prepare("
            SELECT t.Ticket_ID 
            FROM Ticket t 
            JOIN Passenger p ON t.Passenger_ID = p.Passenger_ID 
            WHERE t.Ticket_ID = ? AND p.User_ID = ?
        ");
        $stmt_verify->execute([$ticket_id, $user_id]);
        $ticket = $stmt_verify->fetch();

        if ($ticket) {
            $pdo->beginTransaction();
            
            // 1. Update ticket status
            $stmt_update = $pdo->prepare("UPDATE Ticket SET Status = 'Cancelled' WHERE Ticket_ID = ?");
            $stmt_update->execute([$ticket_id]);

            // 2. Remove reservation
            $stmt_del_res = $pdo->prepare("DELETE FROM Reservation WHERE Ticket_ID = ?");
            $stmt_del_res->execute([$ticket_id]);

            $pdo->commit();
            header("Location: /tms/account.php?msg=cancelled");
        } else {
            // Unauthorized or invalid ticket
            header("Location: /tms/account.php?msg=error");
        }
        exit;
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        die("Cancellation failed: " . $e->getMessage());
    }
} else {
    header("Location: /tms/account.php");
    exit;
}
?>
