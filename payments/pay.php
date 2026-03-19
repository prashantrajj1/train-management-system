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
    
    echo "<script>alert('Payment Successful! Ticket Booked.'); window.location.href = '/tms/reports/ticket.php?id=$ticket_id';</script>";
    exit;
}
?>

<div class="container" style="max-width: 600px;">
    <h2 style="color: var(--primary-color); margin-bottom: 20px;">Payment Gateway</h2>
    
    <!-- Order Summary -->
    <div style="background: #e9ecef; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h3>Order Summary</h3>
        <p><strong>Passenger:</strong> <?php echo htmlspecialchars($ticket['Name']); ?></p>
        <p><strong>Train:</strong> <?php echo htmlspecialchars($ticket['Train_Name']); ?></p>
        <p><strong>Travel Date:</strong> <?php echo htmlspecialchars($ticket['Travel_Date']); ?></p>
        <p><strong>Class:</strong> <?php echo htmlspecialchars($ticket['Class_Type']); ?></p>
        <hr style="margin: 10px 0; border-color: #ccc;">
        <h4 style="color: green; font-size: 20px;">Total Amount: ₹<?php echo number_format($ticket['Fare'], 2); ?></h4>
    </div>

    <!-- Payment Form -->
    <div style="background: white; border: 1px solid #ddd; border-radius: 8px; padding: 24px;">
        <form method="POST" id="payment-form">
            <div class="form-group">
                <label>Select Payment Mode</label>
                <select name="payment_mode" id="payment_mode" class="form-control" required onchange="toggleQR(this.value)">
                    <option value="UPI">UPI / BHIM</option>
                    <option value="Credit Card">Credit Card</option>
                    <option value="Debit Card">Debit Card</option>
                    <option value="Net Banking">Net Banking</option>
                </select>
            </div>

            <!-- UPI QR Section -->
            <div id="upi-qr-box" style="text-align: center; margin: 20px 0; padding: 20px;
                 background: #f8f9ff; border: 2px dashed #004a99; border-radius: 10px;">
                <p style="color: #555; margin-bottom: 12px; font-weight: 600;">
                    <i class="fa fa-qrcode"></i>&nbsp; Scan QR with any UPI app to pay
                </p>
                <img src="/tms/assets/img/upi_qr.jpg"
                     alt="UPI QR Code - PRASHANT KUMAR"
                     style="width: 220px; height: auto; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                <p style="margin-top: 12px; font-size: 0.9rem; color: #444;">
                    <strong>PRASHANT KUMAR</strong><br>
                    <span style="color: #666;">UPI ID: 9801813297@superyes</span>
                </p>
                <p style="margin-top: 8px; font-size: 0.78rem; color: #999;">
                    After scanning and paying, click the button below to confirm.
                </p>
            </div>

            <button type="submit" class="btn-search" style="background-color: #28a745; width: 100%; margin-top: 10px;">
                <i class="fa fa-check-circle"></i>&nbsp;
                Confirm Payment of ₹<?php echo number_format($ticket['Fare'], 2); ?>
            </button>
        </form>
    </div>
</div>

<script>
function toggleQR(mode) {
    const qrBox = document.getElementById('upi-qr-box');
    qrBox.style.display = (mode === 'UPI') ? 'block' : 'none';
}
// Initialize on load
toggleQR(document.getElementById('payment_mode').value);
</script>

<?php include '../includes/footer.php'; ?>
