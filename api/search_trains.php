<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$from = $_GET['from_station'] ?? '';
$to = $_GET['to_station'] ?? '';
$date = $_GET['travel_date'] ?? '';

if (!$from || !$to || !$date) {
    echo json_encode(['error' => 'Missing required parameters.']);
    exit;
}

try {
    // Find from station
    $stmt1 = $pdo->prepare("SELECT Station_ID, Station_Name, Station_Code FROM Station WHERE Station_Name LIKE ? OR Station_Code = ?");
    $stmt1->execute(["%$from%", $from]);
    $from_st = $stmt1->fetch(PDO::FETCH_ASSOC);

    // Find to station
    $stmt2 = $pdo->prepare("SELECT Station_ID, Station_Name, Station_Code FROM Station WHERE Station_Name LIKE ? OR Station_Code = ?");
    $stmt2->execute(["%$to%", $to]);
    $to_st = $stmt2->fetch(PDO::FETCH_ASSOC);

    if (!$from_st || !$to_st) {
        echo json_encode(['trains' => []]); // No stations found
        exit;
    }

    $from_id = $from_st['Station_ID'];
    $to_id = $to_st['Station_ID'];

    // Find trains matching route (from_id and to_id) in correct order
    $sql = "SELECT t.Train_ID, t.Train_Name, t.Train_Type, t.Total_Seats,
                   (SELECT Arrival_Time FROM Schedule WHERE Train_ID = t.Train_ID AND Station_ID = ? LIMIT 1) as Arr,
                   (SELECT Departure_Time FROM Schedule WHERE Train_ID = t.Train_ID AND Station_ID = ? LIMIT 1) as Dept
            FROM Route_Station rs1
            JOIN Route_Station rs2 ON rs1.Route_ID = rs2.Route_ID
            JOIN Route r ON rs1.Route_ID = r.Route_ID
            JOIN Train t ON r.Train_ID = t.Train_ID
            WHERE rs1.Station_ID = ? AND rs2.Station_ID = ? 
            AND rs1.Stop_Number < rs2.Stop_Number";
    
    $search = $pdo->prepare($sql);
    $search->execute([$to_id, $from_id, $from_id, $to_id]);
    $trains = $search->fetchAll(PDO::FETCH_ASSOC);
    
    // Process trains to calculate seat availability
    foreach ($trains as &$tr) {
        $train_id = $tr['Train_ID'];
        $total_seats = (int) $tr['Total_Seats'];
        
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

        // Add journey details & distance calculation
        $tr['Duration'] = 'N/A';
        $distance_km = 0;
        
        if ($tr['Arr'] && $tr['Dept']) {
            $arr_time = strtotime($tr['Arr']);
            $dept_time = strtotime($tr['Dept']);
            if ($arr_time < $dept_time) {
                // assume next day arrival
                $arr_time += 86400; 
            }
            $diff = $arr_time - $dept_time;
            $hours = $diff / 3600;
            
            $mins = floor(($diff % 3600) / 60);
            $tr['Duration'] = floor($hours) . "h {$mins}m";
            
            // Assume 65 km/h avg speed
            $distance_km = round($hours * 65);
        }
        
        $tr['Distance'] = $distance_km . ' km';
        
        // Distance-based pricing
        // Minimum fare ₹50, standard rate ₹1.5/km
        $base_fare = max(50, $distance_km * 1.5);

        $tr['Classes'] = [
            ['code' => '1A', 'name' => 'First Class AC', 'available' => $avail_1a, 'total' => $seats_1a, 'fare' => round($base_fare * 3)],
            ['code' => '2A', 'name' => 'Second Class AC', 'available' => $avail_2a, 'total' => $seats_2a, 'fare' => round($base_fare * 2)],
            ['code' => 'SL', 'name' => 'Sleeper', 'available' => $avail_sl, 'total' => $seats_sl, 'fare' => round($base_fare * 0.8)],
            ['code' => 'Gen', 'name' => 'General', 'available' => $avail_gen, 'total' => $seats_gen, 'fare' => round($base_fare * 0.4)]
        ];
        
        $tr['From_Station'] = $from_st['Station_Name'];
        $tr['To_Station'] = $to_st['Station_Name'];
        
        // Format times
        if ($tr['Dept']) $tr['Dept_Formatted'] = date('h:i A', strtotime($tr['Dept']));
        if ($tr['Arr']) $tr['Arr_Formatted'] = date('h:i A', strtotime($tr['Arr']));
    }

    echo json_encode(['status' => 'success', 'trains' => $trains, 'query' => ['from' => $from_st['Station_Name'], 'to' => $to_st['Station_Name'], 'date' => $date]]);

} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
