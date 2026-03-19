<?php
require_once 'config/db.php';
include 'includes/header.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT * FROM User WHERE Username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['Password'])) {
            $_SESSION['user_id'] = $user['User_ID'];
            $_SESSION['username'] = $user['Username'];
            $_SESSION['role'] = $user['Role'];
            
            if ($user['Role'] === 'Admin') {
                header("Location: /tms/train-management-system/admin/index.php");
            } else {
                header("Location: /tms/train-management-system/index.php");
            }
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Please fill all fields.';
    }
}
?>

<div class="container" style="max-width: 400px; margin-top: 50px;">
    <h2 style="text-align: center; color: var(--primary-color);">Login</h2>
    
    <?php if ($error): ?>
        <div style="color: red; margin-bottom: 15px; text-align: center;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn-search btn-primary">Login</button>
    </form>
    <div style="text-align: center; margin-top: 15px;">
        <a href="register.php" style="color: var(--primary-color);">Don't have an account? Register</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
