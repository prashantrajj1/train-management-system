<?php
require_once '../config/db.php';
include '../includes/header.php';

$from = $_GET['from_station'] ?? '';
$to = $_GET['to_station'] ?? '';
$date = $_GET['travel_date'] ?? '';
$class = $_GET['class_type'] ?? 'All Classes';

$trains = [];
if ($from && $to && $date) {
    // Find stations
    $stmt1 = $pdo->prepare("SELECT Station_ID FROM Station WHERE Station_Name LIKE ? OR Station_Code = ?");
    $stmt1->execute(["%$from%", $from]);
    $from_st = $stmt1->fetch();

    $stmt2 = $pdo->prepare("SELECT Station_ID FROM Station WHERE Station_Name LIKE ? OR Station_Code = ?");
    $stmt2->execute(["%$to%", $to]);
    $to_st = $stmt2->fetch();

    if ($from_st && $to_st) {
        $from_id = $from_st['Station_ID'];
        $to_id = $to_st['Station_ID'];

        // Find trains matching route (from_id and to_id)
        $sql = "SELECT t.Train_ID, t.Train_Name, t.Train_Type, 
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
        $trains = $search->fetchAll();

        // Fare calculation based on process.php
        $base_fare = 500.00;
        if ($class === '1A') $base_fare *= 3;
        if ($class === '2A') $base_fare *= 2;
        if ($class === 'SL') $base_fare *= 0.8;
        $fare = $base_fare;
    }
}
?>

<div class="container">
    <h2 style="color: var(--primary-color);">Search Results</h2>
    <p>Showing trains from <strong><?php echo htmlspecialchars($from); ?></strong> to <strong><?php echo htmlspecialchars($to); ?></strong></p>

    <?php if(!empty($trains)): ?>
        <table>
            <thead>
                <tr>
                    <th>Train Name</th>
                    <th>Train Type</th>
                    <th>Departure</th>
                    <th>Arrival</th>
                    <th>Fare (₹)</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($trains as $tr): ?>
                <tr>
                    <td><?php echo htmlspecialchars($tr['Train_Name']); ?></td>
                    <td><?php echo htmlspecialchars($tr['Train_Type']); ?></td>
                    <td><?php echo htmlspecialchars($tr['Dept'] ? date('H:i', strtotime($tr['Dept'])) : 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($tr['Arr'] ? date('H:i', strtotime($tr['Arr'])) : 'N/A'); ?></td>
                    <td>₹<?php echo number_format($fare, 2); ?> (<?php echo htmlspecialchars($class); ?>)</td>
                    <td style="color: green; font-weight: bold;">AVAILABLE</td>
                    <td>
                        <a href="/tms/booking/book.php?train_id=<?php echo $tr['Train_ID']; ?>&date=<?php echo $date; ?>&class=<?php echo urlencode($class); ?>" class="btn-action btn-primary" style="text-decoration: none;">Book Now</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div style="margin-top:20px; padding: 20px; background: #fff3cd; color: #856404; border-radius: 4px;">
            No trains found for this route and date. Please try different stations or dates.
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
