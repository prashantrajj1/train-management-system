<?php include 'includes/header.php'; ?>

<div class="hero">
    <h1>Plan Your Journey Today</h1>
    <p>Search and book trains easily</p>
</div>

<div class="search-container">
    <form action="/tms/booking/search.php" method="GET">
        <div style="display: flex; gap: 20px;">
            <div class="form-group" style="flex: 1;">
                <label for="from">From Station</label>
                <input type="text" id="from" name="from_station" class="form-control" placeholder="Origin" required>
            </div>
            <div class="form-group" style="flex: 1;">
                <label for="to">To Station</label>
                <input type="text" id="to" name="to_station" class="form-control" placeholder="Destination" required>
            </div>
            <div class="form-group" style="flex: 1;">
                <label for="date">Travel Date</label>
                <input type="date" id="date" name="travel_date" class="form-control" required>
            </div>
        </div>
        <button type="submit" class="btn">Search Trains</button>
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
