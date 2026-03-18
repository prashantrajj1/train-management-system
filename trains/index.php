<?php
require_once '../config/db.php';
include '../includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: /tms/index.php");
    exit;
}

// Fetch all trains
$stmt = $pdo->query("SELECT * FROM Train ORDER BY Train_ID DESC");
$trains = $stmt->fetchAll();
?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="color: var(--primary-color);">Manage Trains</h2>
        <a href="/tms/trains/add.php" class="btn-action btn-primary"><i class="fa fa-plus"></i> Add New Train</a>
    </div>

    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
        <p style="color: green; background: #d4edda; padding: 10px; border-radius: 4px;">Train deleted successfully.</p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Train Name</th>
                <th>Train Type</th>
                <th>Total Seats</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($trains as $t): ?>
            <tr>
                <td><?php echo htmlspecialchars($t['Train_ID']); ?></td>
                <td><?php echo htmlspecialchars($t['Train_Name']); ?></td>
                <td><?php echo htmlspecialchars($t['Train_Type']); ?></td>
                <td><?php echo htmlspecialchars($t['Total_Seats']); ?></td>
                <td>
                    <a href="/tms/trains/add.php?id=<?php echo $t['Train_ID']; ?>" class="btn-action btn-edit">Edit</a>
                    <a href="/tms/trains/delete.php?id=<?php echo $t['Train_ID']; ?>" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this train?');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($trains)): ?>
            <tr>
                <td colspan="5" style="text-align: center;">No trains found in the system.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
