<?php
$file = 'mock_data.sql';
$sql = "\n\n-- MOCK DATA GENERATION --\n\n";

$sql .= "SET FOREIGN_KEY_CHECKS = 0;\n";
$sql .= "TRUNCATE TABLE Reservation;\n";
$sql .= "TRUNCATE TABLE Payment;\n";
$sql .= "TRUNCATE TABLE Ticket;\n";
$sql .= "TRUNCATE TABLE Passenger;\n";
$sql .= "TRUNCATE TABLE Schedule;\n";
$sql .= "TRUNCATE TABLE Route_Station;\n";
$sql .= "TRUNCATE TABLE Route;\n";
$sql .= "TRUNCATE TABLE Train;\n";
$sql .= "TRUNCATE TABLE Station;\n";
$sql .= "SET FOREIGN_KEY_CHECKS = 1;\n\n";

// 1. Generate 60 Stations
$stations_names = ["Mumbai Central","New Delhi","Chennai Central","Howrah","KSR Bengaluru","Ahmedabad","Pune","Secunderabad","Jaipur","Kanpur Central","Lucknow Charbagh","Nagpur","Bhopal","Patna","Indore","Thiruvananthapuram","Guwahati","Bhubaneswar","Chandigarh","Dehradun","Surat","Vadodara","Ludhiana","Agra Cantt","Varanasi","Amritsar","Allahabad","Gwalior","Jabalpur","Raipur","Ranchi","Jamshedpur","Dhanbad","Gaya","Kochi","Kozhikode","Madurai","Coimbatore","Trichy","Tirunelveli","Mysuru","Hubballi","Mangaluru","Vijayawada","Visakhapatnam","Tirupati","Nellore","Warangal","Kurnool","Rajkot", "Cuttack", "Puri", "Rourkela", "Berhampur", "Sambalpur", "Balasore", "Jharsuguda", "Bhadrak", "Khurda Road", "Rayagada"];
// Exactly 60

$sql .= "INSERT INTO Station (Station_ID, Station_Name, Station_Code, Location) VALUES\n";
$station_inserts = [];
foreach ($stations_names as $i => $name) {
    if ($i >= 60) break;
    $station_id = $i + 1;
    $code = strtoupper(substr(str_replace(' ', '', $name), 0, 3) . ($i%10));
    $station_inserts[] = "($station_id, '$name', '$code', '$name City')";
}
$sql .= implode(",\n", $station_inserts) . ";\n\n";

// 2. Generate 50 Trains
$train_types = ["Express", "Superfast", "Rajdhani", "Shatabdi", "Passenger", "Duronto", "Garib Rath", "Vande Bharat"];
$sql .= "INSERT INTO Train (Train_ID, Train_Name, Train_Type, Total_Seats) VALUES\n";
$train_inserts = [];
for ($i = 1; $i <= 50; $i++) {
    $type = $train_types[array_rand($train_types)];
    $name = "Train " . rand(1000, 9999) . " $type";
    $seats = rand(200, 1000); // multiple of 10 usually but anything is fine
    $train_inserts[] = "($i, '$name', '$type', $seats)";
}
$sql .= implode(",\n", $train_inserts) . ";\n\n";

// 3. Generate 50 Routes (1 per train)
$sql .= "INSERT INTO Route (Route_ID, Train_ID) VALUES\n";
$route_inserts = [];
for ($i = 1; $i <= 50; $i++) {
    $route_inserts[] = "($i, $i)";
}
$sql .= implode(",\n", $route_inserts) . ";\n\n";

// 4. Generate Route_Stations and Schedules
$sql_rs = "INSERT INTO Route_Station (Route_ID, Station_ID, Stop_Number) VALUES\n";
$sql_sch = "INSERT INTO Schedule (Train_ID, Station_ID, Arrival_Time, Departure_Time, Travel_Date) VALUES\n";
$rs_inserts = [];
$sch_inserts = [];

$travel_date = date('Y-m-d', strtotime('+1 day'));

