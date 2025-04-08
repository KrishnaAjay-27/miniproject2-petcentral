<?php
require('connection.php');

if(isset($_POST['center_id'])) {
    $center_id = mysqli_real_escape_string($con, $_POST['center_id']);
    
    $query = "SELECT vc.*, d.district_name 
              FROM vaccination_centers vc 
              LEFT JOIN districts d ON vc.district_id = d.district_id 
              WHERE vc.center_id = '$center_id'";
    
    $result = mysqli_query($con, $query);
    $center = mysqli_fetch_assoc($result);
    
    if($center) {
        ?>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="font-weight-bold">Basic Information</h6>
                    <table class="table table-bordered">
                        <tr>
                            <th>Center Name</th>
                            <td><?php echo htmlspecialchars($center['center_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Head Name</th>
                            <td><?php echo htmlspecialchars($center['head_name']); ?></td>
                        </tr>
                        <tr>
                            <th>License Number</th>
                            <td><?php echo htmlspecialchars($center['license_number']); ?></td>
                        </tr>
                        <tr>
                            <th>District</th>
                            <td><?php echo htmlspecialchars($center['district_name']); ?></td>
                        </tr>
                    </table>
                </div>
                
                <div class="col-md-6">
                    <h6 class="font-weight-bold">Contact Information</h6>
                    <table class="table table-bordered">
                        <tr>
                            <th>Email</th>
                            <td><?php echo htmlspecialchars($center['email']); ?></td>
                        </tr>
                        <tr>
                            <th>Contact Number</th>
                            <td><?php echo htmlspecialchars($center['contact_number']); ?></td>
                        </tr>
                        <tr>
                            <th>Address</th>
                            <td><?php echo htmlspecialchars($center['address']); ?></td>
                        </tr>
                        <tr>
                            <th>Registration Date</th>
                            <td><?php echo date('d-m-Y', strtotime($center['registration_date'])); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-6">
                    <h6 class="font-weight-bold">License Proof</h6>
                    <div class="border p-2 text-center">
                        <?php if($center['license_proof']) { ?>
                            <img src="uploads/<?php echo htmlspecialchars($center['license_proof']); ?>" 
                                 class="img-fluid" style="max-height: 200px;" 
                                 alt="License Proof">
                        <?php } else { ?>
                            <p class="text-muted">No license proof uploaded</p>
                        <?php } ?>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <h6 class="font-weight-bold">Center Image</h6>
                    <div class="border p-2 text-center">
                        <?php if($center['center_image']) { ?>
                            <img src="uploads/<?php echo htmlspecialchars($center['center_image']); ?>" 
                                 class="img-fluid" style="max-height: 200px;" 
                                 alt="Center Image">
                        <?php } else { ?>
                            <p class="text-muted">No center image uploaded</p>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    } else {
        echo '<div class="alert alert-danger">Center details not found.</div>';
    }
}
?>