<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$train_id = $_GET['train_id'] ?? null;

if (!$train_id) {
    echo json_encode(['error' => 'Missing train ID.']);
    exit;
}

try {
    // Get basic train info
    $stmt_train = $pdo->prepare("SELECT Train_Name FROM Train WHERE Train_ID = ?");
    $stmt_train->execute([$train_id]);
    $train = $stmt_train->fetch(PDO::FETCH_ASSOC);

    if (!$train) {
        echo json_encode(['error' => 'Train not found.']);
        exit;
    }

    // Get the route
    // Note: Route_Station specifies Stop_Number, while Schedule specifies Time
    // We can join Schedule and Station to get the stops for this Train_ID ordered by Time
    $sql = "SELECT s.Station_Name, s.Station_Code, sch.Arrival_Time, sch.Departure_Time
            FROM Schedule sch
            JOIN Station s ON sch.Station_ID = s.Station_ID
            WHERE sch.Train_ID = ?
            ORDER BY sch.Arrival_Time ASC, sch.Departure_Time ASC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$train_id]);
    
    $stops = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $route = [];
    foreach ($stops as $index => $stop) {
        $arr = $stop['Arrival_Time'] ? date('h:i A', strtotime($stop['Arrival_Time'])) : 'Origin';
        $dept = $stop['Departure_Time'] ? date('h:i A', strtotime($stop['Departure_Time'])) : 'Destination';
        
        // Calculate cumulative distance based on average speed (65 km/h)
        $distance = 0;
        if ($index > 0 && $stops[0]['Departure_Time']) {
            $origin_time = strtotime($stops[0]['Departure_Time']);
            $current_time = strtotime($stop['Arrival_Time'] ?? $stop['Departure_Time']);
            
            if ($current_time < $origin_time) {
                $current_time += 86400; // Account for midnight crossing
            }
            
            $hours = ($current_time - $origin_time) / 3600;
            $distance = max(0, round($hours * 65)); // 65 km/h avg speed
        }
        
        $route[] = [
            'stop_number' => $index + 1,
            'station_name' => $stop['Station_Name'],
            'station_code' => $stop['Station_Code'],
            'arrival' => $arr,
            'departure' => $dept,
            'distance_km' => $distance
        ];
    }

    echo json_encode([
        'status' => 'success', 
        'train_name' => $train['Train_Name'],
        'route' => $route
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
