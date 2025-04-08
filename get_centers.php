<?php
require('connection.php');

if(isset($_POST['district_id'])) {
    $district_id = mysqli_real_escape_string($con, $_POST['district_id']);
    
    // Join with district table to get center details
    $query = "SELECT vc.*, d.district_name 
              FROM vaccination_centers vc
              JOIN district d ON vc.district_id = d.district_id 
              WHERE vc.district_id = '$district_id' 
              AND vc.status = 1 
              ORDER BY vc.center_name";
    
    $result = mysqli_query($con, $query);
    
    echo '<option value="">Select Center</option>';
    while($center = mysqli_fetch_assoc($result)) {
        echo '<option value="' . $center['center_id'] . '">' 
             . htmlspecialchars($center['center_name']) 
             . ' - ' . htmlspecialchars($center['head_name'])
             . '</option>';
    }
} else {
    echo '<option value="">First select a district</option>';
}

mysqli_close($con);
?>