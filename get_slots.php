<?php
require('connection.php');

if(isset($_POST['date']) && isset($_POST['center_id']) && isset($_POST['vaccination_id'])) {
    $date = mysqli_real_escape_string($con, $_POST['date']);
    $center_id = mysqli_real_escape_string($con, $_POST['center_id']);
    
    // Get day of week
    $day = date('l', strtotime($date));

    // Query to fetch time slots with booking status for the specific date
    $slots_query = "SELECT vs.id, vs.start_time, vs.end_time,
                    (SELECT COUNT(*) 
                     FROM vaccination_bookings vb 
                     WHERE vb.id = vs.id 
                     AND vb.appointment_date = '$date'
                     AND vb.booking_status != 'cancelled') as is_booked
                   FROM vaccination_slots vs 
                   WHERE vs.center_id = '$center_id' 
                   AND vs.day_of_week = '$day' 
                   AND vs.status = 1 
                   ORDER BY vs.start_time";

    $slots_result = mysqli_query($con, $slots_query);

    if(!$slots_result) {
        echo '<div class="alert alert-danger">Error fetching slots</div>';
        exit;
    }

    // Create dropdown
    echo '<select class="form-control" id="time_slot" name="selected_slot" required>
            <option value="">Select Time Slot</option>';
    
    if(mysqli_num_rows($slots_result) > 0) {
        while($slot = mysqli_fetch_assoc($slots_result)) {
            // Format times to 12-hour format
            $start_time = date('h:i A', strtotime($slot['start_time']));
            $end_time = date('h:i A', strtotime($slot['end_time']));
            
            // Check if slot is already booked for this date
            if($slot['is_booked'] > 0) {
                echo "<option value='' disabled>
                        {$start_time} - {$end_time} (Already Booked)
                      </option>";
            } else {
                echo "<option value='{$slot['id']}'>
                        {$start_time} - {$end_time} (Available)
                      </option>";
            }
        }
    } else {
        echo '<option value="" disabled>No slots available</option>';
    }
    
    echo '</select>';

} else {
    echo '<div class="alert alert-warning">Please select date and vaccination</div>';
}
?>