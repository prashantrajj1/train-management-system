<?php
require_once 'config/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');

try {
    // Find all past or cancelled tickets for this user
    // Past: Travel_Date < today OR Status = 'Cancelled'
    $sql = "UPDATE Ticket t
            JOIN Passenger p ON t.Passenger_ID = p.Passenger_ID
            SET t.Hidden_By_User = TRUE
            WHERE p.User_ID = ? 
            AND (t.Travel_Date < ? OR t.Status = 'Cancelled')";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $today]);
    
    header("Location: " . BASE_URL . "account.php?msg=cleared");
    exit;
} catch (PDOException $e) {
    header("Location: " . BASE_URL . "account.php?msg=error");
    exit;
}
?>
