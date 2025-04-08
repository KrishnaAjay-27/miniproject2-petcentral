<?php
require('connection.php');
require('header.php');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dog Skin Disease Diagnosis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #f9c74f;
            --secondary-color: #ffd166;
            --accent-color: #ffba08;
            --text-color: #333;
            --bg-color: #fff9eb;
            --error-color: #dc3545;
            --success-color: #28a745;
        }

        body {
            background-color: var(--bg-color);
            font-family: 'Poppins', sans-serif;
        }

        .disease-card {
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
            border: none;
        }

        .disease-card:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .card-header {
            background-color: var(--primary-color) !important;
            color: var(--text-color) !important;
            border-radius: 15px 15px 0 0 !important;
            border-bottom: none;
        }

        .preview-image {
            max-height: 300px;
            object-fit: contain;
            border-radius: 10px;
        }

        .btn-primary {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
            color: var(--text-color) !important;
            font-weight: 500;
            padding: 12px 25px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: var(--accent-color) !important;
            border-color: var(--accent-color) !important;
            transform: translateY(-2px);
        }

        .spinner-border {
            color: var(--primary-color) !important;
        }

        .confidence-high, .confidence-medium {
            color: var(--primary-color);
            font-weight: 600;
        }

        .badge-treatment, .badge-medicine {
            background-color: var(--secondary-color) !important;
            color: var(--text-color) !important;
            font-weight: 500;
            padding: 8px 12px;
        }

        .alert-success {
            background-color: #fff;
            border-color: var(--primary-color);
            color: var(--text-color);
            border-radius: 10px;
        }

        .alert-warning {
            background-color: var(--bg-color);
            border-color: var(--accent-color);
            color: var(--text-color);
        }

        .card-header h5 {
            color: var(--text-color);
            font-weight: 600;
        }

        .list-group-item {
            border-color: rgba(249, 199, 79, 0.2);
            padding: 15px;
        }

        .form-control {
            border: 1px solid rgba(249, 199, 79, 0.3);
            padding: 12px;
            border-radius: 8px;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(249, 199, 79, 0.25);
        }

        .form-label {
            color: var(--text-color);
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card disease-card">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0">Dog Skin Disease Diagnosis</h2>
                    </div>
                    <div class="card-body">
                        <form id="uploadForm" class="mb-4">
                            <div class="mb-3">
                                <label for="imageInput" class="form-label">Upload Image of Dog Skin Condition</label>
                                <input type="file" class="form-control" id="imageInput" name="image" accept="image/*" required>
                            </div>
                            <div class="mb-3">
                                <div id="imagePreview" class="d-none text-center">
                                    <img id="preview" class="preview-image img-fluid rounded mb-3">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Analyze Image</button>
                        </form>
                        
                        <div id="loadingIndicator" class="text-center d-none">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Analyzing image...</p>
                        </div>
                        
                        <div id="result" class="d-none"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Image preview
        const imageInput = document.getElementById('imageInput');
        const imagePreview = document.getElementById('imagePreview');
        const preview = document.getElementById('preview');
        
        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    imagePreview.classList.remove('d-none');
                }
                reader.readAsDataURL(file);
            }
        });
        
        // Form submission
        document.getElementById('uploadForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData();
            const imageFile = document.getElementById('imageInput').files[0];
            if (!imageFile) {
                alert('Please select an image to upload');
                return;
            }
            
            formData.append('image', imageFile);
            
            // Show loading indicator
            document.getElementById('loadingIndicator').classList.remove('d-none');
            document.getElementById('result').classList.add('d-none');
            
            try {
                const response = await fetch('diagnose.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                // Hide loading indicator
                document.getElementById('loadingIndicator').classList.add('d-none');
                
                const resultDiv = document.getElementById('result');
                resultDiv.classList.remove('d-none');
                
                if (data.success) {
                    // Determine confidence level class
                    let confidenceClass = 'confidence-medium';
                    if (data.confidence > 0.9) {
                        confidenceClass = 'confidence-high';
                    }
                    
                    // Build treatment list
                    let treatmentsList = '';
                    if (data.treatment && data.treatment.length > 0) {
                        treatmentsList = '<ul class="list-group list-group-flush mb-3">';
                        data.treatment.forEach(treatment => {
                            treatmentsList += `<li class="list-group-item"><span class="badge rounded-pill badge-treatment me-2">Treatment</span>${treatment}</li>`;
                        });
                        treatmentsList += '</ul>';
                    }
                    
                    // Build medicines list
                    let medicinesList = '';
                    if (data.medicines && data.medicines.length > 0) {
                        medicinesList = '<ul class="list-group list-group-flush">';
                        data.medicines.forEach(medicine => {
                            medicinesList += `<li class="list-group-item"><span class="badge rounded-pill badge-medicine me-2">Medicine</span>${medicine}</li>`;
                        });
                        medicinesList += '</ul>';
                    }
                
                    resultDiv.innerHTML = `
                        <div class="alert alert-success mb-4">
                            <h4 class="alert-heading">Diagnosis Result</h4>
                            <p><strong>Disease:</strong> ${data.disease}</p>
                            <p><strong>Confidence:</strong> <span class="${confidenceClass}">${(data.confidence * 100).toFixed(1)}%</span></p>
                            <hr>
                            <p>${data.details}</p>
                            <p><strong>Common Symptoms:</strong> ${data.symptoms}</p>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Recommended Treatments</h5>
                            </div>
                            <div class="card-body">
                                ${treatmentsList}
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Recommended Medicines</h5>
                            </div>
                            <div class="card-body">
                                ${medicinesList}
                            </div>
                        </div>
                        
                        <div class="alert alert-warning mt-4">
                            <strong>Important:</strong> This is an AI-based diagnosis. Always consult with a veterinarian for proper diagnosis and treatment.
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <h4 class="alert-heading">Error</h4>
                            <p>${data.error}</p>
                        </div>
                    `;
                }
            } catch (error) {
                document.getElementById('loadingIndicator').classList.add('d-none');
                document.getElementById('result').classList.remove('d-none');
                document.getElementById('result').innerHTML = `
                    <div class="alert alert-danger">
                        <h4 class="alert-heading">Error</h4>
                        <p>Failed to process the image: ${error.message}</p>
                    </div>
                `;
            }
        });
    </script>
</body>
</html>
