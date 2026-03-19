<?php
require_once '../config/db.php';
include '../includes/header.php';

// Fetch trains and stations for dropdowns
$trains = $pdo->query("SELECT Train_ID, Train_Name FROM Train")->fetchAll();
$stations = $pdo->query("SELECT Station_ID, Station_Name FROM Station")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $train_id = $_POST['train_id'];
    $station_id = $_POST['station_id'];
    $arrival = $_POST['arrival'];
    $departure = $_POST['departure'];
    $date = $_POST['date'];

    $stmt = $pdo->prepare("INSERT INTO Schedule (Train_ID, Station_ID, Arrival_Time, Departure_Time, Travel_Date) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$train_id, $station_id, $arrival, $departure, $date]);
    
    echo "<script>window.location.href = '/tms/train-management-system/routes/index.php';</script>";
    exit;
}
?>

<div class="container" style="max-width: 600px;">
    <h2 style="color: var(--primary-color); margin-bottom: 20px;">Add Schedule</h2>
    
    <div class="booking-widget" style="width: 100%; box-shadow: none; border: 1px solid var(--border-color); margin: 0; padding: 20px;">
        <form method="POST">
            <div class="form-group">
                <label>Select Train</label>
                <select name="train_id" class="form-control" required>
                    <option value="">-- Select Train --</option>
                    <?php foreach($trains as $t): ?>
                        <option value="<?php echo $t['Train_ID']; ?>"><?php echo htmlspecialchars($t['Train_Name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Select Station</label>
                <select name="station_id" class="form-control" required>
                    <option value="">-- Select Station --</option>
                    <?php foreach($stations as $s): ?>
                        <option value="<?php echo $s['Station_ID']; ?>"><?php echo htmlspecialchars($s['Station_Name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <div class="form-group" style="flex:1;">
                    <label>Arrival Time</label>
                    <input type="time" name="arrival" class="form-control" required>
                </div>
                <div class="form-group" style="flex:1;">
                    <label>Departure Time</label>
                    <input type="time" name="departure" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label>Travel Date</label>
                <input type="date" name="date" class="form-control" required>
            </div>
            
            <button type="submit" class="btn-search btn-primary" style="background-color: var(--primary-color);">Save Schedule</button>
            <a href="/tms/train-management-system/routes/index.php" style="display: block; text-align: center; margin-top: 15px; color: #555;">Cancel</a>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
