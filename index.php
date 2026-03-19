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
    <form id="trainSearchForm">
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

<!-- Dynamic Results Container -->
<div id="searchResults" class="container" style="display: none; margin-top: 30px;">
    <h2 style="color: var(--primary-color);">Available Trains</h2>
    <div id="resultsContent"></div>
</div>

<!-- Modal for Route and Seats -->
<div id="trainModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5);">
    <div class="modal-content" style="background-color: #fefefe; margin: 10% auto; padding: 30px; border: 1px solid #888; width: 80%; max-width: 800px; border-radius: 8px; position: relative;">
        <span class="close" onclick="closeModal()" style="color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
        <h2 id="modalTitle" style="color: var(--primary-color); margin-top: 0;">Modal Title</h2>
        <div id="modalBody" style="margin-top: 20px;">
            <!-- Content loaded dynamically -->
        </div>
    </div>
</div>

<script>
document.getElementById('trainSearchForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    document.getElementById('searchBtn').textContent = 'Searching...';
    document.getElementById('searchBtn').disabled = true;
    
    const from = document.getElementById('from').value;
    const to = document.getElementById('to').value;
    const date = document.getElementById('date').value;
    
    fetch(`/tms/train-management-system/api/search_trains.php?from_station=${encodeURIComponent(from)}&to_station=${encodeURIComponent(to)}&travel_date=${encodeURIComponent(date)}`)
        .then(response => response.json())
        .then(data => {
            const resultsDiv = document.getElementById('searchResults');
            const contentDiv = document.getElementById('resultsContent');
            
            resultsDiv.style.display = 'block';
            contentDiv.innerHTML = '';
            
            if (data.error) {
                contentDiv.innerHTML = `<div style="padding: 15px; background: #f8d7da; color: #721c24; border-radius: 4px;">${data.error}</div>`;
                return;
            }
            
            if (!data.trains || data.trains.length === 0) {
                contentDiv.innerHTML = `<div style="padding: 15px; background: #fff3cd; color: #856404; border-radius: 4px;">No trains found for this route and date.</div>`;
                return;
            }
            
            let html = '<div style="display: flex; flex-direction: column; gap: 20px;">';
            
            data.trains.forEach(train => {
                html += `
                <div style="border: 1px solid #ddd; border-radius: 8px; padding: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); background: #fff;">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 15px; font-weight: bold;">
                        <span style="font-size: 1.2rem; color: #333;">${train.Train_Name} (${train.Train_Type})</span>
                        <div style="display: flex; gap: 10px;">
                            <button onclick="viewRoute(${train.Train_ID}, '${train.Train_Name}')" class="btn" style="background: #e0e0e0; color: #333; padding: 5px 15px; font-size: 0.9rem;">View Route</button>
                            <button onclick='viewSeats(${JSON.stringify(train.Classes)}, ${train.Train_ID}, "${date}")' class="btn" style="background: var(--primary-color); padding: 5px 15px; font-size: 0.9rem;">Check Seats</button>
                        </div>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; margin-top: 15px; align-items: center;">
                        <div style="text-align: center;">
                            <div style="font-size: 1.2rem; font-weight: bold; color: #444;">${train.Dept_Formatted || 'N/A'}</div>
                            <div style="color: #666; font-size: 0.9rem;">${train.From_Station}</div>
                        </div>
                        
                        <div style="flex: 1; text-align: center; color: #888; position: relative; margin: 0 20px;">
                            <div style="font-size: 0.8rem;">${train.Duration}</div>
                            <div style="border-top: 2px dashed #ccc; margin: 5px 0;"></div>
                        </div>
                        
                        <div style="text-align: center;">
                            <div style="font-size: 1.2rem; font-weight: bold; color: #444;">${train.Arr_Formatted || 'N/A'}</div>
                            <div style="color: #666; font-size: 0.9rem;">${train.To_Station}</div>
                        </div>
                    </div>
                    
                    <!-- Compartments Container (Inline) -->
                    <div id="seats-${train.Train_ID}" style="display: none; margin-top: 20px; padding-top: 20px; border-top: 1px dashed #ddd;"></div>
                </div>`;
            });
            
            html += '</div>';
            contentDiv.innerHTML = html;
        })
        .catch(err => {
            console.error('Fetch error:', err);
            document.getElementById('resultsContent').innerHTML = `<div style="padding: 15px; background: #f8d7da; color: #721c24; border-radius: 4px;">An error occurred while fetching trains. Please try again.</div>`;
        })
        .finally(() => {
            document.getElementById('searchBtn').textContent = 'Search Trains';
            document.getElementById('searchBtn').disabled = false;
        });
});

