<?php
require('connection.php');

if(isset($_POST['center_id'])) {
    $center_id = mysqli_real_escape_string($con, $_POST['center_id']);
    
    // Fetch active vaccinations with stock > 0
    $query = "SELECT vd.*, vc.center_name 
              FROM vaccination_details vd
              JOIN vaccination_centers vc ON vd.center_id = vc.center_id
              WHERE vd.center_id = '$center_id' 
              AND vd.status = 1 
              AND vd.stock > 0 
              ORDER BY vd.vaccination_name";
    
    $result = mysqli_query($con, $query);
    
    if(mysqli_num_rows($result) > 0) {
        echo '<option value="">Select Vaccination</option>';
        while($vac = mysqli_fetch_assoc($result)) {
            echo '<option value="' . $vac['vaccination_id'] . '">' 
                 . htmlspecialchars($vac['vaccination_name']) 
                 . ' - For ' . htmlspecialchars($vac['pet_species'])
                 . ' (₹' . number_format($vac['price'], 2) . ')'
                 . ' [Stock: ' . $vac['stock'] . ']'
                 . '</option>';
        }
    } else {
        echo '<option value="">No vaccinations available</option>';
    }
} else {
    echo '<option value="">First select a center</option>';
}

mysqli_close($con);
?>