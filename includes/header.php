<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Train Management System</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<header>
    <div class="logo">
        <h1><i class="fa fa-train"></i> Train Management</h1>
    </div>
    <nav>
        <ul>
            <li><a href="<?php echo BASE_URL; ?>index.php">Home</a></li>
            <li><a href="<?php echo BASE_URL; ?>trains/index.php">Trains</a></li>
            
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
                <li><a href="<?php echo BASE_URL; ?>stations/index.php">Stations</a></li>
                <li><a href="<?php echo BASE_URL; ?>routes/index.php">Routes</a></li>
                <li><a href="<?php echo BASE_URL; ?>admin/index.php">Dashboard</a></li>
            <?php endif; ?>

            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['role'] !== 'Admin'): ?>
                <li>
                    <a href="<?php echo BASE_URL; ?>account.php" style="display:flex; align-items:center; gap:6px;">
                        <i class="fa fa-user-circle"></i>
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </a>
                </li>
                <?php else: ?>
                <li><span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span></li>
                <?php endif; ?>
                <li><a href="<?php echo BASE_URL; ?>logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="<?php echo BASE_URL; ?>login.php">Login</a></li>
                <li><a href="<?php echo BASE_URL; ?>register.php">Register</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
