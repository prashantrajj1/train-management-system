<?php
require_once '../config/db.php';

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM Schedule WHERE Schedule_ID = ?");
        $stmt->execute([$id]);
        header("Location: " . BASE_URL . "routes/index.php?msg=deleted");
        exit;
    } catch (PDOException $e) {
        die("Error deleting schedule: " . $e->getMessage());
    }
} else {
    header("Location: " . BASE_URL . "routes/index.php");
    exit;
}
?>
