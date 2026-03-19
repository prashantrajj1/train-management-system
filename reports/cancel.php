<?php
require_once '../config/db.php';

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        $pdo->beginTransaction();
        
        // Update ticket status
        $stmt = $pdo->prepare("UPDATE Ticket SET Status = 'Cancelled' WHERE Ticket_ID = ?");
        $stmt->execute([$id]);

        // Remove reservation if exists
        $stmt_r = $pdo->prepare("DELETE FROM Reservation WHERE Ticket_ID = ?");
        $stmt_r->execute([$id]);

        $pdo->commit();
        header("Location: " . BASE_URL . "reports/index.php?msg=cancelled");
        exit;
    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Error cancelling ticket: " . $e->getMessage());
    }
} else {
    header("Location: " . BASE_URL . "reports/index.php");
    exit;
}
?>
