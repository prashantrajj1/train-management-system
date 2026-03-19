<?php
require_once '../config/db.php';
require_once '../includes/functions.php';
include '../includes/header.php';

$from = $_GET['from_station'] ?? '';
$to = $_GET['to_station'] ?? '';
$date = $_GET['travel_date'] ?? '';

$trains = [];
$error = '';
if (!$from || !$to || !$date) {
    $error = "Missing required parameters. Please perform a search again.";
} else {
    $stmt1 = $pdo->prepare("SELECT Station_ID, Station_Name, Station_Code FROM Station WHERE Station_Name LIKE ? OR Station_Code = ?");
    $stmt1->execute(["%$from%", $from]);
    $from_st = $stmt1->fetch(PDO::FETCH_ASSOC);

    $stmt2 = $pdo->prepare("SELECT Station_ID, Station_Name, Station_Code FROM Station WHERE Station_Name LIKE ? OR Station_Code = ?");
    $stmt2->execute(["%$to%", $to]);
    $to_st = $stmt2->fetch(PDO::FETCH_ASSOC);

    if (!$from_st || !$to_st) {
        $error = "Stations not found.";
    } else {
        $from_id = $from_st['Station_ID'];
        $to_id = $to_st['Station_ID'];

        $sql = "SELECT t.Train_ID, t.Train_Name, t.Train_Type, t.Total_Seats,
                       rs1.Stop_Number as From_Stop, rs2.Stop_Number as To_Stop,
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
        
        foreach ($trains as &$tr) {
            $train_id = $tr['Train_ID'];
            $total_seats = (int) $tr['Total_Seats'];
            
            $seat_stmt = $pdo->prepare("SELECT Class_Type, COUNT(*) as Booked FROM Ticket WHERE Train_ID = ? AND Travel_Date = ? AND Status != 'Cancelled' GROUP BY Class_Type");
            $seat_stmt->execute([$train_id, $date]);
            $booked_by_class = [];
            while ($row = $seat_stmt->fetch(PDO::FETCH_ASSOC)) {
                $booked_by_class[$row['Class_Type']] = (int) $row['Booked'];
            }

            $seats_1a = max(0, floor($total_seats * 0.1));
            $seats_2a = max(0, floor($total_seats * 0.2));
            $seats_sl = max(0, floor($total_seats * 0.4));
            $seats_gen = max(0, $total_seats - ($seats_1a + $seats_2a + $seats_sl));

            $avail_1a = max(0, $seats_1a - ($booked_by_class['1A'] ?? 0));
            $avail_2a = max(0, $seats_2a - ($booked_by_class['2A'] ?? 0));
            $avail_sl = max(0, $seats_sl - ($booked_by_class['SL'] ?? 0));
            $avail_gen = max(0, $seats_gen - ($booked_by_class['General'] ?? 0));

            $tr['Duration'] = 'N/A';
            $distance_km = 0;
            
            if ($tr['Arr'] && $tr['Dept']) {
                $arr_time = strtotime($tr['Arr']);
                $dept_time = strtotime($tr['Dept']);
                if ($arr_time < $dept_time) $arr_time += 86400; 
                $diff = $arr_time - $dept_time;
                $hours = $diff / 3600;
                $mins = floor(($diff % 3600) / 60);
                $tr['Duration'] = sprintf("%02d:%02d", floor($hours), $mins);
            }
            $tr['Train_Number'] = str_pad($tr['Train_ID'], 5, "0", STR_PAD_LEFT);
            
            // Calculate dynamic fare based on stops + train type + class
            $stop_diff = (int)$tr['To_Stop'] - (int)$tr['From_Stop'];
            $distance_km = $stop_diff * 100; // Estimated 100km per major stop
            
            $tr['Classes'] = [
                ['code' => '1A', 'name' => 'AC First Class (1A)', 'available' => $avail_1a, 'fare' => calculateFare($tr['Train_Type'], $distance_km, '1A')],
                ['code' => '2A', 'name' => 'AC 2 Tier (2A)', 'available' => $avail_2a, 'fare' => calculateFare($tr['Train_Type'], $distance_km, '2A')],
                ['code' => 'SL', 'name' => 'Sleeper (SL)', 'available' => $avail_sl, 'fare' => calculateFare($tr['Train_Type'], $distance_km, 'SL')],
                ['code' => 'Gen', 'name' => 'General (GN)', 'available' => $avail_gen, 'fare' => calculateFare($tr['Train_Type'], $distance_km, 'Gen')]
            ];
            
            $tr['Dept_Formatted'] = $tr['Dept'] ? date('H:i', strtotime($tr['Dept'])) : 'N/A';
            $tr['Arr_Formatted'] = $tr['Arr'] ? date('H:i', strtotime($tr['Arr'])) : 'N/A';
        }
    }
}
?>

