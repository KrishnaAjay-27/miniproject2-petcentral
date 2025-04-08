<?php
session_start();
require('connection.php');
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}


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
} else {
    echo "Error: " . mysqli_error($con);
    $center_name = 'Vaccination Center';
}



// Fetch appointments for this vaccination center
$query = "SELECT 
    vb.booking_id,
    vb.appointment_date,
    vb.booking_status,
    vb.pet_name,
    vb.pet_age,
    l.name as user_name,
    l.phone as user_phone,
    l.email as user_email,
    vd.vaccination_name,
    vs.start_time,
    vs.end_time
FROM vaccination_bookings vb
JOIN registration l ON vb.lid = l.lid
JOIN vaccination_details vd ON vb.vaccination_id = vd.vaccination_id
JOIN vaccination_slots vs ON vb.id = vs.id
WHERE vb.center_id = (SELECT center_id FROM vaccination_centers WHERE lid = '$uid')
ORDER BY vb.appointment_date DESC, vs.start_time ASC";

$result = mysqli_query($con, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Center Schedule</title>
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
        
        
        .btn-yellow {
            background-color: var(--theme-yellow);
            border-color: var(--theme-yellow);
            color: #333;
        }
        
        .btn-yellow:hover {
            background-color: var(--theme-yellow-hover);
            border-color: var(--theme-yellow-hover);
            color: #333;
        }
        
        .status-pending { color: #ffc107; }
        .status-approved { color: #28a745; }
        .status-completed { color: #17a2b8; }
        .status-cancelled { color: #dc3545; }
        
        .appointment-card {
            transition: transform 0.3s;
            margin-bottom: 20px;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .appointment-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
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

        <!-- Add this new section for report buttons -->
        <div class="card mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-file-pdf mr-2"></i>Download Reports</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <button onclick="window.location.href='generate_report.php?type=approved'" class="btn btn-yellow btn-block">
                            <i class="fas fa-check-circle mr-2"></i>Approved Bookings Report
                        </button>
                    </div>
                    <div class="col-md-4 mb-2">
                        <button onclick="window.location.href='generate_report.php?type=cancelled'" class="btn btn-danger btn-block">
                            <i class="fas fa-times-circle mr-2"></i>Cancelled Bookings Report
                        </button>
                    </div>
                    <div class="col-md-4 mb-2">
                        <button onclick="window.location.href='generate_report.php?type=cancelled_by_clinic'" class="btn btn-warning btn-block">
                            <i class="fas fa-hospital-alt mr-2"></i>Clinic Cancelled Report
                        </button>
                    </div>
                    <div class="col-md-6 mb-2">
                        <button onclick="window.location.href='generate_report.php?type=current_month'" class="btn btn-info btn-block">
                            <i class="fas fa-calendar-alt mr-2"></i>Current Month Report
                        </button>
                    </div>
                    <div class="col-md-6 mb-2">
                        <button onclick="window.location.href='generate_report.php?type=all_monthly'" class="btn btn-primary btn-block">
                            <i class="fas fa-calendar mr-2"></i>All Bookings (Monthly)
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        
        <h2 class="mb-4"><i class="fas fa-calendar-alt mr-2"></i>Appointment Schedule</h2>
        
        <div class="row">
            <?php while($appointment = mysqli_fetch_assoc($result)): ?>
                <div class="col-md-6">
                    <div class="card appointment-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Booking <?php echo $appointment['booking_id']; ?></h5>
                            <span class="status-<?php echo strtolower($appointment['booking_status']); ?>">
                                <i class="fas fa-circle mr-1"></i>
                                <?php echo ucfirst($appointment['booking_status']); ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <p><strong><i class="fas fa-calendar-day mr-2"></i>Date:</strong> 
                                <?php echo date('d M Y', strtotime($appointment['appointment_date'])); ?>
                            </p>
                            <p><strong><i class="fas fa-clock mr-2"></i>Time:</strong> 
                                <?php echo date('h:i A', strtotime($appointment['start_time'])) . ' - ' . 
                                         date('h:i A', strtotime($appointment['end_time'])); ?>
                            </p>
                            <p><strong><i class="fas fa-user mr-2"></i>Patient:</strong> 
                                <?php echo htmlspecialchars($appointment['user_name']); ?>
                            </p>
                            <p><strong><i class="fas fa-phone mr-2"></i>Contact:</strong> 
                                <?php echo htmlspecialchars($appointment['user_phone']); ?>
                            </p>
                            <p><strong><i class="fas fa-envelope mr-2"></i>Email:</strong> 
                                <?php echo htmlspecialchars($appointment['user_email']); ?>
                            </p>
                            <p><strong><i class="fas fa-paw mr-2"></i>Pet Name:</strong> 
                                <?php echo htmlspecialchars($appointment['pet_name']); ?> 
                                (Age: <?php echo htmlspecialchars($appointment['pet_age']); ?>)
                            </p>
                            <p><strong><i class="fas fa-syringe mr-2"></i>Vaccination:</strong> 
                                <?php echo htmlspecialchars($appointment['vaccination_name']); ?>
                            </p>
                            
                            <?php if($appointment['booking_status'] == 'pending'): ?>
                                <div class="mt-3">
                                    <button class="btn btn-yellow btn-sm" 
                                            onclick="updateStatus(<?php echo $appointment['booking_id']; ?>, 'approved')">
                                        <i class="fas fa-check mr-1"></i>Approve
                                    </button>
                                    <button class="btn btn-danger btn-sm" 
                                            onclick="updateStatus(<?php echo $appointment['booking_id']; ?>, 'cancelled_by_clinic')">
                                        <i class="fas fa-times mr-1"></i>Cancel
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
    function updateStatus(bookingId, status) {
    let statusText = status === 'approved' ? 'approve' : 'cancel';
    if(confirm('Are you sure you want to ' + statusText + ' this appointment?')) {
        $.ajax({
            url: 'update_appointment_status.php',
            type: 'POST',
            dataType: 'json',
            data: {
                booking_id: bookingId,
                status: status
            },
            success: function(response) {
                if(response && response.success) {
                    location.reload(); // Just reload without alert
                } else {
                    alert(response?.message || 'Error updating status');
                }
            },
            error: function(xhr, status, error) {
                console.error('Ajax Error:', error);
                console.log('Response:', xhr.responseText); // Add this for debugging
                location.reload(); // Reload anyway since the update probably worked
            }
        });
    }
}
    </script>
</body>
</html>