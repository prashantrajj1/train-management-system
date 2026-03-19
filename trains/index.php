<?php
require_once '../config/db.php';
include '../includes/header.php';

$isAdmin = (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'Admin');


// Fetch all trains
$stmt = $pdo->query("SELECT * FROM Train ORDER BY Train_ID DESC");
$trains = $stmt->fetchAll();
?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="color: var(--primary-color);">Available Trains</h2>
        <?php if ($isAdmin): ?>
        <a href="/tms/train-management-system/trains/add.php" class="btn-action btn-primary"><i class="fa fa-plus"></i> Add New Train</a>
        <?php endif; ?>
    </div>

    <?php if(isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
        <p style="color: green; background: #d4edda; padding: 10px; border-radius: 4px;">Train deleted successfully.</p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Train Name</th>
                <th>Train Type</th>
                <th>Total Seats</th>
                <th>Route</th>
                <?php if ($isAdmin): ?>
                <th>Actions</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach($trains as $t): ?>
            <tr>
                <td><strong><?php echo htmlspecialchars($t['Train_Name']); ?></strong><br><small class="text-muted"><?php echo htmlspecialchars($t['Train_ID']); ?></small></td>
                <td><?php echo htmlspecialchars($t['Train_Type']); ?></td>
                <td>
                    <button onclick="toggleSeats(<?php echo $t['Train_ID']; ?>)" style="background: none; border: 1px dashed var(--primary-color); color: var(--primary-color); padding: 5px 15px; border-radius: 20px; font-weight: bold; cursor: pointer; transition: 0.2s;" onmouseover="this.style.background='var(--primary-color)'; this.style.color='#fff';" onmouseout="this.style.background='none'; this.style.color='var(--primary-color)';">
                        <i class="fa fa-chair"></i> <?php echo htmlspecialchars($t['Total_Seats']); ?>
                    </button>
                </td>
                <td>
                    <button onclick="viewRoute(<?php echo $t['Train_ID']; ?>, '<?php echo addslashes(htmlspecialchars($t['Train_Name'])); ?>')" class="btn" style="background: #e0e0e0; color: #333; padding: 5px 10px; font-size: 0.8rem; margin-right: 5px;"><i class="fa fa-map-marker-alt"></i> View Route</button>
                </td>
                <?php if ($isAdmin): ?>
                <td>
                    <a href="/tms/train-management-system/trains/add.php?id=<?php echo $t['Train_ID']; ?>" class="btn-action btn-edit">Edit</a>
                    <a href="/tms/train-management-system/trains/delete.php?id=<?php echo $t['Train_ID']; ?>" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this train?');">Delete</a>
                </td>
                <?php endif; ?>
            </tr>
            <tr id="seats-row-<?php echo $t['Train_ID']; ?>" style="display: none; background: #fdfdfd;">
                <td colspan="<?php echo $isAdmin ? '5' : '4'; ?>" style="padding: 15px; border-top: 1px dashed #ccc;">
                    <div style="display: flex; gap: 15px; align-items: flex-end; margin-bottom: 15px;">
                        <div>
                            <label style="font-weight: bold; font-size: 0.9rem;">Select Travel Date:</label><br>
                            <input type="date" id="date-<?php echo $t['Train_ID']; ?>" class="form-control" value="<?php echo date('Y-m-d'); ?>" style="padding: 5px; width: 150px;">
                        </div>
                        <button onclick="fetchSeats(<?php echo $t['Train_ID']; ?>)" class="btn" style="padding: 6px 15px; font-size: 0.9rem;">Check Now</button>
                    </div>
                    <div id="seats-content-<?php echo $t['Train_ID']; ?>">
                        <!-- Seats loaded dynamically here -->
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($trains)): ?>
            <tr>
                <td colspan="<?php echo $isAdmin ? '5' : '4'; ?>" style="text-align: center;">No trains found in the system.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal for Route -->
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
            if (!data.route || data.route.length === 0) {
                document.getElementById('modalBody').innerHTML = `<div style="padding: 15px; background: #fff3cd; color: #856404; border-radius: 4px;">No stops mapped for this train yet.</div>`;
                return;
            }
            
            let html = `<table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
                <thead>
                    <tr style="background: #f4f4f4; text-align: left;">
                        <th style="padding: 10px; border-bottom: 2px solid #ddd;">#</th>
                        <th style="padding: 10px; border-bottom: 2px solid #ddd;">Station</th>
                        <th style="padding: 10px; border-bottom: 2px solid #ddd;">Arrival</th>
                        <th style="padding: 10px; border-bottom: 2px solid #ddd;">Departure</th>
                        <th style="padding: 10px; border-bottom: 2px solid #ddd;">Distance</th>
                    </tr>
                </thead>
                <tbody>`;
                
            data.route.forEach((stop, index) => {
                html += `<tr>
                    <td style="padding: 10px; border-bottom: 1px solid #eee;">${stop.stop_number}</td>
                    <td style="padding: 10px; border-bottom: 1px solid #eee;"><strong>${stop.station_name}</strong> (${stop.station_code})</td>
                    <td style="padding: 10px; border-bottom: 1px solid #eee; color: #555;">${stop.arrival}</td>
                    <td style="padding: 10px; border-bottom: 1px solid #eee; color: #555;">${stop.departure}</td>
                    <td style="padding: 10px; border-bottom: 1px solid #eee; font-weight: bold; color: #004a99;">${stop.distance_km} km</td>
                </tr>`;
            });
            
            html += `</tbody></table>`;
            document.getElementById('modalBody').innerHTML = html;
        })
        .catch(err => {
            document.getElementById('modalBody').innerHTML = `<div style="color: red;">Error loading route details.</div>`;
        });
}

