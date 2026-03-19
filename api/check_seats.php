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
    
    // Estimate distance to get realistic base fare for standard checking
    // Use full route distance if no origin/destination is provided
    $sql_dist = "SELECT MIN(Departure_Time) as First_Dept, MAX(Arrival_Time) as Last_Arr FROM Schedule WHERE Train_ID = ?";
    $dist_stmt = $pdo->prepare($sql_dist);
    $dist_stmt->execute([$train_id]);
    $dist_inf = $dist_stmt->fetch(PDO::FETCH_ASSOC);
    
    $distance_km = 200; // default
    if ($dist_inf && $dist_inf['First_Dept'] && $dist_inf['Last_Arr']) {
        $arr_time = strtotime($dist_inf['Last_Arr']);
        $dept_time = strtotime($dist_inf['First_Dept']);
        if ($arr_time < $dept_time) $arr_time += 86400;
        $hours = ($arr_time - $dept_time) / 3600;
        $distance_km = round($hours * 65);
    }

    $base_fare = max(50, $distance_km * 1.5);

    $classes = [
        ['code' => '1A', 'name' => 'First Class AC', 'available' => $avail_1a, 'total' => $seats_1a, 'fare' => round($base_fare * 3)],
        ['code' => '2A', 'name' => 'Second Class AC', 'available' => $avail_2a, 'total' => $seats_2a, 'fare' => round($base_fare * 2)],
        ['code' => 'SL', 'name' => 'Sleeper', 'available' => $avail_sl, 'total' => $seats_sl, 'fare' => round($base_fare * 0.8)],
        ['code' => 'Gen', 'name' => 'General', 'available' => $avail_gen, 'total' => $seats_gen, 'fare' => round($base_fare * 0.4)]
    ];

    echo json_encode(['status' => 'success', 'classes' => $classes, 'date' => $date]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
