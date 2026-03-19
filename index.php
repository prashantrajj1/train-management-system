<?php
require_once 'config/db.php';
include 'includes/header.php';

// Fetch all stations for the dropdowns
$stmt_st = $pdo->query("SELECT Station_Name, Station_Code FROM Station ORDER BY Station_Name ASC");
$all_stations = $stmt_st->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="hero">
    <h1>Plan Your Journey Today</h1>
    <p>Search and book trains easily</p>
</div>

<div class="search-container">
    <form action="<?php echo BASE_URL; ?>booking/search.php" method="GET">
        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
            <div class="form-group" style="flex: 1; min-width: 200px;">
                <label for="from">From Station</label>
                <select id="from" name="from_station" class="form-control" required style="padding: 10px; font-size: 1rem; height: 45px;">
                    <option value="">Select Origin Station</option>
                    <?php foreach($all_stations as $st): ?>
                    <option value="<?php echo htmlspecialchars($st['Station_Name']); ?>"><?php echo htmlspecialchars($st['Station_Name']) . ' (' . htmlspecialchars($st['Station_Code']) . ')'; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="flex: 1; min-width: 200px;">
                <label for="to">To Station</label>
                <select id="to" name="to_station" class="form-control" required style="padding: 10px; font-size: 1rem; height: 45px;">
                    <option value="">Select Destination Station</option>
                    <?php foreach($all_stations as $st): ?>
                    <option value="<?php echo htmlspecialchars($st['Station_Name']); ?>"><?php echo htmlspecialchars($st['Station_Name']) . ' (' . htmlspecialchars($st['Station_Code']) . ')'; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="flex: 1; min-width: 200px;">
                <label for="date">Travel Date</label>
                <input type="date" id="date" name="travel_date" class="form-control" required>
            </div>
        </div>
        <button type="submit" class="btn" style="margin-top: 15px;" id="searchBtn">Search Trains</button>
    </form>
</div>



<div class="container" style="text-align: center; margin-top: 50px;">
    <h2>Why Choose Us?</h2>
    <div style="display: flex; justify-content: space-around; margin-top: 30px;">
        <div style="flex: 1;">
            <i class="fa fa-bolt" style="font-size: 2rem; color: #004a99;"></i>
            <h3>Fast Booking</h3>
            <p>Book your tickets in under a minute.</p>
        </div>
        <div style="flex: 1;">
            <i class="fa fa-shield" style="font-size: 2rem; color: #004a99;"></i>
            <h3>Secure Payment</h3>
            <p>Your transactions are 100% secure.</p>
        </div>
        <div style="flex: 1;">
            <i class="fa fa-headset" style="font-size: 2rem; color: #004a99;"></i>
            <h3>24/7 Support</h3>
            <p>We are here to help you anytime.</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
