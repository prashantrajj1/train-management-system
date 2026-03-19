<?php
require_once 'config/db.php';

// 1. Clean existing route data
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
$pdo->exec("TRUNCATE TABLE Reservation;");
$pdo->exec("TRUNCATE TABLE Payment;");
$pdo->exec("TRUNCATE TABLE Ticket;");
$pdo->exec("TRUNCATE TABLE Schedule;");
$pdo->exec("TRUNCATE TABLE Route_Station;");
$pdo->exec("TRUNCATE TABLE Route;");
$pdo->exec("TRUNCATE TABLE Train;");
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

try {
    $pdo->beginTransaction();

    // 2. Define Extensive List of Trains (High Traffic + Intercity)
    $trains = [
        ['id' => 12841, 'name' => 'Coromandal Express', 'type' => 'Superfast', 'seats' => 840],
        ['id' => 12301, 'name' => 'Kolkata Rajdhani', 'type' => 'Rajdhani', 'seats' => 720],
        ['id' => 12951, 'name' => 'Mumbai Rajdhani', 'type' => 'Rajdhani', 'seats' => 720],
        ['id' => 12627, 'name' => 'Karnataka Express', 'type' => 'Superfast', 'seats' => 900],
        ['id' => 12801, 'name' => 'Purushottam Express', 'type' => 'Superfast', 'seats' => 850],
        ['id' => 20817, 'name' => 'Bhubaneswar Rajdhani', 'type' => 'Rajdhani', 'seats' => 720],
        ['id' => 12141, 'name' => 'Puna-Patna Express', 'type' => 'Express', 'seats' => 950],
        ['id' => 12001, 'name' => 'Shatabdi Express', 'type' => 'Shatabdi', 'seats' => 600], // Delhi-Bhopal
        ['id' => 12704, 'name' => 'Falaknuma Express', 'type' => 'Superfast', 'seats' => 820], // Howrah-Secunderabad
        ['id' => 12245, 'name' => 'Howrah Duronto', 'type' => 'Duronto', 'seats' => 680], // Howrah-Bangalore
        ['id' => 18414, 'name' => 'Puri-Paradeep Intercity', 'type' => 'Passenger', 'seats' => 1000],
        ['id' => 12821, 'name' => 'Dhauli Express', 'type' => 'Superfast', 'seats' => 900], // Howrah-Puri
        ['id' => 12656, 'name' => 'Navajeevan Express', 'type' => 'Superfast', 'seats' => 880], // Ahmedabad-Chennai
        ['id' => 11020, 'name' => 'Konark Express', 'type' => 'Express', 'seats' => 900], // Bhubaneswar-Mumbai
        ['id' => 11019, 'name' => 'Konark Return Express', 'type' => 'Express', 'seats' => 900], // Mumbai-Bhubaneswar
        ['id' => 12137, 'name' => 'Punjab Mail', 'type' => 'Superfast', 'seats' => 840], // Mumbai-Chandigarh
        ['id' => 12505, 'name' => 'North East Express', 'type' => 'Superfast', 'seats' => 880], // Delhi-Guwahati
        ['id' => 12008, 'name' => 'Shatabdi Special', 'type' => 'Shatabdi', 'seats' => 500], // Bangalore-Chennai
        ['id' => 12009, 'name' => 'Shatabdi Western', 'type' => 'Shatabdi', 'seats' => 500], // Mumbai-Ahmedabad
        ['id' => 12345, 'name' => 'Saraighat Express', 'type' => 'Superfast', 'seats' => 800], // Howrah-Guwahati
        ['id' => 12431, 'name' => 'Rajdhani Trivandrum', 'type' => 'Rajdhani', 'seats' => 720] // Delhi-TVC
    ];

    $stmt_train = $pdo->prepare("INSERT INTO Train (Train_ID, Train_Name, Train_Type, Total_Seats) VALUES (?, ?, ?, ?)");
    $stmt_route = $pdo->prepare("INSERT INTO Route (Route_ID, Train_ID) VALUES (?, ?)");
    
    foreach ($trains as $t) {
        $stmt_train->execute([$t['id'], $t['name'], $t['type'], $t['seats']]);
        $stmt_route->execute([$t['id'], $t['id']]);
    }

    // 3. Define Precise Route Station Sequences (Overlapping Interconnected Network)
    $routes = [
        // Coromandal Express: Howrah -> Chennai
        12841 => [4, 56, 58, 51, 18, 59, 54, 45, 44, 3],
        // Kolkata Rajdhani: Howrah -> New Delhi
        12301 => [4, 33, 34, 25, 27, 10, 2],
        // Mumbai Rajdhani: Mumbai -> New Delhi
        12951 => [1, 21, 22, 6, 9, 2],
        // Karnataka Express: Bangalore -> New Delhi
        12627 => [5, 8, 12, 13, 28, 24, 2],
        // Purushottam Express: Puri -> New Delhi
        12801 => [52, 18, 51, 56, 32, 27, 10, 2],
        // Puna-Patna: Pune -> Patna
        12141 => [7, 1, 13, 10, 25, 14],
        // Bhubaneswar Rajdhani: Bhubaneswar -> New Delhi
        20817 => [18, 51, 56, 32, 25, 10, 2],
        // Shatabdi Bhopal: Delhi -> Bhopal
        12001 => [2, 24, 28, 13],
        // Falaknuma: Howrah -> Secunderabad
        12704 => [4, 56, 18, 45, 44, 8],
        // Howrah Bangalore Duronto
        12245 => [4, 18, 45, 44, 5],
        // Intercity: Puri -> Bhubaneswar -> Cuttack
        18414 => [52, 59, 18, 51],
        // Dhauli: Howrah -> Puri
        12821 => [4, 56, 58, 51, 18, 52],
        // Navajeevan: Ahmedabad -> Chennai
        12656 => [6, 21, 1, 7, 8, 44, 3],
        // Konark: Bhubaneswar -> Mumbai
        11020 => [18, 45, 8, 7, 1],
        // Konark Return: Mumbai -> Bhubaneswar
        11019 => [1, 7, 8, 45, 18],
        // Punjab Mail: Mumbai -> Chandigarh
        12137 => [1, 21, 6, 9, 2, 19],
        // North East: Delhi -> Guwahati
        12505 => [2, 10, 14, 17],
        // Bangalore-Chennai Intercity
        12008 => [5, 3],
        // Western Intercity: Mumbai -> Ahmedabad
        12009 => [1, 21, 22, 6],
        // Guwahati Express: Howrah -> Guwahati
        12345 => [4, 17],
        // Trivandrum Rajdhani: Delhi -> Thiruvananthapuram
        12431 => [2, 9, 6, 21, 1, 7, 5, 16]
    ];

    $stmt_rs = $pdo->prepare("INSERT INTO Route_Station (Route_ID, Station_ID, Stop_Number) VALUES (?, ?, ?)");
    $stmt_sch = $pdo->prepare("INSERT INTO Schedule (Train_ID, Station_ID, Arrival_Time, Departure_Time, Travel_Date) VALUES (?, ?, ?, ?, ?)");

    $dates = [];
    for ($i = -1; $i < 7; $i++) {
        $dates[] = date('Y-m-d', strtotime("+$i days"));
    }

    foreach ($routes as $train_id => $stations) {
        foreach ($stations as $idx => $station_id) {
            $stop_num = $idx + 1;
            $stmt_rs->execute([$train_id, $station_id, $stop_num]);

            $arrival_base = $idx * 2;
            $arr_time = ($idx == 0) ? null : sprintf("%02d:00:00", (8 + $arrival_base) % 24);
            $dept_time = ($idx == count($stations) - 1) ? null : sprintf("%02d:15:00", (8 + $arrival_base) % 24);

            foreach ($dates as $date) {
                $actual_date = $date;
                if ((8 + $arrival_base) >= 24) {
                    $actual_date = date('Y-m-d', strtotime($date . " +1 day"));
                }
                $stmt_sch->execute([$train_id, $station_id, $arr_time, $dept_time, $actual_date]);
            }
        }
    }

    $pdo->commit();
    echo "Successfully created a dense, interconnected route network for the next 7 days!\n";

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    die("Population failed: " . $e->getMessage());
}
?>
