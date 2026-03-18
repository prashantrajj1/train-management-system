<?php
require_once '../config/db.php';
include '../includes/header.php';

// Security check: Only Admins can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: /tms/index.php");
    exit;
}

// Fetch some basic stats
$trainsCount = $pdo->query("SELECT COUNT(*) FROM Train")->fetchColumn();
$stationsCount = $pdo->query("SELECT COUNT(*) FROM Station")->fetchColumn();
$usersCount = $pdo->query("SELECT COUNT(*) FROM User")->fetchColumn();
$ticketsCount = $pdo->query("SELECT COUNT(*) FROM Ticket")->fetchColumn();
?>

<div class="container">
    <h2 style="color: var(--primary-color); margin-bottom: 20px;">Admin Dashboard</h2>
    <p>Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>! You have administrative privileges.</p>

    <div style="display: flex; gap: 20px; margin-top: 30px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 200px; background: #e2eefd; padding: 20px; border-radius: 8px; text-align: center; border: 1px solid var(--border-color);">
            <i class="fa fa-train" style="font-size: 30px; color: var(--primary-color); margin-bottom: 15px;"></i>
            <h3><?php echo $trainsCount; ?> Trains</h3>
            <p style="margin-top: 10px;"><a href="/tms/trains/index.php" class="btn-primary btn-action">Manage Trains</a></p>
        </div>
        
        <div style="flex: 1; min-width: 200px; background: #e2eefd; padding: 20px; border-radius: 8px; text-align: center; border: 1px solid var(--border-color);">
            <i class="fa fa-building" style="font-size: 30px; color: var(--primary-color); margin-bottom: 15px;"></i>
            <h3><?php echo $stationsCount; ?> Stations</h3>
            <p style="margin-top: 10px;"><a href="/tms/stations/index.php" class="btn-primary btn-action">Manage Stations</a></p>
        </div>

        <div style="flex: 1; min-width: 200px; background: #e2eefd; padding: 20px; border-radius: 8px; text-align: center; border: 1px solid var(--border-color);">
            <i class="fa fa-users" style="font-size: 30px; color: var(--primary-color); margin-bottom: 15px;"></i>
            <h3><?php echo $usersCount; ?> Users</h3>
            <p style="margin-top: 10px; color: #666;">View Only</p>
        </div>

        <div style="flex: 1; min-width: 200px; background: #e2eefd; padding: 20px; border-radius: 8px; text-align: center; border: 1px solid var(--border-color);">
            <i class="fa fa-ticket" style="font-size: 30px; color: var(--primary-color); margin-bottom: 15px;"></i>
            <h3><?php echo $ticketsCount; ?> Bookings</h3>
            <p style="margin-top: 10px;"><a href="/tms/reports/index.php" class="btn-primary btn-action">View Reports</a></p>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
