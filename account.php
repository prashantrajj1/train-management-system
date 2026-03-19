<?php
require_once 'config/db.php';
include 'includes/header.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "login.php");
    exit;
}

$user_id  = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role     = $_SESSION['role'];

// Fetch all tickets for this user matched by User_ID stored in Passenger
$sql = "
    SELECT 
        t.Ticket_ID,
        p.Name        AS Passenger_Name,
        p.Age,
        p.Gender,
        p.Phone,
        tr.Train_Name,
        tr.Train_Type,
        t.Travel_Date,
        t.Class_Type,
        t.Status,
        t.Fare,
        COALESCE(r.Coach_Number, 'N/A') AS Coach,
        COALESCE(r.Seat_Number,  'N/A') AS Seat
    FROM Ticket t
    JOIN Passenger p  ON t.Passenger_ID = p.Passenger_ID
    JOIN Train    tr  ON t.Train_ID     = tr.Train_ID
    LEFT JOIN Reservation r ON t.Ticket_ID = r.Ticket_ID
    WHERE p.User_ID = ? AND t.Hidden_By_User = FALSE
    ORDER BY t.Travel_Date DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$tickets = $stmt->fetchAll();

$profile  = !empty($tickets) ? $tickets[0] : null;
$today    = date('Y-m-d');

// Split upcoming vs past
$upcoming = array_values(array_filter($tickets, fn($t) => $t['Travel_Date'] >= $today && $t['Status'] !== 'Cancelled'));
$past     = array_values(array_filter($tickets, fn($t) => $t['Travel_Date'] <  $today || $t['Status'] === 'Cancelled'));
?>

