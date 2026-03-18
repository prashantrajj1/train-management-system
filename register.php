<?php
require_once 'config/db.php';
include 'includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = $_POST['fullname'] ?? '';
    $age = $_POST['age'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($fullname && $age && $phone && $username && $password) {
        $stmt = $pdo->prepare("SELECT * FROM User WHERE Username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = 'Username already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO User (Full_Name, Age, Phone, Username, Password, Role) VALUES (?, ?, ?, ?, ?, 'Passenger')");
            if ($stmt->execute([$fullname, $age, $phone, $username, $hash])) {
                $success = 'Registration successful! You can now login.';
            } else {
                $error = 'Registration failed.';
            }
        }
    } else {
        $error = 'Please fill all fields.';
    }
}
?>

<div class="container" style="max-width: 400px; margin-top: 50px;">
    <h2 style="text-align: center; color: var(--primary-color);">Register</h2>
    
    <?php if ($error): ?>
        <div style="color: red; margin-bottom: 15px; text-align: center;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div style="color: green; margin-bottom: 15px; text-align: center;"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="fullname" class="form-control" required>
        </div>
        <div class="form-group" style="display: flex; gap: 10px;">
            <div style="flex: 1;">
                <label>Age</label>
                <input type="number" name="age" class="form-control" required min="1" max="120">
            </div>
            <div style="flex: 2;">
                <label>Phone Number</label>
                <input type="text" name="phone" class="form-control" required pattern="[0-9]{10}">
            </div>
        </div>
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn-search btn-primary">Register</button>
    </form>
    <div style="text-align: center; margin-top: 15px;">
        <a href="login.php" style="color: var(--primary-color);">Already have an account? Login</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
