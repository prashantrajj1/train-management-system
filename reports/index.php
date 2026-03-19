<?php
require_once '../config/db.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: /tms/train-management-system/index.php");
    exit;
}

// Fetch all tickets
$sql = "SELECT t.Ticket_ID, p.Name as Passenger, tr.Train_Name, t.Travel_Date, t.Class_Type, t.Status, t.Fare 
        FROM Ticket t
        JOIN Passenger p ON t.Passenger_ID = p.Passenger_ID
        JOIN Train tr ON t.Train_ID = tr.Train_ID
        ORDER BY t.Ticket_ID DESC";
$stmt = $pdo->query($sql);
$tickets = $stmt->fetchAll();

// Calculate Revenue
$rev_stmt = $pdo->query("SELECT SUM(Fare) FROM Ticket WHERE Status != 'Cancelled'");
$revenue = $rev_stmt->fetchColumn();
?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="color: var(--primary-color);">Booking & Revenue Reports</h2>
        <div style="background: #d4edda; color: #155724; padding: 10px 20px; border-radius: 4px; font-weight: bold;">
            Total Revenue: ₹<?php echo number_format($revenue ?? 0, 2); ?>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Ticket ID</th>
                <th>Passenger</th>
                <th>Train</th>
                <th>Date</th>
                <th>Class</th>
                <th>Fare</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($tickets as $t): ?>
            <tr>
                <td><?php echo $t['Ticket_ID']; ?></td>
                <td><?php echo htmlspecialchars($t['Passenger']); ?></td>
                <td><?php echo htmlspecialchars($t['Train_Name']); ?></td>
                <td><?php echo htmlspecialchars($t['Travel_Date']); ?></td>
                <td><?php echo htmlspecialchars($t['Class_Type']); ?></td>
                <td>₹<?php echo number_format($t['Fare'], 2); ?></td>
                <td>
                    <span style="color: <?php echo $t['Status'] == 'Cancelled' ? 'red' : 'green'; ?>; font-weight: bold;">
                        <?php echo htmlspecialchars($t['Status']); ?>
                    </span>
                </td>
                <td>
                    <a href="/tms/train-management-system/reports/ticket.php?id=<?php echo $t['Ticket_ID']; ?>" class="btn-action btn-primary">View</a>
                    <?php if($t['Status'] != 'Cancelled'): ?>
                        <a href="/tms/train-management-system/reports/cancel.php?id=<?php echo $t['Ticket_ID']; ?>" class="btn-action btn-delete" onclick="return confirm('Cancel this ticket?');">Cancel</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($tickets)): ?>
            <tr><td colspan="8" style="text-align: center;">No bookings found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
