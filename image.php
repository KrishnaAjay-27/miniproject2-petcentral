<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dog Breed Classifier</title>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/mobilenet"></script>
  
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #4a90e2;
        }

        .container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-top: 20px;
        }

        #imageUpload {
            display: block;
            margin: 20px auto;
            padding: 10px;
            border: 2px solid #ccc;
            border-radius: 5px;
            cursor: pointer;
        }

        #classifyButton {
            display: block;
            width: 100%;
            background-color: #4a90e2;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
            transition: background-color 0.3s ease;
        }

        #classifyButton:hover {
            background-color: #357ab8;
        }

        #result {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            margin-top: 20px;
        }

        #uploadedImage {
            max-width: 100%;
            border-radius: 10px;
            margin-top: 20px;
            display: none;
        }

        .footer {
            text-align: center;
            margin-top: 50px;
            font-size: 14px;
            color: #999;
        }
    </style>
</head>
<body>

    <h1>Dog Breed Classifier</h1>
    <div class="container">
        <input type="file" id="imageUpload" accept="image/*">
        <button id="classifyButton">Classify</button>
        <div id="result"></div>
        <img id="uploadedImage" alt="Uploaded dog image">
    </div>

   

    <script src="script.js?v=1"></script>
</body>
</html>
