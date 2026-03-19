<?php
require_once '../config/db.php';

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM Train WHERE Train_ID = ?");
        $stmt->execute([$id]);
        header("Location: " . BASE_URL . "trains/index.php?msg=deleted");
        exit;
    } catch (PDOException $e) {
        die("Error deleting train: " . $e->getMessage());
    }
} else {
    header("Location: " . BASE_URL . "trains/index.php");
    exit;
}
?>
