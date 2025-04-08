<?php
include("header.php");
require('connection.php');

if (!isset($_SESSION['uid']) || !isset($_GET['district_id'])) {
    echo "<script>window.location.href='index.php';</script>";
    exit();
}

$district_id = mysqli_real_escape_string($con, $_GET['district_id']);
$userid = $_SESSION['uid'];

// Fetch centers in the selected district with all details
$centers_query = "SELECT vc.center_id, vc.center_name, vc.head_name, 
                        vc.email, vc.contact_number, vc.address, 
                        vc.center_image, vc.status, vc.district_id,
                        d.district_name 
                 FROM vaccination_centers vc
                 JOIN districts d ON vc.district_id = d.district_id
                 WHERE vc.district_id = '$district_id' 
                 AND vc.status = 1
                 ORDER BY vc.center_name";

$centers_result = mysqli_query($con, $centers_query);

// Fetch public holidays
$holidays = array(
    '2024-01-26', // Republic Day
    '2024-08-15', // Independence Day
    // Add more holidays as needed
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vaccination Centers</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <style>
         :root {
        --theme-yellow: #FFD700;
        --theme-yellow-dark: #FFC700;
        --theme-yellow-hover: #FFB700;
    }

    .center-card {
        transition: transform 0.3s;
        border: none;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .center-card:hover {
        transform: translateY(-5px);
    }
    .center-image {
        height: 200px;
        object-fit: cover;
    }
    .btn-yellow {
        background-color: var(--theme-yellow);
        border-color: var(--theme-yellow);
        color: #333;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    .btn-yellow:hover {
        background-color: var(--theme-yellow-hover);
        border-color: var(--theme-yellow-hover);
        color: #333;
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }
    .btn-outline-yellow {
        color: #333;
        border-color: var(--theme-yellow);
        background-color: transparent;
    }
    .btn-outline-yellow:hover {
        background-color: var(--theme-yellow);
        color: #333;
    }
    .modal-header {
        background-color: var(--theme-yellow) !important;
        border-bottom: 0;
        color: #333 !important;
    }
    .form-control:focus {
        border-color: var(--theme-yellow);
        box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.25);
    }
    .custom-control-input:checked ~ .custom-control-label::before {
        border-color: var(--theme-yellow);
        background-color: var(--theme-yellow);
    }
        
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Vaccination Centers in <?php 
                $district = mysqli_fetch_assoc(mysqli_query($con, "SELECT district_name FROM districts WHERE district_id = '$district_id'"));
                echo htmlspecialchars($district['district_name']); 
            ?></h3>
           <a href="book_appointments.php" class="btn btn-outline-yellow">
    <i class="fas fa-arrow-left mr-2"></i>Back to Districts
</a>
        </div>

        <div class="row">
            <?php while($center = mysqli_fetch_assoc($centers_result)) { ?>
                <div class="col-md-6 mb-4">
                    <div class="card center-card">
                        <img src="uploads/<?php echo htmlspecialchars($center['center_image'] ?: 'default.jpg'); ?>" 
                             class="card-img-top center-image" 
                             alt="<?php echo htmlspecialchars($center['center_name']); ?>"
                             onerror="this.src='uploads/center_images/default.jpg'">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($center['center_name']); ?></h5>
                            <p class="card-text">
                                <i class="fas fa-user mr-2"></i><?php echo htmlspecialchars($center['head_name']); ?><br>
                                <i class="fas fa-envelope mr-2"></i><?php echo htmlspecialchars($center['email']); ?><br>
                                <i class="fas fa-phone mr-2"></i><?php echo htmlspecialchars($center['contact_number']); ?><br>
                                <i class="fas fa-map-marker-alt mr-2"></i><?php echo htmlspecialchars($center['address']); ?>
                            </p>
                            <button class="btn btn-yellow btn-block" 
                                    onclick="showBookingModal(<?php echo $center['center_id']; ?>)">
                                <i class="fas fa-calendar-plus mr-2"></i>Book Appointment
                            </button>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>

    <!-- Booking Modal -->
    <div class="modal fade" id="bookingModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-calendar-plus mr-2"></i>Book Vaccination Appointment
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="bookingForm" method="POST" action="process_booking.php">
                        <input type="hidden" id="center_id" name="center_id">
                        
                        <div class="row">
                            <!-- Vaccination Selection -->
                            <div class="col-md-12 mb-3">
                                <label class="font-weight-bold">
                                    <i class="fas fa-syringe mr-2"></i>Select Vaccination
                                </label>
                                <select class="form-control" id="vaccination" name="vaccination_id" required>
                                    <option value="">Choose Vaccination</option>
                                </select>
                            </div>

                            <!-- Pet Details -->
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">
                                    <i class="fas fa-paw mr-2"></i>Pet Name
                                </label>
                                <input type="text" class="form-control" name="pet_name" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">
                                    <i class="fas fa-birthday-cake mr-2"></i>Pet Age
                                </label>
                                <input type="text" class="form-control" name="pet_age" required>
                            </div>

                            <!-- Date Selection -->
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">
                                    <i class="fas fa-calendar-alt mr-2"></i>Select Date
                                </label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="appointment_date" 
                                           name="appointment_date" required readonly>
                                    <div class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="fas fa-calendar"></i>
                                        </span>
                                    </div>
                                </div>
                                <small class="text-muted">
                                    * Weekends and holidays are disabled
                                </small>
                            </div>

                            <!-- Time Slots -->
                            <div class="col-md-6 mb-3">
                                <label class="font-weight-bold">
                                    <i class="fas fa-clock mr-2"></i>Select Time Slot
                                </label>
                                <select class="form-control" id="time_slot" name="selected_slot" required disabled>
                                    <option value="">First select date</option>
                                </select>
                                <small class="text-muted" id="slot_message"></small>
                            </div>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="form-group mt-3">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" 
                                       id="terms" required>
                                <label class="custom-control-label" for="terms">
                                    I agree to the terms and conditions
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i>Close
                    </button>
                    <button type="button" class="btn btn-outline-yellow" onclick="submitBooking()">
                        <i class="fas fa-arrow-left mr-2"></i>Book Appointment
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Initialize datepicker with better styling
        $('#appointment_date').datepicker({
            format: 'yyyy-mm-dd',
            startDate: new Date(),
            daysOfWeekDisabled: [0, 6], // Disable weekends
            autoclose: true,
            todayHighlight: true,
            beforeShowDay: function(date) {
                var holidays = <?php echo json_encode($holidays); ?>;
                var formatted = date.toISOString().split('T')[0];
                if (holidays.includes(formatted)) {
                    return false;
                }
                return true;
            }
        });

        // Enable/disable time slot based on date selection
        $('#appointment_date').change(function() {
            $('#time_slot').prop('disabled', false);
            loadTimeSlots();
        });

        // Reload time slots when vaccination changes
        $('#vaccination').change(function() {
            if($('#appointment_date').val()) {
                loadTimeSlots();
            }
        });
    });

    function showBookingModal(centerId) {
        $('#center_id').val(centerId);
        loadVaccinations(centerId);
        resetForm();
        $('#bookingModal').modal('show');
    }

    function loadVaccinations(centerId) {
        $.ajax({
            url: 'get_vaccinations.php',
            type: 'POST',
            data: {center_id: centerId},
            success: function(response) {
                $('#vaccination').html(response);
            }
        });
    }

    function loadTimeSlots() {
        var date = $('#appointment_date').val();
        var centerId = $('#center_id').val();
        var vaccinationId = $('#vaccination').val();
        
        if(date && centerId && vaccinationId) {
            $.ajax({
                url: 'get_slots.php',
                type: 'POST',
                data: {
                    date: date,
                    center_id: centerId,
                    vaccination_id: vaccinationId
                },
                success: function(response) {
                    $('#time_slot').html(response);
                    updateSlotMessage();
                }
            });
        }
    }

    function updateSlotMessage() {
        var slot = $('#time_slot option:selected');
        if(slot.val()) {
            $('#slot_message').html('Selected time: ' + slot.text()).removeClass('text-danger');
        }
    }

    function resetForm() {
        $('#bookingForm')[0].reset();
        $('#time_slot').prop('disabled', true);
        $('#slot_message').html('');
    }

    function submitBooking() {
        if($('#selected_slot').val() === '') {
            alert('Please select a time slot');
            return;
        }

        // Show loading state
        $('.modal-footer button').prop('disabled', true);
        $('.modal-footer .btn-primary').html('<i class="fas fa-spinner fa-spin mr-2"></i>Processing...');

        // Submit form via AJAX
        $.ajax({
            url: 'process_booking.php',
            type: 'POST',
            data: $('#bookingForm').serialize(),
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    alert(response.message);
                    $('#bookingModal').modal('hide');
                    // Optionally reload page or update UI
                    window.location.reload();
                } else {
                    alert(response.message);
                    // Re-enable buttons
                    $('.modal-footer button').prop('disabled', false);
                    $('.modal-footer .btn-primary').html('<i class="fas fa-check mr-2"></i>Book Appointment');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
                // Re-enable buttons
                $('.modal-footer button').prop('disabled', false);
                $('.modal-footer .btn-primary').html('<i class="fas fa-check mr-2"></i>Book Appointment');
            }
        });
    }
    </script>
</body>
</html>