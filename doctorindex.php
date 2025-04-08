<?php
session_start();
require('connection.php');
if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

// Establish database connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch doctor's details including image
$uid = $_SESSION['uid'];
$query = "SELECT d.*, l.email, d.image1 
          FROM d_registration d 
          JOIN login l ON d.lid = l.lid 
          WHERE d.lid='$uid'";
$result = mysqli_query($con, $query);

if ($result) {
    $doctor = mysqli_fetch_assoc($result);
    $doctor_name = $doctor['name'] ?? 'Doctor';
    $doctor_image = $doctor['image1'] ?? '';
    $doctor_email = $doctor['email'] ?? '';
    $doctor_id = $doctor['did'] ?? '';
} else {
    echo "Error: " . mysqli_error($con);
    $doctor_name = 'Doctor';
}

// Fetch important dates
$events_query = "SELECT * FROM doctor_event
                WHERE did = '$doctor_id' 
                AND event_date >= CURDATE() 
                ORDER BY event_date 
                LIMIT 5";
$events_result = mysqli_query($con, $events_query);

// Handle event creation
if (isset($_POST['add_event'])) {
    $event_title = mysqli_real_escape_string($con, $_POST['event_title']);
    $event_date = mysqli_real_escape_string($con, $_POST['event_date']);
    $event_time = mysqli_real_escape_string($con, $_POST['event_time']);
    $event_ampm = mysqli_real_escape_string($con, $_POST['event_ampm']);
    $event_description = mysqli_real_escape_string($con, $_POST['event_description']);
    $event_color = mysqli_real_escape_string($con, $_POST['event_color']);
    
    // Convert 12-hour format to 24-hour format for database
    $time_parts = explode(':', $event_time);
    $hours = intval($time_parts[0]);
    $minutes = $time_parts[1];
    
    if ($event_ampm === 'PM' && $hours < 12) {
        $hours += 12;
    } else if ($event_ampm === 'AM' && $hours === 12) {
        $hours = 0;
    }
    
    $time_24h = sprintf("%02d:%02d:00", $hours, intval($minutes));
    
    // Validate time range (5:00 AM to 11:59 PM)
    $valid_time = true;
    if ($event_ampm === 'AM' && ($hours < 5 || $hours === 12)) {
        $valid_time = false;
    }
    
    if ($valid_time) {
        $insert_event = "INSERT INTO doctor_event (did, event_title, event_date, event_time, event_description, event_color, created_at) 
                        VALUES ('$doctor_id', '$event_title', '$event_date', '$time_24h', '$event_description', '$event_color', NOW())";
        
        if (mysqli_query($con, $insert_event)) {
            $success_message = "Event added successfully!";
            // Refresh the page to show the new event
            header("Location: doctorindex.php?success=event_added");
            exit();
        } else {
            $error_message = "Error adding event: " . mysqli_error($con);
        }
    } else {
        $error_message = "Please select a time between 5:00 AM and 11:59 PM";
    }
}