function toggleSeats(trainId) {
    const row = document.getElementById(`seats-row-${trainId}`);
    if (row.style.display === 'none') {
        row.style.display = 'table-row';
        fetchSeats(trainId);
    } else {
        row.style.display = 'none';
    }
}

function fetchSeats(trainId) {
    const date = document.getElementById(`date-${trainId}`).value;
    const contentDiv = document.getElementById(`seats-content-${trainId}`);
    contentDiv.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Loading availability...';
    
    fetch(`/tms/train-management-system/api/check_seats.php?train_id=${trainId}&travel_date=${date}`)
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                contentDiv.innerHTML = `<span style="color: red;">${data.error}</span>`;
                return;
            }
            
            let html = `<div style="display: flex; gap: 15px; flex-wrap: wrap;">`;
            data.classes.forEach(cls => {
                const availableColor = cls.available > 10 ? '#28a745' : (cls.available > 0 ? '#ffc107' : '#dc3545');
                const statusText = cls.available > 0 ? `AVL ${cls.available}` : 'WL / FULL';
                
                html += `
                <div style="border: 1px solid #c8d2e0; border-radius: 6px; padding: 15px; flex: 1; min-width: 120px; background: #fff; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                    <div style="font-weight: bold; color: #004a99; font-size: 1.1rem; border-bottom: 1px solid #eee; padding-bottom: 8px; margin-bottom: 10px;">${cls.name} (${cls.code})</div>
                    <div style="color: ${availableColor}; font-weight: bold; font-size: 1.2rem;">${statusText}</div>
                    <div style="color: #666; margin-top: 5px; font-size: 1.1rem;">₹${cls.fare}</div>
                    
                    ${cls.available > 0 ? 
                        `<a href="/tms/train-management-system/booking/book.php?train_id=${trainId}&date=${date}&class=${encodeURIComponent(cls.code)}" class="btn" style="margin-top: 15px; background: #004a99; padding: 8px; width: 100%; display: block; text-align: center; text-decoration: none; box-sizing: border-box; font-size: 0.9rem;">Book</a>` : 
                        `<button disabled class="btn" style="margin-top: 15px; background: #ccc; cursor: not-allowed; padding: 8px; width: 100%; font-size: 0.9rem;">No Seats</button>`
                    }
                </div>`;
            });
            html += `</div>`;
            contentDiv.innerHTML = html;
        })
        .catch(err => {
            contentDiv.innerHTML = `<span style="color: red;">Failed to load.</span>`;
        });
}

function closeModal() {
    document.getElementById('trainModal').style.display = 'none';
}

window.onclick = function(event) {
    if (event.target == document.getElementById('trainModal')) {
        closeModal();
    }
}
</script>

<?php include '../includes/footer.php'; ?>
