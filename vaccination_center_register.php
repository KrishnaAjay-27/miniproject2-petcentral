<?php
session_start();
require('connection.php');

// Fetch districts
$districtQuery = "SELECT * FROM districts ORDER BY district_name";
$districtResult = mysqli_query($con, $districtQuery);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Form processing will be added here
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vaccination Center Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .registration-form {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .form-title {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }
        .required-field::after {
            content: "*";
            color: red;
            margin-left: 4px;
        }
        .preview-image {
            max-width: 200px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="registration-form">
            <h2 class="form-title">Vaccination Center Registration</h2>
            
            <form action="" method="POST" enctype="multipart/form-data">
                <!-- Center Details -->
                <div class="mb-4">
                    <h4>Center Details</h4>
                    <hr>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="centerName" class="form-label required-field">Center Name</label>
                        <input type="text" class="form-control" id="centerName" name="centerName" required>
                    </div>
                    <div class="col-md-6">
                        <label for="headName" class="form-label required-field">Head Doctor's Name</label>
                        <input type="text" class="form-control" id="headName" name="headName" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="licenseNumber" class="form-label required-field">License Number</label>
                        <input type="text" class="form-control" id="licenseNumber" name="licenseNumber" required>
                    </div>
                    <div class="col-md-6">
                        <label for="district" class="form-label required-field">District</label>
                        <select class="form-select" id="district" name="district" required>
                            <option value="">Select District</option>
                            <?php while($district = mysqli_fetch_assoc($districtResult)): ?>
                                <option value="<?php echo $district['district_id']; ?>">
                                    <?php echo htmlspecialchars($district['district_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label required-field">Complete Address</label>
                    <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="email" class="form-label required-field">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="col-md-6">
                        <label for="contactNumber" class="form-label required-field">Contact Number</label>
                        <input type="tel" class="form-control" id="contactNumber" name="contactNumber" 
                               pattern="[0-9]{10}" title="Please enter valid 10-digit mobile number" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="licenseProof" class="form-label required-field">License Proof (Image)</label>
                    <input type="file" class="form-control" id="licenseProof" name="licenseProof" 
                           accept="image/*" required onchange="previewImage(this);">
                    <img id="imagePreview" class="preview-image" style="display: none;">
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary px-5">Register</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('password').addEventListener('input', validatePassword);
        document.getElementById('confirmPassword').addEventListener('input', validatePassword);

        function validatePassword() {
            const password = document.getElementById('password');
            const confirm = document.getElementById('confirmPassword');
            const submitBtn = document.querySelector('button[type="submit"]');

            if (password.value.length < 8) {
                password.setCustomValidity('Password must be at least 8 characters long');
            } else {
                password.setCustomValidity('');
            }

            if (password.value !== confirm.value) {
                confirm.setCustomValidity('Passwords do not match');
            } else {
                confirm.setCustomValidity('');
            }

            submitBtn.disabled = password.value.length < 8 || password.value !== confirm.value;
        }

        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
