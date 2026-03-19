<?php
require_once '../config/db.php';
include '../includes/header.php';

// Fetch ticket, passenger, train, and reservation details
$id = $_GET['id'] ?? '';
if (!$id) {
    die("Invalid request");
}

$stmt = $pdo->prepare("SELECT t.*, p.Name, p.Age, p.Gender, p.Phone, tr.Train_Name, tr.Train_Type, r.Coach_Number, r.Seat_Number
                       FROM Ticket t
                       JOIN Passenger p ON t.Passenger_ID = p.Passenger_ID
                       JOIN Train tr ON t.Train_ID = tr.Train_ID
                       LEFT JOIN Reservation r ON t.Ticket_ID = r.Ticket_ID
                       WHERE t.Ticket_ID = ?");
$stmt->execute([$id]);
$ticket = $stmt->fetch();
?>

<div class="container" style="max-width: 800px;">
    <h2 style="color: var(--primary-color); text-align: center; margin-bottom: 20px;">Electronic Ticket (EWS)</h2>
    
    <div style="border: 2px solid var(--primary-color); padding: 20px; border-radius: 8px;">
        <div style="display: flex; justify-content: space-between; border-bottom: 2px solid #ccc; padding-bottom: 10px; margin-bottom: 20px;">
            <div style="color: var(--primary-color); font-weight: bold; font-size: 24px;">IRCTC E-Ticket</div>
            <div><strong>PNR / Ticket ID:</strong> <?php echo 1000000 + $ticket['Ticket_ID']; ?></div>
        </div>

        <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
            <div style="width: 48%;">
                <h4 style="color: var(--secondary-color); margin-bottom: 10px;">Journey Details</h4>
                <p><strong>Train:</strong> <?php echo htmlspecialchars($ticket['Train_Name']); ?> (<?php echo htmlspecialchars($ticket['Train_Type']); ?>)</p>
                <p><strong>Date:</strong> <?php echo htmlspecialchars($ticket['Travel_Date']); ?></p>
                <p><strong>Class:</strong> <?php echo htmlspecialchars($ticket['Class_Type']); ?></p>
                <p><strong>Status:</strong> <span style="color: green; font-weight: bold;"><?php echo htmlspecialchars($ticket['Status']); ?></span></p>
                <?php if($ticket['Status'] === 'Confirmed'): ?>
                    <p><strong>Coach / Seat:</strong> <?php echo htmlspecialchars($ticket['Coach_Number']); ?> / <?php echo htmlspecialchars($ticket['Seat_Number']); ?></p>
                <?php endif; ?>
            </div>
            <div style="width: 48%;">
                <h4 style="color: var(--secondary-color); margin-bottom: 10px;">Passenger Details</h4>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($ticket['Name']); ?></p>
                <p><strong>Age / Gender:</strong> <?php echo htmlspecialchars($ticket['Age']); ?> / <?php echo htmlspecialchars($ticket['Gender']); ?></p>
                <p><strong>Contact:</strong> <?php echo htmlspecialchars($ticket['Phone']); ?></p>
            </div>
        </div>
        
        <div style="text-align: right; font-size: 20px; font-weight: bold; border-top: 2px solid #ccc; padding-top: 10px;">
            Total Fare: ₹<?php echo number_format($ticket['Fare'], 2); ?>
        </div>
    </div>
    
    <div style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" class="btn-action btn-primary" style="font-size: 16px; padding: 10px 20px;">Print Ticket</button>
        <a href="/tms/train-management-system/index.php" class="btn-action" style="background: #6c757d; font-size: 16px; padding: 10px 20px; margin-left: 10px;">Go to Home</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
