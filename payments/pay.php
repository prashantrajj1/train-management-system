<?php
require_once '../config/db.php';
include '../includes/header.php';

$ticket_id = $_GET['ticket_id'] ?? '';

if (!$ticket_id) {
    die("Invalid request");
}

$stmt = $pdo->prepare("SELECT t.*, p.Name, tr.Train_Name 
                       FROM Ticket t 
                       JOIN Passenger p ON t.Passenger_ID = p.Passenger_ID 
                       JOIN Train tr ON t.Train_ID = tr.Train_ID 
                       WHERE t.Ticket_ID = ?");
$stmt->execute([$ticket_id]);
$ticket = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mode = $_POST['payment_mode'];
    
    // Create Payment Record
    $stmt_pay = $pdo->prepare("INSERT INTO Payment (Ticket_ID, Amount, Payment_Mode, Payment_Status) VALUES (?, ?, ?, 'Success')");
    $stmt_pay->execute([$ticket_id, $ticket['Fare'], $mode]);
    
    echo "<script>alert('Payment Successful! Ticket Booked.'); window.location.href = '/tms/train-management-system/reports/ticket.php?id=$ticket_id';</script>";
    exit;
}
?>

<div class="container" style="max-width: 600px;">
    <h2 style="color: var(--primary-color); margin-bottom: 20px;">Payment Gateway</h2>
    
    <div style="background: #e9ecef; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h3>Order Summary</h3>
        <p><strong>Passenger:</strong> <?php echo htmlspecialchars($ticket['Name']); ?></p>
        <p><strong>Train:</strong> <?php echo htmlspecialchars($ticket['Train_Name']); ?></p>
        <p><strong>Travel Date:</strong> <?php echo htmlspecialchars($ticket['Travel_Date']); ?></p>
        <p><strong>Class:</strong> <?php echo htmlspecialchars($ticket['Class_Type']); ?></p>
        <hr style="margin: 10px 0; border-color: #ccc;">
        <h4 style="color: green; font-size: 20px;">Total Amount: ₹<?php echo number_format($ticket['Fare'], 2); ?></h4>
    </div>

    <div class="booking-widget" style="width: 100%; box-shadow: none; border: 1px solid var(--border-color); margin: 0; padding: 20px;">
        <form method="POST">
            <div class="form-group">
                <label>Select Payment Mode</label>
                <select name="payment_mode" class="form-control" required>
                    <option value="UPI">UPI / BHIM</option>
                    <option value="Credit Card">Credit Card</option>
                    <option value="Debit Card">Debit Card</option>
                    <option value="Net Banking">Net Banking</option>
                </select>
            </div>
            <button type="submit" class="btn-search" style="background-color: #28a745;">Make Payment of ₹<?php echo number_format($ticket['Fare'], 2); ?></button>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
