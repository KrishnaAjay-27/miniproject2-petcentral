<?php
require('connection.php');
include('header.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dog Breed Classifier</title>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/mobilenet"></script>
  
    <style>
        :root {
            --primary-color: #f9c74f;
            --secondary-color: #ffd166;
            --accent-color: #ffba08;
            --text-color: #333;
            --bg-color: #fff9eb;
            --error-color: #dc3545;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }

        .main-content {
            max-width: 800px;
            margin: 60px auto 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: var(--text-color);
            font-weight: 600;
            margin-bottom: 30px;
        }

        .container {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .container:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        #backButton {
            text-decoration: none;
            color: var(--text-color);
            font-size: 24px;
            position: absolute;
            top: 20px;
            left: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        #backButton:hover {
            color: var(--accent-color);
            transform: translateX(-5px);
        }

        #imageUpload {
            display: block;
            width: 100%;
            margin: 20px auto;
            padding: 15px;
            border: 2px dashed var(--primary-color);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            background-color: var(--bg-color);
        }

        #imageUpload:hover {
            border-color: var(--accent-color);
            background-color: rgba(249, 199, 79, 0.1);
        }

        #classifyButton {
            display: block;
            width: 100%;
            background-color: var(--primary-color);
            color: var(--text-color);
            padding: 15px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        #classifyButton:hover {
            background-color: var(--accent-color);
            transform: translateY(-2px);
        }

        #result {
            font-size: 18px;
            font-weight: 500;
            text-align: center;
            margin-top: 25px;
            padding: 20px;
            border-radius: 8px;
            background-color: var(--bg-color);
            border: 1px solid var(--primary-color);
        }

        #uploadedImage {
            max-width: 100%;
            border-radius: 12px;
            margin-top: 20px;
            display: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .footer {
            text-align: center;
            margin-top: 50px;
            padding: 20px;
            font-size: 14px;
            color: var(--text-color);
            border-top: 1px solid var(--secondary-color);
        }

        /* Loading animation */
        .loading {
            display: none;
            text-align: center;
            margin: 20px 0;
        }

        .loading-spinner {
            border: 4px solid var(--bg-color);
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .main-content {
                margin-top: 80px;
                padding: 10px;
            }

            .container {
                padding: 20px;
            }

            h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <a id="backButton" href="userindex.php">
            <span>&#8592;</span>
            <span>Back</span>
        </a>
        <h1>Dog Breed Classifier</h1>
        <div class="container">
            <input type="file" id="imageUpload" accept="image/*">
            <button id="classifyButton">Classify Breed</button>
            <div class="loading">
                <div class="loading-spinner"></div>
                <p>Analyzing image...</p>
            </div>
            <div id="result"></div>
            <img id="uploadedImage" alt="Uploaded dog image">
        </div>
    </div>

    <script src="script.js?v=1"></script>
</body>
</html>
