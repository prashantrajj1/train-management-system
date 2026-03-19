<?php
require_once '../config/db.php';
include '../includes/header.php';

// Security check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: " . BASE_URL . "index.php");
    exit;
}

// Handle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    if ($_GET['action'] == 'make_admin') {
        $stmt = $pdo->prepare("UPDATE User SET Role = 'Admin' WHERE User_ID = ?");
        $stmt->execute([$id]);
    } elseif ($_GET['action'] == 'make_passenger') {
        $stmt = $pdo->prepare("UPDATE User SET Role = 'Passenger' WHERE User_ID = ?");
        $stmt->execute([$id]);
    } elseif ($_GET['action'] == 'delete') {
        $stmt = $pdo->prepare("DELETE FROM User WHERE User_ID = ?");
        $stmt->execute([$id]);
    }
    header("Location: users.php");
    exit;
}

$users = $pdo->query("SELECT * FROM User ORDER BY Role ASC, Username ASC")->fetchAll();
?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="color: var(--primary-color);">User Management</h2>
        <a href="index.php" class="btn" style="width: auto; padding: 8px 15px; background: #6c757d;">Back to Dashboard</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Username</th>
                <th>Role</th>
                <th>Phone</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
            <tr>
                <td><?php echo $u['User_ID']; ?></td>
                <td><?php echo htmlspecialchars($u['Full_Name']); ?></td>
                <td><?php echo htmlspecialchars($u['Username']); ?></td>
                <td>
                    <span class="badge <?php echo $u['Role'] == 'Admin' ? 'badge-primary' : 'badge-success'; ?>">
                        <?php echo $u['Role']; ?>
                    </span>
                </td>
                <td><?php echo htmlspecialchars($u['Phone']); ?></td>
                <td>
                    <?php if ($u['User_ID'] != $_SESSION['user_id']): ?>
                        <?php if ($u['Role'] == 'Passenger'): ?>
                            <a href="users.php?action=make_admin&id=<?php echo $u['User_ID']; ?>" class="btn-action btn-edit" style="background: #8b5cf6;">Make Admin</a>
                        <?php else: ?>
                            <a href="users.php?action=make_passenger&id=<?php echo $u['User_ID']; ?>" class="btn-action btn-edit" style="background: #10b981;">Make Passenger</a>
                        <?php endif; ?>
                        <a href="users.php?action=delete&id=<?php echo $u['User_ID']; ?>" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                    <?php else: ?>
                        <span style="color: #888; font-style: italic;">You</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