<div class="container" style="max-width: 900px; margin-left: auto; margin-right: auto;">
    
    <?php if (isset($_GET['msg'])): ?>
        <?php if ($_GET['msg'] == 'cancelled'): ?>
            <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 6px; margin-bottom: 20px; text-align: center; font-weight: 600;">
                <i class="fa fa-check-circle"></i> Ticket cancelled successfully.
            </div>
        <?php elseif ($_GET['msg'] == 'error'): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 20px; text-align: center; font-weight: 600;">
                <i class="fa fa-exclamation-triangle"></i> Cancellation failed or unauthorized.
            </div>
        <?php elseif ($_GET['msg'] == 'cleared'): ?>
            <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 6px; margin-bottom: 20px; text-align: center; font-weight: 600;">
                <i class="fa fa-check-circle"></i> Past journey history cleared successfully.
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Profile Card -->
    <div style="background: white; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,74,153,0.12);
                padding: 30px; margin-bottom: 28px; display: flex; align-items: center; gap: 24px; flex-wrap: wrap;">
        <div style="width: 78px; height: 78px; border-radius: 50%; background: #004a99;
                    display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i class="fa fa-user" style="font-size: 2.2rem; color: white;"></i>
        </div>
        <div style="flex: 1; min-width: 200px;">
            <h2 style="color: #004a99; margin-bottom: 8px;"><?php echo htmlspecialchars($username); ?></h2>
            <p style="color: #555; margin-bottom: 4px;">
                <i class="fa fa-id-badge"></i>&nbsp; Role: <strong><?php echo htmlspecialchars($role); ?></strong>
            </p>
            <?php if ($profile): ?>
            <p style="color: #555; margin-bottom: 4px;">
                <i class="fa fa-phone"></i>&nbsp; Phone: <strong><?php echo htmlspecialchars($profile['Phone']); ?></strong>
            </p>
            <p style="color: #555;">
                <i class="fa fa-user-circle"></i>&nbsp;
                Age: <strong><?php echo htmlspecialchars($profile['Age']); ?></strong>
                &nbsp;|&nbsp;
                Gender: <strong><?php echo htmlspecialchars($profile['Gender']); ?></strong>
            </p>
            <?php endif; ?>
        </div>
        <div>
            <span style="background: #d4edda; color: #155724; padding: 7px 18px;
                         border-radius: 20px; font-weight: 600; font-size: 0.85rem;">
                <i class="fa fa-circle" style="font-size: 0.45rem; vertical-align: middle;"></i>
                &nbsp;Active Session
            </span>
        </div>
    </div>

    <!-- Upcoming / Current Journeys -->
    <div style="background: white; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,74,153,0.12);
                padding: 30px; margin-bottom: 24px;">
        <h3 style="color: #004a99; margin-bottom: 20px;">
            <i class="fa fa-calendar-check"></i>&nbsp; Upcoming &amp; Current Journeys
            <span style="font-size: 0.85rem; font-weight: normal; color: #888; margin-left: 8px;">
                (<?php echo count($upcoming); ?>)
            </span>
        </h3>
        <?php if (empty($upcoming)): ?>
            <div style="text-align: center; padding: 30px; color: #aaa;">
                <i class="fa fa-train" style="font-size: 2.5rem; margin-bottom: 12px; display: block;"></i>
                No upcoming journeys.
                <br><br>
                <a href="/tms/index.php" class="btn"
                   style="width: auto; padding: 10px 28px; display: inline-block; text-decoration: none;">
                    Book a Train
                </a>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>#</th><th>Train</th><th>Date</th><th>Class</th>
                            <th>Coach / Seat</th><th>Fare</th><th>Status</th><th>Ticket</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($upcoming as $t):
                            $sc = match($t['Status']) {
                                'Confirmed' => ['#d4edda','#155724'],
                                'RAC'       => ['#fff3cd','#856404'],
                                default     => ['#e2e3e5','#383d41'],
                            };
                        ?>
                        <tr>
                            <td><?php echo $t['Ticket_ID']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($t['Train_Name']); ?></strong><br>
                                <small style="color:#888;"><?php echo htmlspecialchars($t['Train_Type']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($t['Travel_Date']); ?></td>
                            <td><?php echo htmlspecialchars($t['Class_Type']); ?></td>
                            <td><code><?php echo htmlspecialchars($t['Coach']); ?> / <?php echo htmlspecialchars($t['Seat']); ?></code></td>
                            <td><strong>₹<?php echo number_format($t['Fare'], 2); ?></strong></td>
                            <td>
                                <span style="background:<?php echo $sc[0]; ?>; color:<?php echo $sc[1]; ?>;
                                             padding: 4px 12px; border-radius: 20px; font-size:0.82rem; font-weight:600;">
                                    <?php echo htmlspecialchars($t['Status']); ?>
                                </span>
                            </td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                <a href="/tms/reports/ticket.php?id=<?php echo $t['Ticket_ID']; ?>"
                                   style="padding: 4px 10px; border-radius: 4px; background: #004a99;
                                          color: white; font-size: 0.8rem; text-decoration: none;">
                                    <i class="fa fa-eye"></i> View
                                </a>
                                <a href="/tms/cancel_ticket.php?id=<?php echo $t['Ticket_ID']; ?>"
                                   onclick="return confirm('Are you sure you want to cancel this ticket?');"
                                   style="padding: 4px 10px; border-radius: 4px; background: #dc3545;
                                          color: white; font-size: 0.8rem; text-decoration: none;">
                                    <i class="fa fa-times-circle"></i> Cancel
                                </a>
                            </div>
                        </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Past Journeys -->
    <div style="background: white; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,74,153,0.12); padding: 30px;">
        <h3 style="color: #555; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <i class="fa fa-history"></i>&nbsp; Past Journeys
                <span style="font-size: 0.85rem; font-weight: normal; color: #888; margin-left: 8px;">
                    (<?php echo count($past); ?>)
                </span>
            </div>
            <?php if (!empty($past)): ?>
            <a href="<?php echo BASE_URL; ?>clear_history.php" class="btn btn-action" style="background-color: #dc3545; color: white; padding: 6px 15px; font-size: 0.9rem; text-decoration: none; border-radius: 4px;" onclick="return confirm('Are you sure you want to clear your past journey history? This cannot be undone.');">
                <i class="fa fa-trash"></i> Clear History
            </a>
            <?php endif; ?>
        </h3>
        <?php if (empty($past)): ?>
            <p style="text-align: center; color: #aaa; padding: 20px 0;">No past journeys yet.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>#</th><th>Train</th><th>Date</th><th>Class</th>
                            <th>Coach / Seat</th><th>Fare</th><th>Status</th><th>Ticket</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($past as $t):
                            $sc = match($t['Status']) {
                                'Confirmed' => ['#d4edda','#155724'],
                                'Cancelled' => ['#f8d7da','#721c24'],
                                'RAC'       => ['#fff3cd','#856404'],
                                default     => ['#e2e3e5','#383d41'],
                            };
                        ?>
                        <tr style="opacity: 0.85;">
                            <td><?php echo $t['Ticket_ID']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($t['Train_Name']); ?></strong><br>
                                <small style="color:#888;"><?php echo htmlspecialchars($t['Train_Type']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($t['Travel_Date']); ?></td>
                            <td><?php echo htmlspecialchars($t['Class_Type']); ?></td>
                            <td><code><?php echo htmlspecialchars($t['Coach']); ?> / <?php echo htmlspecialchars($t['Seat']); ?></code></td>
                            <td><strong>₹<?php echo number_format($t['Fare'], 2); ?></strong></td>
                            <td>
                                <span style="background:<?php echo $sc[0]; ?>; color:<?php echo $sc[1]; ?>;
                                             padding: 4px 12px; border-radius: 20px; font-size:0.82rem; font-weight:600;">
                                    <?php echo htmlspecialchars($t['Status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="/tms/reports/ticket.php?id=<?php echo $t['Ticket_ID']; ?>"
                                   style="padding:4px 12px; border-radius:4px; background:#6c757d;
                                          color:white; font-size:0.82rem; text-decoration:none;">
                                    <i class="fa fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php include 'includes/footer.php'; ?>
