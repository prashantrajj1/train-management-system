<?php
require_once '../config/db.php';

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM Station WHERE Station_ID = ?");
        $stmt->execute([$id]);
        header("Location: " . BASE_URL . "stations/index.php?msg=deleted");
        exit;
    } catch (PDOException $e) {
        die("Error deleting station: " . $e->getMessage());
    }
} else {
    header("Location: " . BASE_URL . "stations/index.php");
    exit;
}
?>
