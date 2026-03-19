<?php
require_once '../config/db.php';
include '../includes/header.php';

$id = $_GET['id'] ?? null;
$station = ['Station_Name' => '', 'Station_Code' => '', 'Location' => ''];
$error = '';

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM Station WHERE Station_ID = ?");
    $stmt->execute([$id]);
    $station = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $code = strtoupper($_POST['code']);
    $loc = $_POST['location'];

    try {
        if ($id) {
            // Update
            $stmt = $pdo->prepare("UPDATE Station SET Station_Name = ?, Station_Code = ?, Location = ? WHERE Station_ID = ?");
            $stmt->execute([$name, $code, $loc, $id]);
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO Station (Station_Name, Station_Code, Location) VALUES (?, ?, ?)");
            $stmt->execute([$name, $code, $loc]);
        }
        echo "<script>window.location.href = '/tms/train-management-system/stations/index.php';</script>";
        exit;
    } catch (PDOException $e) {
        $error = "Error: Station code must be unique. (" . $e->getMessage() . ")";
    }
}
?>

<div class="container" style="max-width: 600px;">
    <h2 style="color: var(--primary-color); margin-bottom: 20px;"><?php echo $id ? 'Edit' : 'Add'; ?> Station</h2>
    
    <?php if($error): ?>
        <p style="color: #721c24; background: #f8d7da; padding: 10px; border-radius: 4px;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <div class="booking-widget" style="width: 100%; box-shadow: none; border: 1px solid var(--border-color); margin: 0; padding: 20px;">
        <form method="POST">
            <div class="form-group">
                <label>Station Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($station['Station_Name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Station Code (Unique)</label>
                <input type="text" name="code" class="form-control" placeholder="e.g. NDLS, MMCT" value="<?php echo htmlspecialchars($station['Station_Code']); ?>" required>
            </div>
            <div class="form-group">
                <label>Location (City/State)</label>
                <input type="text" name="location" class="form-control" value="<?php echo htmlspecialchars($station['Location']); ?>" required>
            </div>
            <button type="submit" class="btn-search btn-primary" style="background-color: var(--primary-color);">Save Station</button>
            <a href="/tms/train-management-system/stations/index.php" style="display: block; text-align: center; margin-top: 15px; color: #555;">Cancel</a>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
