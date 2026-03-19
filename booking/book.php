<?php
require_once '../config/db.php';
include '../includes/header.php';

$train_id = $_GET['train_id'] ?? '';
$date = $_GET['date'] ?? '';
$class = $_GET['class'] ?? '';

if (!$train_id || !$date) {
    die("Invalid request");
}

// Fetch Train info
$stmt = $pdo->prepare("SELECT * FROM Train WHERE Train_ID = ?");
$stmt->execute([$train_id]);
$train = $stmt->fetch();
?>

<div class="container" style="max-width: 600px;">
    <h2 style="color: var(--primary-color); margin-bottom: 20px;">Passenger Details</h2>
    <div style="margin-bottom: 20px; padding: 15px; background: #e2eefd; border-radius: 4px;">
        <strong>Booking for:</strong> <?php echo htmlspecialchars($train['Train_Name']); ?> (<?php echo htmlspecialchars($train['Train_Type']); ?>)<br>
        <strong>Travel Date:</strong> <?php echo htmlspecialchars($date); ?><br>
        <strong>Class:</strong> <?php echo htmlspecialchars($class); ?>
    </div>
    
    <div class="booking-widget" style="width: 100%; box-shadow: none; border: 1px solid var(--border-color); margin: 0; padding: 20px;">
        <form action="/tms/train-management-system/booking/process.php" method="POST" id="book-form">
            <input type="hidden" name="train_id" value="<?php echo htmlspecialchars($train_id); ?>">
            <input type="hidden" name="date" value="<?php echo htmlspecialchars($date); ?>">
            <input type="hidden" name="class" value="<?php echo htmlspecialchars($class); ?>">
            
            <div class="form-group">
                <label>Passenger Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            
            <div style="display: flex; gap: 10px;">
                <div class="form-group" style="flex:1;">
                    <label>Age</label>
                    <input type="number" name="age" class="form-control" required>
                </div>
                <div class="form-group" style="flex:1;">
                    <label>Gender</label>
                    <select name="gender" class="form-control" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="text" name="phone" class="form-control" required>
            </div>
            
            <button type="submit" class="btn-search btn-primary" style="background-color: var(--primary-color);">Proceed to Payment</button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
