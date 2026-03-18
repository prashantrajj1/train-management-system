<?php
$_GET['from_station'] = 'Station A'; // Using a generic name we hope exists, or we just look for Station_ID 1 to 2
// we saw in test_data.json:
// Station_ID 1 is part of Route 1. Let's find out what Station 1 and 2 are named.
// Let's just query db to get random stations on a route.
require_once '../config/db.php';

$stmt = $pdo->query("SELECT s1.Station_Name as First, s2.Station_Name as Second 
                     FROM Route_Station rs1 
                     JOIN Route_Station rs2 ON rs1.Route_ID = rs2.Route_ID
                     JOIN Station s1 ON rs1.Station_ID = s1.Station_ID
                     JOIN Station s2 ON rs2.Station_ID = s2.Station_ID
                     WHERE rs1.Stop_Number < rs2.Stop_Number LIMIT 1");
$row = $stmt->fetch();

if (!$row) die("No routes found to test.\n");

$_GET['from_station'] = $row['First'];
$_GET['to_station'] = $row['Second'];
$_GET['travel_date'] = date('Y-m-d'); // Today's date
$_GET['class_type'] = '1A'; // Expected fare: 500 * 3 = 1500

echo "Testing route from {$_GET['from_station']} to {$_GET['to_station']}\n";

ob_start();
include 'search.php';
$output = ob_get_clean();

if (strpos($output, 'No trains found') !== false) {
    echo "TEST FAILED: No trains found.\n";
} else {
    echo "TEST PASSED: Trains found!\n";
    if (strpos($output, 'Fare (₹)') !== false) {
        echo "FARE COLUMN FOUND.\n";
        if (strpos($output, '1,500.00') !== false) {
           echo "CORRECT FARE AMOUNT FOUND (1500.00).\n";
        } else {
           echo "INCORRECT FARE AMOUNT.\n";
        }
    } else {
        echo "FARE COLUMN MISSING.\n";
    }
}
?>