for ($route_id = 1; $route_id <= 50; $route_id++) {
    $num_stops = rand(4, 7);
    $stations_used = (array) array_rand($stations_names, $num_stops);
    shuffle($stations_used);
    
    // Base time: around 6 AM
    $base_time = strtotime("06:00:00");
    
    foreach ($stations_used as $idx => $st_idx) {
        $station_id = $st_idx + 1; // 1-indexed
        $stop_num = $idx + 1;
        
        $rs_inserts[] = "($route_id, $station_id, $stop_num)";
        
        // Time logic: train travels 1.5 - 3 hours between stations
        if ($idx == 0) {
            $arr = 'NULL';
            $dept = "'" . date('H:i:s', $base_time) . "'";
        } else if ($idx == $num_stops - 1) {
            $base_time += rand(5400, 10800);
            $arr = "'" . date('H:i:s', $base_time) . "'";
            $dept = 'NULL';
        } else {
            $base_time += rand(5400, 10800);
            $arr = "'" . date('H:i:s', $base_time) . "'";
            $base_time += rand(300, 900); // 5-15 min stop
            $dept = "'" . date('H:i:s', $base_time) . "'";
        }
        
        $sch_inserts[] = "($route_id, $station_id, $arr, $dept, '$travel_date')";
    }
}
$sql .= $sql_rs . implode(",\n", $rs_inserts) . ";\n\n";
$sql .= $sql_sch . implode(",\n", $sch_inserts) . ";\n\n";

// 5. Generate 50 Passengers
$names = ["Amit", "Rahul", "Priya", "Sneha", "Vikram", "Neha", "Rohan", "Anjali", "Karan", "Pooja"];
$surnames = ["Sharma", "Verma", "Singh", "Patel", "Gupta", "Kumar", "Das", "Reddy", "Nair", "Iyer"];
$genders = ['Male', 'Female', 'Other'];

$sql .= "INSERT INTO Passenger (Passenger_ID, Name, Age, Gender, Phone) VALUES\n";
$pass_inserts = [];
for ($i = 1; $i <= 50; $i++) {
    $name = $names[array_rand($names)] . " " . $surnames[array_rand($surnames)];
    $age = rand(18, 70);
    $gender = $genders[array_rand($genders)];
    $phone = "9" . rand(100000000, 999999999);
    $pass_inserts[] = "($i, '$name', $age, '$gender', '$phone')";
}
$sql .= implode(",\n", $pass_inserts) . ";\n\n";

// 6. Generate 50 Tickets
$classes = ["1A", "2A", "SL", "General"];
$statuses = ["Confirmed", "RAC", "Waiting list"];

$sql .= "INSERT INTO Ticket (Ticket_ID, Passenger_ID, Train_ID, Travel_Date, Class_Type, Status, Fare) VALUES\n";
$tkt_inserts = [];
for ($i = 1; $i <= 50; $i++) {
    $p_id = $i;
    $t_id = rand(1, 50);
    $cls = $classes[array_rand($classes)];
    $stat = $statuses[array_rand($statuses)];
    $fare = rand(200, 3000); 
    $tkt_inserts[] = "($i, $p_id, $t_id, '$travel_date', '$cls', '$stat', $fare)";
}
$sql .= implode(",\n", $tkt_inserts) . ";\n\n";

// 7. Generate Reservations for Confirmed Tickets
$sql .= "INSERT INTO Reservation (Ticket_ID, Coach_Number, Seat_Number) VALUES\n";
$res_inserts = [];
for ($i = 1; $i <= 50; $i++) {
    if (rand(1,3) == 1) {
        $coach = "C" . rand(1, 10);
        $seat = "S" . rand(1, 72);
        $res_inserts[] = "($i, '$coach', '$seat')";
    }
}
if (!empty($res_inserts)) {
    $sql .= implode(",\n", $res_inserts) . ";\n\n";
}

file_put_contents($file, $sql, FILE_APPEND);
echo "Appended new validated records.\n";
?>
