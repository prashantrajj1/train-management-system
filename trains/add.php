<?php
require_once '../config/db.php';
include '../includes/header.php';

$id = $_GET['id'] ?? null;
$train = ['Train_Name' => '', 'Train_Type' => '', 'Total_Seats' => ''];

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM Train WHERE Train_ID = ?");
    $stmt->execute([$id]);
    $train = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $type = $_POST['type'];
    $seats = $_POST['seats'];

    if ($id) {
        // Update
        $stmt = $pdo->prepare("UPDATE Train SET Train_Name = ?, Train_Type = ?, Total_Seats = ? WHERE Train_ID = ?");
        $stmt->execute([$name, $type, $seats, $id]);
    } else {
        // Insert
        $stmt = $pdo->prepare("INSERT INTO Train (Train_Name, Train_Type, Total_Seats) VALUES (?, ?, ?)");
        $stmt->execute([$name, $type, $seats]);
    }
    
    // Redirect properly
    echo "<script>window.location.href = '/tms/train-management-system/trains/index.php';</script>";
    exit;
}
?>

<div class="container" style="max-width: 600px;">
    <h2 style="color: var(--primary-color); margin-bottom: 20px;"><?php echo $id ? 'Edit' : 'Add'; ?> Train</h2>
    
    <div class="booking-widget" style="width: 100%; box-shadow: none; border: 1px solid var(--border-color); margin: 0; padding: 20px;">
        <form method="POST">
            <div class="form-group">
                <label>Train Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($train['Train_Name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Train Type</label>
                <input type="text" name="type" class="form-control" placeholder="e.g. Express, Superfast, Passenger" value="<?php echo htmlspecialchars($train['Train_Type']); ?>" required>
            </div>
            <div class="form-group">
                <label>Total Seats Capacity</label>
                <input type="number" name="seats" class="form-control" value="<?php echo htmlspecialchars($train['Total_Seats']); ?>" required>
            </div>
            <button type="submit" class="btn-search btn-primary" style="background-color: var(--primary-color);">Save Train</button>
            <a href="/tms/train-management-system/trains/index.php" style="display: block; text-align: center; margin-top: 15px; color: #555;">Cancel</a>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
