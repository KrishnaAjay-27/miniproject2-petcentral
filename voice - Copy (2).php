<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Voice Recognition Test</title>
</head>
<body>
    <button id="start">Start Voice Recognition</button>
    <p id="result"></p>

    <script>
        const startButton = document.getElementById('start');
        const resultParagraph = document.getElementById('result');
        const recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();

        startButton.addEventListener('click', () => {
            recognition.start();
        });

        recognition.onresult = function(event) {
            const transcript = event.results[0][0].transcript;
            resultParagraph.textContent = 'You said: ' + transcript;
        };

        recognition.onerror = function(event) {
            console.error('Error:', event);
        };
    </script>
</body>
</html>