<div class="search-page-bg" style="background-color: #f5f5f5; min-height: 80vh; padding: 20px 0;">
    <div class="container" style="max-width: 1000px; margin: 0 auto;">
        <?php if ($error): ?>
            <div style="padding: 15px; background: #f8d7da; color: #721c24; border-radius: 4px;"><?php echo htmlspecialchars($error); ?></div>
            <p><br><a href="<?php echo BASE_URL; ?>index.php" class="btn" style="background-color: #fb792b;">Modify Search</a></p>
        <?php else: ?>
            
            <!-- Result Summary Header -->
            <div class="irctc-summary-bar">
                <span class="summary-text">
                    <strong style="font-size: 1.1rem;"><?php echo count($trains); ?> Results for <?php echo htmlspecialchars($from_st['Station_Name']); ?> &rarr; <?php echo htmlspecialchars($to_st['Station_Name']); ?></strong> 
                    <span style="color: #555;">| <?php echo date('D, d M Y', strtotime($date)); ?> For Quota | General</span>
                </span>
                <div class="summary-actions">
                    <button class="btn-sort"><i class="fa fa-filter"></i> Sort By | Departure</button>
                    <div style="display:inline-block; margin-left:10px;">
                        <button class="btn-day">&lt; Previous Day</button>
                        <button class="btn-day">Next Day &gt;</button>
                    </div>
                </div>
            </div>

            <?php if (empty($trains)): ?>
                <div style="padding: 15px; background: #fff3cd; color: #856404; border-radius: 4px; margin-top: 20px;">No trains found for this route and date.</div>
            <?php else: ?>
                <div class="train-list" style="margin-top: 15px;">
                    <?php foreach ($trains as $tr): ?>
                        <div class="irctc-card">
                            <!-- Header -->
                            <div class="irctc-header">
                                <div class="train-title">
                                    <strong><?php echo htmlspecialchars($tr['Train_Name']); ?> (<?php echo $tr['Train_Number']; ?>)</strong>
                                </div>
                                <div class="train-runs-on">
                                    Runs On: <span class="days">M T W T F S S</span>
                                </div>
                                <div class="train-schedule-link">
                                    <a href="#">Train Schedule</a>
                                </div>
                            </div>

                            <!-- Schedule Row -->
                            <div class="irctc-body">
                                <div class="time-block left">
                                    <div class="time"><?php echo $tr['Dept_Formatted']; ?></div>
                                    <div class="station"><?php echo htmlspecialchars($from_st['Station_Name']); ?> | <?php echo date('D, d Mar', strtotime($date)); ?></div>
                                </div>
                                <div class="duration-block">
                                    <span class="line"></span>
                                    <span class="duration-text"><?php echo $tr['Duration']; ?></span>
                                    <span class="line"></span>
                                </div>
                                <div class="time-block right">
                                    <div class="time"><?php echo $tr['Arr_Formatted']; ?></div>
                                    <div class="station"><?php echo htmlspecialchars($to_st['Station_Name']); ?> | <?php echo date('D, d Mar', strtotime($date)); ?></div>
                                </div>
                            </div>

                            <!-- Class Boxes -->
                            <div class="irctc-classes">
                                <?php foreach ($tr['Classes'] as $cls): 
                                    $is_avl = $cls['available'] > 0;
                                    $status_color = $is_avl ? '#28a745' : '#dc3545';
                                    $status_text = $is_avl ? 'AVAILABLE-' . str_pad($cls['available'], 4, "0", STR_PAD_LEFT) : 'WL / FULL';
                                ?>
                                    <div class="class-box" onclick="selectClass(this, <?php echo $tr['Train_ID']; ?>, '<?php echo $cls['code']; ?>', <?php echo $cls['fare']; ?>)">
                                        <div class="class-name"><?php echo $cls['name']; ?></div>
                                        <div class="class-status" style="color: <?php echo $status_color; ?>">
                                            <?php echo $status_text; ?>
                                        </div>
                                        <div class="class-fare">₹<?php echo $cls['fare']; ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Actions -->
                            <div class="irctc-actions">
                                <form action="<?php echo BASE_URL; ?>booking/book.php" method="GET" style="display: inline;" onsubmit="return validateClassSelection(<?php echo $tr['Train_ID']; ?>)">
                                    <input type="hidden" name="train_id" value="<?php echo $tr['Train_ID']; ?>">
                                    <input type="hidden" name="date" value="<?php echo htmlspecialchars($date); ?>">
                                    <input type="hidden" name="class" id="selected-class-<?php echo $tr['Train_ID']; ?>" value="">
                                    <input type="hidden" name="fare" id="selected-fare-<?php echo $tr['Train_ID']; ?>" value="">
                                    <button type="submit" class="btn-book">Book Now</button>
                                </form>
                                <button class="btn-other-dates">OTHER DATES</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function selectClass(boxElement, trainId, classCode, fareAmount) {
    // Remove selected state from all boxes in this train card
    const card = boxElement.closest('.irctc-card');
    card.querySelectorAll('.class-box').forEach(b => b.classList.remove('selected'));
    
    // Add selected state to clicked box
    boxElement.classList.add('selected');
    
    // Update hidden input for booking form
    const classInput = document.getElementById('selected-class-' + trainId);
    if (classInput) {
        classInput.value = classCode;
    }
    const fareInput = document.getElementById('selected-fare-' + trainId);
    if (fareInput) {
        fareInput.value = fareAmount;
    }
}

function validateClassSelection(trainId) {
    const classInput = document.getElementById('selected-class-' + trainId);
    if (!classInput.value) {
        alert("Please select a journey class first.");
        return false;
    }
    return true;
}
</script>

<?php include '../includes/footer.php'; ?>
