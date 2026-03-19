<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$train_id = $_GET['train_id'] ?? null;
$date = $_GET['travel_date'] ?? date('Y-m-d');

if (!$train_id) {
    echo json_encode(['error' => 'Missing train ID.']);
    exit;
}

try {
    // Get train details
    $stmt = $pdo->prepare("SELECT Train_ID, Total_Seats FROM Train WHERE Train_ID = ?");
    $stmt->execute([$train_id]);
    $train = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$train) {
        echo json_encode(['error' => 'Train not found.']);
        exit;
    }

    $total_seats = (int) $train['Total_Seats'];

    // Find booked seats for this train on this date
    $seat_stmt = $pdo->prepare("SELECT Class_Type, COUNT(*) as Booked FROM Ticket WHERE Train_ID = ? AND Travel_Date = ? AND Status != 'Cancelled' GROUP BY Class_Type");
    $seat_stmt->execute([$train_id, $date]);
    $booked_by_class = [];
    while ($row = $seat_stmt->fetch(PDO::FETCH_ASSOC)) {
        $booked_by_class[$row['Class_Type']] = (int) $row['Booked'];
    }

    // Distribute Total_Seats logically for UI purposes
    $seats_1a = max(0, floor($total_seats * 0.1));
    $seats_2a = max(0, floor($total_seats * 0.2));
    $seats_sl = max(0, floor($total_seats * 0.4));
    $seats_gen = max(0, $total_seats - ($seats_1a + $seats_2a + $seats_sl));

    $avail_1a = max(0, $seats_1a - ($booked_by_class['1A'] ?? 0));
    $avail_2a = max(0, $seats_2a - ($booked_by_class['2A'] ?? 0));
    $avail_sl = max(0, $seats_sl - ($booked_by_class['SL'] ?? 0));
    $avail_gen = max(0, $seats_gen - ($booked_by_class['General'] ?? 0));
    
    // Standardize fare calculation using the new centralized function
    require_once '../includes/functions.php';
    
    // Estimate total route distance based on stop count if no specific journey is given
    $stop_stmt = $pdo->prepare("SELECT COUNT(*) as StopCount FROM Route_Station WHERE Route_ID = ?");
    $stop_stmt->execute([$train_id]);
    $stop_data = $stop_stmt->fetch(PDO::FETCH_ASSOC);
    $total_stops = (int)$stop_data['StopCount'];
    $distance_km = max(100, ($total_stops - 1) * 100);

    // Get train type for multiplier
    $stmt_type = $pdo->prepare("SELECT Train_Type FROM Train WHERE Train_ID = ?");
    $stmt_type->execute([$train_id]);
    $tr_type = $stmt_type->fetchColumn();

    $classes = [
        ['code' => '1A', 'name' => 'First Class AC', 'available' => $avail_1a, 'total' => $seats_1a, 'fare' => calculateFare($tr_type, $distance_km, '1A')],
        ['code' => '2A', 'name' => 'Second Class AC', 'available' => $avail_2a, 'total' => $seats_2a, 'fare' => calculateFare($tr_type, $distance_km, '2A')],
        ['code' => 'SL', 'name' => 'Sleeper', 'available' => $avail_sl, 'total' => $seats_sl, 'fare' => calculateFare($tr_type, $distance_km, 'SL')],
        ['code' => 'Gen', 'name' => 'General', 'available' => $avail_gen, 'total' => $seats_gen, 'fare' => calculateFare($tr_type, $distance_km, 'Gen')]
    ];

    echo json_encode(['status' => 'success', 'classes' => $classes, 'date' => $date]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