function viewRoute(trainId, trainName) {
    document.getElementById('modalTitle').textContent = `Route for ${trainName}`;
    document.getElementById('modalBody').innerHTML = '<div style="text-align: center; padding: 20px;"><i class="fa fa-spinner fa-spin fa-2x"></i> Loading route...</div>';
    document.getElementById('trainModal').style.display = 'block';
    
    fetch(`/tms/train-management-system/api/get_route.php?train_id=${trainId}`)
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                document.getElementById('modalBody').innerHTML = `<div style="color: red;">${data.error}</div>`;
                return;
            }
            
            let html = `<table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                <thead>
                    <tr style="background: #f4f4f4; text-align: left;">
                        <th style="padding: 10px; border-bottom: 2px solid #ddd;">#</th>
                        <th style="padding: 10px; border-bottom: 2px solid #ddd;">Station</th>
                        <th style="padding: 10px; border-bottom: 2px solid #ddd;">Arrival</th>
                        <th style="padding: 10px; border-bottom: 2px solid #ddd;">Departure</th>
                    </tr>
                </thead>
                <tbody>`;
                
            data.route.forEach((stop, index) => {
                html += `<tr>
                    <td style="padding: 10px; border-bottom: 1px solid #eee;">${stop.stop_number}</td>
                    <td style="padding: 10px; border-bottom: 1px solid #eee;"><strong>${stop.station_name}</strong> (${stop.station_code})</td>
                    <td style="padding: 10px; border-bottom: 1px solid #eee; color: #555;">${stop.arrival}</td>
                    <td style="padding: 10px; border-bottom: 1px solid #eee; color: #555;">${stop.departure}</td>
                </tr>`;
            });
            
            html += `</tbody></table>`;
            document.getElementById('modalBody').innerHTML = html;
        })
        .catch(err => {
            document.getElementById('modalBody').innerHTML = `<div style="color: red;">Error loading route details.</div>`;
        });
}

function viewSeats(classes, trainId, date) {
    const seatsContainer = document.getElementById(`seats-${trainId}`);
    
    if (seatsContainer.style.display === 'block') {
        seatsContainer.style.display = 'none';
        return;
    }
    
    let html = `<h4 style="margin-top: 0; color: #444; margin-bottom: 15px;">Compartment Availability</h4>
                <div style="display: flex; gap: 15px; flex-wrap: wrap;">`;
                
    classes.forEach(cls => {
        const availableColor = cls.available > 10 ? '#28a745' : (cls.available > 0 ? '#ffc107' : '#dc3545');
        const statusText = cls.available > 0 ? `AVL ${cls.available}` : 'WL / FULL';
        
        html += `
        <div style="border: 1px solid #c8d2e0; border-radius: 6px; padding: 15px; flex: 1; min-width: 120px; background: #f8fbff; text-align: center; cursor: pointer; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
            <div style="font-weight: bold; color: #004a99; font-size: 1.1rem; border-bottom: 1px solid #c8d2e0; padding-bottom: 8px; margin-bottom: 10px;">${cls.name} (${cls.code})</div>
            <div style="color: ${availableColor}; font-weight: bold; font-size: 1.2rem;">${statusText}</div>
            <div style="color: #666; margin-top: 5px; font-size: 1.1rem;">₹${cls.fare}</div>
            
            ${cls.available > 0 ? 
                `<a href="/tms/train-management-system/booking/book.php?train_id=${trainId}&date=${date}&class=${encodeURIComponent(cls.code)}" class="btn" style="margin-top: 15px; background: #004a99; padding: 8px; width: 100%; display: block; text-align: center; text-decoration: none; box-sizing: border-box; font-size: 0.9rem;">Book Now</a>` : 
                `<button disabled class="btn" style="margin-top: 15px; background: #ccc; cursor: not-allowed; padding: 8px; width: 100%; font-size: 0.9rem;">Not Available</button>`
            }
        </div>`;
    });
    
    html += `</div>`;
    seatsContainer.innerHTML = html;
    seatsContainer.style.display = 'block';
}

function closeModal() {
    document.getElementById('trainModal').style.display = 'none';
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    if (event.target == document.getElementById('trainModal')) {
        closeModal();
    }
}
</script>

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
