<?php
require_once '../config/db.php';

header('Content-Type: application/json');

$query = $_GET['q'] ?? '';

if (strlen($query) < 1) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT Station_Name FROM Station WHERE Station_Name LIKE ? OR Station_Code LIKE ? LIMIT 10");
    $searchTerm = "%$query%";
    $stmt->execute([$searchTerm, $searchTerm]);
    $stations = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode($stations);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error']);
}
?>