mysqli_close($con);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
            --accent-color: #3498db;
            --text-light: #ecf0f1;
            --hover-color: #2980b9;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --info-color: #3498db;
            --light-bg: #f5f6fa;
            --card-bg: #ffffff;
            --border-color: #e0e0e0;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --border-radius: 10px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--light-bg);
            display: flex;
            color: #333;
        }

        .sidebar {
            background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
            width: 280px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 15px rgba(0,0,0,0.1);
        }

        .doctor-info {
            text-align: center;
            padding: 30px 20px;
            background: rgba(255,255,255,0.05);
            position: relative;
        }

        .doctor-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 0 auto 15px;
            border: 3px solid var(--accent-color);
            overflow: hidden;
            background: #fff;
        }

        .doctor-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .doctor-avatar i {
            font-size: 40px;
            color: var(--accent-color);
            line-height: 100px;
        }

        .doctor-name {
            color: var(--text-light);
            font-size: 24px;
            margin: 10px 0 5px;
            font-weight: 500;
        }

        .doctor-email {
            color: var(--accent-color);
            font-size: 14px;
            margin: 0;
        }

        .nav-links {
            padding: 20px 0;
            width: 100%;
        }

        .nav-item {
            padding: 15px 25px;
            display: flex;
            align-items: center;
            color: var(--text-light);
            text-decoration: none;
            transition: all 0.3s;
            margin: 5px 15px;
            border-radius: 10px;
        }

        .nav-item i {
            margin-right: 15px;
            font-size: 20px;
            width: 25px;
            text-align: center;
        }

        .nav-item:hover, .nav-item.active {
            background: var(--accent-color);
            color: white;
        }

        .nav-item.active {
            box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3);
        }

        .content {
            margin-left: 280px;
            padding: 0;
            width: calc(100% - 280px);
        }

        .sidebar-footer {
            margin-top: auto;
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-logout-btn {
            display: flex;
            align-items: center;
            padding: 15px 25px;
            color: var(--text-light);
            text-decoration: none;
            transition: all 0.3s;
            margin: 5px 15px;
            border-radius: 10px;
            background: transparent;
        }

        .sidebar-logout-btn i {
            margin-right: 15px;
            font-size: 20px;
            width: 25px;
            text-align: center;
        }

        .sidebar-logout-btn:hover {
            background: var(--accent-color);
            color: white;
            box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3);
        }

        .main-content {
            padding: 30px;
            background-color: var(--light-bg);
            min-height: 100vh;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .dashboard-title {
            font-size: 28px;
            font-weight: 600;
            color: var(--primary-color);
        }

        .dashboard-date {
            font-size: 16px;
            color: var(--secondary-color);
            display: flex;
            align-items: center;
        }

        .dashboard-date i {
            margin-right: 8px;
            color: var(--accent-color);
        }

        .calendar-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 30px;
            overflow: hidden;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: var(--primary-color);
            color: var(--text-light);
        }

        .card-header h2 {
            font-size: 18px;
            font-weight: 500;
            display: flex;
            align-items: center;
        }

        .card-header h2 i {
            margin-right: 10px;
        }

        .add-event-btn {
            background: var(--accent-color);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            font-size: 14px;
            transition: all 0.3s;
        }

        .add-event-btn i {
            margin-right: 5px;
        }

        .add-event-btn:hover {
            background: var(--hover-color);
        }

        .card-body {
            padding: 20px;
        }

        #calendar {
            height: 600px;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: var(--card-bg);
            margin: 10% auto;
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            width: 500px;
            max-width: 90%;
            position: relative;
        }

        .close {
            position: absolute;
            right: 20px;
            top: 15px;
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: var(--danger-color);
        }

        .modal-header {
            margin-bottom: 20px;
        }

        .modal-title {
            font-size: 24px;
            color: var(--primary-color);
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--secondary-color);
        }

        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            transition: border 0.3s;
        }

        .form-group input:focus, .form-group textarea:focus {
            border-color: var(--accent-color);
            outline: none;
        }

        .color-options {
            display: flex;
            gap: 10px;
        }

        .color-option {
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        .color-option input {
            display: none;
        }

        .color-circle {
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
            border: 2px solid transparent;
            transition: all 0.3s;
        }

        .color-option input:checked + .color-circle {
            transform: scale(1.2);
            border-color: var(--secondary-color);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 30px;
        }

        .btn-primary, .btn-secondary {
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
            border: none;
        }

        .btn-primary {
            background: var(--accent-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--hover-color);
        }

        .btn-secondary {
            background: #e0e0e0;
            color: var(--secondary-color);
        }

        .btn-secondary:hover {
            background: #d0d0d0;
        }

        .alert {
            padding: 15px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .alert i {
            margin-right: 10px;
            font-size: 20px;
        }

        .alert-success {
            background-color: rgba(46, 204, 113, 0.2);
            color: var(--success-color);
            border: 1px solid var(--success-color);
        }

        .alert-danger {
            background-color: rgba(231, 76, 60, 0.2);
            color: var(--danger-color);
            border: 1px solid var(--danger-color);
        }

        @media (max-width: 992px) {
            .sidebar {
                width: 80px;
                overflow: hidden;
            }
            
            .doctor-info {
                padding: 15px 10px;
            }
            
            .doctor-avatar {
                width: 50px;
                height: 50px;
            }
            
            .doctor-name, .doctor-email {
                display: none;
            }
            
            .nav-item {
                padding: 15px;
                margin: 5px;
                justify-content: center;
            }
            
            .nav-item i {
                margin-right: 0;
            }
            
            .nav-item span {
                display: none;
            }
            
            .content {
                margin-left: 80px;
                width: calc(100% - 80px);
            }
            
            .sidebar-logout-btn {
                padding: 15px;
                margin: 5px;
                justify-content: center;
            }
            
            .sidebar-logout-btn i {
                margin-right: 0;
            }
            
            .sidebar-logout-btn span {
                display: none;
            }
        }

        .availability-list {
            max-height: 500px;
            overflow-y: auto;
            padding: 10px;
        }

        .availability-item {
            padding: 15px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid;
            transition: all 0.3s;
        }

        .availability-item:hover {
            transform: translateX(5px);
            box-shadow: var(--shadow);
        }

        .event-info h3 {
            margin: 0 0 5px 0;
            color: var(--primary-color);
            font-size: 16px;
        }

        .event-datetime {
            color: var(--secondary-color);
            margin: 0;
            font-size: 14px;
        }

        .event-description {
            margin: 10px 0 0 0;
            color: var(--secondary-color);
            font-size: 14px;
            line-height: 1.4;
        }

        .event-datetime i {
            margin-right: 5px;
            margin-left: 10px;
        }

        .event-datetime i:first-child {
            margin-left: 0;
        }

        .no-events {
            text-align: center;
            color: var(--secondary-color);
            padding: 20px;
            font-style: italic;
        }
        .header-buttons {
    display: flex;
    gap: 10px;
}

.availability-btn {
    background: var(--success-color);
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 5px;
    cursor: pointer;
    display: flex;
    align-items: center;
    font-size: 14px;
    transition: all 0.3s;
}

.availability-btn i {
    margin-right: 5px;
}

.availability-btn:hover {
    background: #27ae60;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(46, 204, 113, 0.2);
}

.availability-btn:active {
    transform: translateY(0);
}
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="doctor-info">
            <div class="doctor-avatar">
                <?php if (!empty($doctor_image) && file_exists("uploads/" . $doctor_image)): ?>
                    <img src="uploads/<?php echo htmlspecialchars($doctor_image); ?>" alt="Doctor Profile">
                <?php else: ?>
                    <i class="fas fa-user-md"></i>
                <?php endif; ?>
            </div>
            <h1 class="doctor-name"><?php echo htmlspecialchars($doctor_name); ?></h1>
            <p class="doctor-email"><?php echo htmlspecialchars($doctor_email); ?></p>
        </div>

        <div class="nav-links">
            <a href="doctorindex.php" class="nav-item active">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            
            <a href="view_profile_doctor.php" class="nav-item">
                <i class="fas fa-user"></i>
                <span>View Profile</span>
            </a>

            <a href="view_chat_message.php" class="nav-item">
                <i class="fas fa-comments"></i>
                <span>Messages</span>
            </a>
            
            <a href="upload_pet_video.php" class="nav-item">
                <i class="fas fa-video"></i>
                <span>Video Classes</span>
            </a>
        </div>

        <div class="sidebar-footer">
            <a href="logout.php" class="sidebar-logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </div>

    <div class="content">
        <div class="main-content">
            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <h1 class="dashboard-title">Welcome, Dr. <?php echo htmlspecialchars($doctor_name); ?></h1>
                <div class="dashboard-date">
                    <i class="far fa-calendar-alt"></i> <?php echo date('l, F j, Y'); ?>
                </div>
            </div>
            
            <?php if (isset($_GET['success']) && $_GET['success'] == 'event_added'): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <div>Event added successfully!</div>
            </div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <div><?php echo $error_message; ?></div>
            </div>
            <?php endif; ?>
            
            <!-- Calendar Section -->
            <div class="calendar-card">
                <div class="card-header">
                    <h2><i class="fas fa-calendar-alt"></i> Calendar</h2>
                    <div class="header-buttons">
                        <button class="availability-btn" id="openAvailabilityModal">
                            <i class="fas fa-clock"></i> Schedule Details
                        </button>
                        <button class="add-event-btn" id="openEventModal">
                            <i class="fas fa-plus"></i> Add Event
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Modal -->
    <div id="eventModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div class="modal-header">
                <h2 class="modal-title">Add New Event</h2>
            </div>
            <form method="post" action="">
                <div class="form-group">
                    <label for="event_title">Event Title</label>
                    <input type="text" id="event_title" name="event_title" required>
                </div>
                
                <div class="form-group">
                    <label for="event_date">Event Date</label>
                    <input type="date" id="event_date" name="event_date" required>
                </div>
                
                <div class="form-group">
                    <label for="event_time">Event Time</label>
                    <div class="time-input-wrapper">
                        <input type="text" id="event_time" name="event_time" required pattern="(0[1-9]|1[0-2]):[0-5][0-9]" placeholder="HH:MM">
                        <select name="event_ampm" id="event_ampm" required>
                            <option value="AM">AM</option>
                            <option value="PM">PM</option>
                        </select>
                    </div>
                    <small class="time-hint">Please select a time between 5:00 AM and 11:59 PM</small>
                </div>
                
                <div class="form-group">
                    <label for="event_description">Description</label>
                    <textarea id="event_description" name="event_description" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Event Color</label>
                    <div class="color-options">
                        <label class="color-option">
                            <input type="radio" name="event_color" value="#3498db" checked>
                            <span class="color-circle" style="background-color: #3498db;"></span>
                        </label>
                        <label class="color-option">
                            <input type="radio" name="event_color" value="#2ecc71">
                            <span class="color-circle" style="background-color: #2ecc71;"></span>
                        </label>
                        <label class="color-option">
                            <input type="radio" name="event_color" value="#e74c3c">
                            <span class="color-circle" style="background-color: #e74c3c;"></span>
                        </label>
                        <label class="color-option">
                            <input type="radio" name="event_color" value="#f39c12">
                            <span class="color-circle" style="background-color: #f39c12;"></span>
                        </label>
                        <label class="color-option">
                            <input type="radio" name="event_color" value="#9b59b6">
                            <span class="color-circle" style="background-color: #9b59b6;"></span>
                        </label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-secondary" id="cancelEvent">Cancel</button>
                    <button type="submit" class="btn-primary" name="add_event">Add Event</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Availability Modal -->
    <div id="availabilityModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeAvailability">&times;</span>
            <div class="modal-header">
                <h2 class="modal-title">
                    <i class="fas fa-clock"></i> My Availability Schedule
                </h2>
            </div>
            <div class="availability-list">
                <?php
                // Reconnect to database since it was closed earlier
                require('connection.php');
                
                // Fetch all events
                $all_events_query = "SELECT event_title, event_date, event_time, event_description, event_color 
                                   FROM doctor_event 
                                   WHERE did = '$doctor_id' 
                                   ORDER BY event_date, event_time";
                $all_events_result = mysqli_query($con, $all_events_query);
                
                if (mysqli_num_rows($all_events_result) > 0) {
                    while ($event = mysqli_fetch_assoc($all_events_result)) {
                        $event_date = date('F j, Y', strtotime($event['event_date']));
                        $event_time = date('h:i A', strtotime($event['event_time']));
                        ?>
                        <div class="availability-item" style="border-left-color: <?php echo $event['event_color']; ?>">
                            <div class="event-info">
                                <h3><?php echo htmlspecialchars($event['event_title']); ?></h3>
                                <p class="event-datetime">
                                    <i class="far fa-calendar"></i> <?php echo $event_date; ?>
                                    <i class="far fa-clock"></i> <?php echo $event_time; ?>
                                </p>
                                <?php if (!empty($event['event_description'])): ?>
                                    <p class="event-description"><?php echo htmlspecialchars($event['event_description']); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo '<p class="no-events">No events scheduled</p>';
                }
                mysqli_close($con);
                ?>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize calendar
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: [
                    <?php 
                    // Reset pointer to beginning of result set
                    if ($events_result) {
                        mysqli_data_seek($events_result, 0);
                        while ($event = mysqli_fetch_assoc($events_result)) {
                            echo "{";
                            echo "title: '" . addslashes($event['event_title']) . "',";
                            echo "start: '" . $event['event_date'] . "T" . $event['event_time'] . "',";
                            echo "backgroundColor: '" . $event['event_color'] . "',";
                            echo "borderColor: '" . $event['event_color'] . "'";
                            echo "},";
                        }
                    }
                    ?>
                ],
                eventClick: function(info) {
                    alert('Event: ' + info.event.title);
                },
                dateClick: function(info) {
                    document.getElementById('event_date').value = info.dateStr;
                    document.getElementById('eventModal').style.display = 'block';
                }
            });
            calendar.render();
            
            // Modal functionality
            var modal = document.getElementById('eventModal');
            var openBtn = document.getElementById('openEventModal');
            var closeBtn = document.getElementsByClassName('close')[0];
            var cancelBtn = document.getElementById('cancelEvent');
            
            openBtn.onclick = function() {
                modal.style.display = 'block';
            }
            
            closeBtn.onclick = function() {
                modal.style.display = 'none';
            }
            
            cancelBtn.onclick = function() {
                modal.style.display = 'none';
            }
            
            // Initialize date picker
            flatpickr("#event_date", {
                minDate: "today",
                dateFormat: "Y-m-d"
            });

            flatpickr("#event_time", {
                enableTime: true,
                noCalendar: true,
                dateFormat: "h:i",
                time_24hr: false,
                minuteIncrement: 1,
                minTime: "05:00",
                maxTime: "23:59",
                onChange: function(selectedDates, dateStr, instance) {
                    // Extract hours from the selected time
                    const timeParts = dateStr.split(':');
                    const hours = parseInt(timeParts[0]);
                    
                    // Update AM/PM select based on time
                    const ampmSelect = document.getElementById('event_ampm');
                    if (hours >= 12) {
                        ampmSelect.value = 'PM';
                    } else {
                        ampmSelect.value = 'AM';
                    }
                }
            });

            // Add form validation
            document.querySelector('form').addEventListener('submit', function(e) {
                const timeInput = document.getElementById('event_time');
                const ampmSelect = document.getElementById('event_ampm');
                const timeParts = timeInput.value.split(':');
                const hours = parseInt(timeParts[0]);
                
                if (ampmSelect.value === 'AM' && (hours < 5 || hours === 12)) {
                    e.preventDefault();
                    alert('Please select a time between 5:00 AM and 11:59 PM');
                }
            });

            // Availability Modal functionality
            var availabilityModal = document.getElementById('availabilityModal');
            var openAvailabilityBtn = document.getElementById('openAvailabilityModal');
            var closeAvailabilityBtn = document.getElementById('closeAvailability');

            openAvailabilityBtn.onclick = function() {
                availabilityModal.style.display = 'block';
            }

            closeAvailabilityBtn.onclick = function() {
                availabilityModal.style.display = 'none';
            }

            // Update the window click handler to include both modals
            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
                if (event.target == availabilityModal) {
                    availabilityModal.style.display = 'none';
                }
            }
        });
    </script>
</body>
</html>
