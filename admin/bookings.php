<?php
require_once '../config/db.php';
include '../includes/header.php';

// Security check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: " . BASE_URL . "index.php");
    exit;
}

// Handle cancellation
if (isset($_GET['cancel_id'])) {
    $cancel_id = $_GET['cancel_id'];
    $stmt = $pdo->prepare("UPDATE Ticket SET Status = 'Cancelled' WHERE Ticket_ID = ?");
    $stmt->execute([$cancel_id]);
    header("Location: bookings.php?msg=cancelled");
    exit;
}

// Fetch all bookings
$query = "
    SELECT t.Ticket_ID, p.Name as Passenger, tr.Train_Name, t.Travel_Date, t.Status, t.Fare, t.Class_Type 
    FROM Ticket t 
    JOIN Passenger p ON t.Passenger_ID = p.Passenger_ID 
    JOIN Train tr ON t.Train_ID = tr.Train_ID 
    ORDER BY t.Ticket_ID DESC
";
$bookings = $pdo->query($query)->fetchAll();
?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="color: var(--primary-color);">All Bookings</h2>
        <a href="index.php" class="btn" style="width: auto; padding: 8px 15px; background: #6c757d;">Back to Dashboard</a>
    </div>

    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'cancelled'): ?>
        <p style="color: green; background: #d4edda; padding: 10px; border-radius: 4px;">Ticket cancelled successfully.</p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Ticket ID</th>
                <th>Passenger</th>
                <th>Train</th>
                <th>Travel Date</th>
                <th>Class</th>
                <th>Fare</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bookings as $b): ?>
            <tr>
                <td>#<?php echo $b['Ticket_ID']; ?></td>
                <td><strong><?php echo htmlspecialchars($b['Passenger']); ?></strong></td>
                <td><?php echo htmlspecialchars($b['Train_Name']); ?></td>
                <td><?php echo date('d M, Y', strtotime($b['Travel_Date'])); ?></td>
                <td><?php echo $b['Class_Type']; ?></td>
                <td>₹<?php echo number_format($b['Fare'], 2); ?></td>
                <td>
                    <?php 
                        $badgeClass = match($b['Status']) {
                            'Confirmed' => 'badge-success',
                            'Cancelled' => 'badge-danger',
                            'Waiting list' => 'badge-warning',
                            default => 'badge-primary'
                        };
                    ?>
                    <span class="badge <?php echo $badgeClass; ?>"><?php echo $b['Status']; ?></span>
                </td>
                <td>
                    <?php if ($b['Status'] != 'Cancelled'): ?>
                        <a href="bookings.php?cancel_id=<?php echo $b['Ticket_ID']; ?>" class="btn-action btn-delete" onclick="return confirm('Confirm cancellation?');">Cancel</a>
                    <?php else: ?>
                        <span style="color: #888;">N/A</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
