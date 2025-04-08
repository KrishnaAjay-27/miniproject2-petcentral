<?php
session_start();
require('connection.php');
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

// Fetch vaccination center details
$uid = $_SESSION['uid'];
$query = "SELECT vc.*, l.email 
          FROM vaccination_centers vc 
          JOIN login l ON vc.lid = l.lid 
          WHERE vc.lid = '$uid'";
$result = mysqli_query($con, $query);

if ($result) {
    $center = mysqli_fetch_assoc($result);
    $center_name = $center['center_name'] ?? 'Vaccination Center';
    $center_email = $center['email'] ?? '';
    $center_status = $center['status'] ?? 0;
    $center_id = $center['center_id'];

    // Fetch total vaccinations (completed appointments)
    $total_query = "SELECT COUNT(*) as total 
                    FROM vaccination_bookings 
                    WHERE center_id = '$center_id' 
                    AND booking_status = 'completed'";
    $total_result = mysqli_query($con, $total_query);
    $total_vaccinations = mysqli_fetch_assoc($total_result)['total'];

    // Fetch today's appointments
    $today_query = "SELECT COUNT(*) as today 
                   FROM vaccination_bookings 
                   WHERE center_id = '$center_id' 
                   AND appointment_date = CURDATE() 
                   AND booking_status IN ('pending', 'approved')";
    $today_result = mysqli_query($con, $today_query);
    $today_appointments = mysqli_fetch_assoc($today_result)['today'];

    // Fetch pending appointments
    $pending_query = "SELECT COUNT(*) as pending 
                     FROM vaccination_bookings 
                     WHERE center_id = '$center_id' 
                     AND booking_status = 'pending'";
    $pending_result = mysqli_query($con, $pending_query);
    $pending_appointments = mysqli_fetch_assoc($pending_result)['pending'];

    // Fetch recent appointments
    $recent_query = "SELECT 
                        vb.booking_id,
                        vb.appointment_date,
                        vb.booking_status,
                        vb.pet_name,
                        l.name as user_name,
                        vd.vaccination_name,
                        vs.start_time
                    FROM vaccination_bookings vb
                    JOIN registration l ON vb.lid = l.lid
                    JOIN vaccination_details vd ON vb.vaccination_id = vd.vaccination_id
                    JOIN vaccination_slots vs ON vb.id = vs.id
                    WHERE vb.center_id = '$center_id'
                    ORDER BY vb.appointment_date DESC, vs.start_time DESC
                    LIMIT 5";
    $recent_result = mysqli_query($con, $recent_query);

} else {
    echo "Error: " . mysqli_error($con);
    $center_name = 'Vaccination Center';
}

mysqli_close($con);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vaccination Center Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background: #343a40;
            padding-top: 20px;
            overflow-y: auto;
            z-index: 100;
        }
        
        .sidebar .center-info {
            padding: 20px;
            color: #fff;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar .nav-link {
            padding: 12px 20px;
            color: #ced4da;
            display: flex;
            align-items: center;
            transition: 0.3s;
        }
        
        .sidebar .nav-link:hover {
            color: #fff;
            background: rgba(255,255,255,0.1);
        }
        
        .sidebar .nav-link i {
            width: 25px;
            margin-right: 10px;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        
        .status-card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .status-card:hover {
            transform: translateY(-5px);
        }
        
        .status-card i {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }
        
        .header {
            background: #fff;
            padding: 15px 25px;
            margin-bottom: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .center-status,
        .status-active,
        .status-pending {
            display: none;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="center-info">
            <h5 class="mb-2"><?php echo htmlspecialchars($center_name); ?></h5>
            <p class="mb-2"><?php echo htmlspecialchars($center_email); ?></p>
        </div>
        <div class="nav flex-column">
            <a href="vaccination_center_index.php" class="nav-link">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="add_vaccination_details.php" class="nav-link">
            <i class="fas fa-syringe"></i>Add Vaccination Details
            </a>
            <a href="vaccination_history.php" class="nav-link">
            <i class="fas fa-syringe"></i>Vaccination History
            </a>
            <a href="center_profile.php" class="nav-link">
                <i class="fas fa-user-circle"></i> Center Profile
            </a>
            <a href="center_schedule.php" class="nav-link">
                <i class="fas fa-clock"></i> Manage Schedule
            </a>
            <a href="logout.php" class="nav-link text-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="header">
            <h4 class="mb-0">Welcome, <?php echo htmlspecialchars($center_name); ?></h4>
        </div>

        <div class="row">
          
            <div class="col-md-4">
                <div class="status-card text-center">
                    <i class="fas fa-calendar-day text-success"></i>
                    <h3 class="mb-2"><?php echo number_format($today_appointments); ?></h3>
                    <p class="text-muted mb-0">Today's Appointments</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="status-card text-center">
                    <i class="fas fa-hourglass-half text-warning"></i>
                    <h3 class="mb-2"><?php echo number_format($pending_appointments); ?></h3>
                    <p class="text-muted mb-0">Pending Appointments</p>
                </div>
            </div>
        </div>

        <!-- Recent Appointments Section -->
        <div class="card mt-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-calendar-check mr-2"></i>Recent Appointments</h5>
            </div>
            <div class="card-body">
                <?php if(mysqli_num_rows($recent_result) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Patient</th>
                                    <th>Pet Name</th>
                                    <th>Vaccination</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($appointment = mysqli_fetch_assoc($recent_result)): ?>
                                    <tr>
                                        <td>#<?php echo $appointment['booking_id']; ?></td>
                                        <td><?php echo date('d M Y', strtotime($appointment['appointment_date'])); ?></td>
                                        <td><?php echo date('h:i A', strtotime($appointment['start_time'])); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['user_name']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['pet_name']); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['vaccination_name']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php 
                                                echo ($appointment['booking_status'] == 'completed') ? 'success' : 
                                                     (($appointment['booking_status'] == 'pending') ? 'warning' : 
                                                     (($appointment['booking_status'] == 'cancelled_by_clinic') ? 'danger' : 'info')); 
                                            ?>">
                                                <?php echo ucfirst($appointment['booking_status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center text-muted">No recent appointments found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>