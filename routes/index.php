<?php
require_once '../config/db.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: /tms/train-management-system/index.php");
    exit;
}

// Fetch all schedules joining with Train and Station
$sql = "SELECT sc.Schedule_ID, t.Train_Name, s.Station_Name, sc.Arrival_Time, sc.Departure_Time, sc.Travel_Date 
        FROM Schedule sc
        JOIN Train t ON sc.Train_ID = t.Train_ID
        JOIN Station s ON sc.Station_ID = s.Station_ID
        ORDER BY sc.Travel_Date DESC, sc.Arrival_Time ASC";
$stmt = $pdo->query($sql);
$schedules = $stmt->fetchAll();
?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="color: var(--primary-color);">Manage Schedules</h2>
        <a href="/tms/train-management-system/routes/add_schedule.php" class="btn-action btn-primary"><i class="fa fa-plus"></i> Add New Schedule</a>
    </div>

    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
        <p style="color: green; background: #d4edda; padding: 10px; border-radius: 4px;">Schedule deleted successfully.</p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Train</th>
                <th>Station</th>
                <th>Arrival</th>
                <th>Departure</th>
                <th>Travel Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($schedules as $s): ?>
            <tr>
                <td><?php echo htmlspecialchars($s['Schedule_ID']); ?></td>
                <td><?php echo htmlspecialchars($s['Train_Name']); ?></td>
                <td><?php echo htmlspecialchars($s['Station_Name']); ?></td>
                <td><?php echo htmlspecialchars($s['Arrival_Time']); ?></td>
                <td><?php echo htmlspecialchars($s['Departure_Time']); ?></td>
                <td><?php echo htmlspecialchars($s['Travel_Date']); ?></td>
                <td>
                    <a href="/tms/train-management-system/routes/delete_schedule.php?id=<?php echo $s['Schedule_ID']; ?>" class="btn-action btn-delete" onclick="return confirm('Delete this schedule?');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($schedules)): ?>
            <tr>
                <td colspan="7" style="text-align: center;">No schedules found.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
