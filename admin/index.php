<?php
require_once '../config/db.php';
include '../includes/header.php';

// Security check: Only Admins can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: " . BASE_URL . "index.php");
    exit;
}

// Fetch metrics
$trainsCount = $pdo->query("SELECT COUNT(*) FROM Train")->fetchColumn();
$stationsCount = $pdo->query("SELECT COUNT(*) FROM Station")->fetchColumn();
$usersCount = $pdo->query("SELECT COUNT(*) FROM User WHERE Role = 'Passenger'")->fetchColumn();
$ticketsCount = $pdo->query("SELECT COUNT(*) FROM Ticket")->fetchColumn();
$revenue = $pdo->query("SELECT SUM(Fare) FROM Ticket WHERE Status != 'Cancelled'")->fetchColumn() ?: 0;

// Fetch last 5 bookings
$recentBookings = $pdo->query("
    SELECT t.Ticket_ID, p.Name as Passenger, tr.Train_Name, t.Travel_Date, t.Status, t.Fare 
    FROM Ticket t 
    JOIN Passenger p ON t.Passenger_ID = p.Passenger_ID 
    JOIN Train tr ON t.Train_ID = tr.Train_ID 
    ORDER BY t.Ticket_ID DESC 
    LIMIT 5
")->fetchAll();
?>

<div class="container" style="max-width: 1200px; margin: auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h1 style="color: var(--primary-color); font-size: 2rem;">Admin Dashboard</h1>
            <p style="color: #666;">Welcome back, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>. Here is the daily summary.</p>
        </div>
        <div>
            <span class="badge badge-primary" style="padding: 10px 20px; font-size: 0.9rem;">
                <i class="fa fa-calendar"></i> <?php echo date('d M, Y'); ?>
            </span>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <i class="fa fa-indian-rupee-sign"></i>
            <h3>₹<?php echo number_format($revenue, 0); ?></h3>
            <p>Total Revenue</p>
        </div>
        <div class="stat-card" style="border-top-color: #ff6600;">
            <i class="fa fa-train" style="color: #ff6600;"></i>
            <h3><?php echo $trainsCount; ?></h3>
            <p>Active Trains</p>
        </div>
        <div class="stat-card" style="border-top-color: #10b981;">
            <i class="fa fa-map-marker-alt" style="color: #10b981;"></i>
            <h3><?php echo $stationsCount; ?></h3>
            <p>Total Stations</p>
        </div>
        <div class="stat-card" style="border-top-color: #8b5cf6;">
            <i class="fa fa-users" style="color: #8b5cf6;"></i>
            <h3><?php echo $usersCount; ?></h3>
            <p>Passengers</p>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-top: 20px;">
        
        <!-- Recent Activity Section -->
        <div style="background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); padding: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="color: #333;"><i class="fa fa-clock-rotate-left"></i> Recent Bookings</h3>
                <a href="<?php echo BASE_URL; ?>admin/bookings.php" style="color: var(--primary-color); text-decoration: none; font-size: 0.9rem; font-weight: 600;">View All</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Passenger</th>
                        <th>Train</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentBookings as $b): ?>
                    <tr>
                        <td>#<?php echo $b['Ticket_ID']; ?></td>
                        <td><strong><?php echo htmlspecialchars($b['Passenger']); ?></strong></td>
                        <td><?php echo htmlspecialchars($b['Train_Name']); ?></td>
                        <td><?php echo date('d M', strtotime($b['Travel_Date'])); ?></td>
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
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Quick Actions Sidebar -->
        <div style="background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); padding: 24px;">
            <h3 style="color: #333; margin-bottom: 20px;"><i class="fa fa-bolt"></i> Quick Actions</h3>
            
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <a href="<?php echo BASE_URL; ?>trains/add.php" class="btn" style="text-align: left; background: #f8fafc; color: #334155; border: 1px solid #e2e8f0; width: 100%;">
                    <i class="fa fa-plus-circle" style="color: var(--primary-color);"></i> Add New Train
                </a>
                <a href="<?php echo BASE_URL; ?>stations/add.php" class="btn" style="text-align: left; background: #f8fafc; color: #334155; border: 1px solid #e2e8f0; width: 100%;">
                    <i class="fa fa-plus-circle" style="color: var(--primary-color);"></i> Add Station
                </a>
                <a href="<?php echo BASE_URL; ?>routes/add_schedule.php" class="btn" style="text-align: left; background: #f8fafc; color: #334155; border: 1px solid #e2e8f0; width: 100%;">
                    <i class="fa fa-calendar-plus" style="color: var(--primary-color);"></i> Add Schedule
                </a>
                <hr style="border: none; border-top: 1px solid #eee; margin: 10px 0;">
                <a href="<?php echo BASE_URL; ?>trains/index.php" class="btn" style="text-align: left; background: #f8fafc; color: #334155; border: 1px solid #e2e8f0; width: 100%;">
                    <i class="fa fa-list"></i> View All Trains
                </a>
                <a href="<?php echo BASE_URL; ?>admin/users.php" class="btn" style="text-align: left; background: #f8fafc; color: #334155; border: 1px solid #e2e8f0; width: 100%;">
                    <i class="fa fa-user-shield"></i> User Management
                </a>
                <a href="<?php echo BASE_URL; ?>admin/bookings.php" class="btn" style="text-align: left; background: #f8fafc; color: #334155; border: 1px solid #e2e8f0; width: 100%;">
                    <i class="fa fa-ticket"></i> All Bookings
                </a>
                <a href="<?php echo BASE_URL; ?>reports/index.php" class="btn" style="text-align: left; background: #fff; color: #334155; border: 1px solid #e2e8f0; width: 100%;">
                    <i class="fa fa-file-invoice-dollar"></i> Global Reports
                </a>
            </div>
        </div>

    </div>
</div>

<?php include '../includes/footer.php'; ?>
