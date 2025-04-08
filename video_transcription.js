let recognition = null;
let isTranscribing = false;

function openVideoModal(videoPath, videoId) {
    const videoPlayer = document.getElementById('videoPlayer');
    const transcriptArea = document.getElementById('transcriptArea');
    
    // Set video source
    videoPlayer.src = videoPath;
    
    // Show modal
    const videoModal = new bootstrap.Modal(document.getElementById('videoModal'));
    videoModal.show();
    
    // Initialize Web Speech API
    if (!recognition) {
        recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
        recognition.continuous = true;
        recognition.interimResults = true;
        recognition.lang = 'en-US';
        
        recognition.onresult = function(event) {
            let finalTranscript = '';
            for (let i = event.resultIndex; i < event.results.length; i++) {
                if (event.results[i].isFinal) {
                    finalTranscript += event.results[i][0].transcript + ' ';
                }
            }
            if (finalTranscript) {
                transcriptArea.value += finalTranscript + '\n';
            }
        };
        
        recognition.onerror = function(event) {
            console.error('Speech recognition error:', event.error);
            transcriptArea.value += '\nError: ' + event.error + '\n';
        };

        recognition.onend = function() {
            if (isTranscribing) {
                recognition.start(); // Restart if we're still supposed to be transcribing
            }
        };
    }
    
    // Start transcription when video plays
    videoPlayer.onplay = function() {
        if (!isTranscribing) {
            transcriptArea.value = 'Starting transcription...\n';
            recognition.start();
            isTranscribing = true;
        }
    };
    
    // Stop transcription when video is paused or ends
    videoPlayer.onpause = function() {
        if (isTranscribing) {
            recognition.stop();
            isTranscribing = false;
            transcriptArea.value += '\nTranscription paused.\n';
        }
    };
    
    videoPlayer.onended = function() {
        if (isTranscribing) {
            recognition.stop();
            isTranscribing = false;
            transcriptArea.value += '\nTranscription completed.\n';
        }
    };
}

// Clean up when modal is closed
document.getElementById('videoModal').addEventListener('hidden.bs.modal', function () {
    const videoPlayer = document.getElementById('videoPlayer');
    if (recognition && isTranscribing) {
        recognition.stop();
        isTranscribing = false;
    }
    videoPlayer.pause();
    videoPlayer.currentTime = 0;
});

function translateContent() {
    const transcriptArea = document.getElementById('transcriptArea');
    const targetLanguage = document.getElementById('targetLanguage').value;
    const translatedArea = document.getElementById('translatedArea');
    
    // Get the transcript text, excluding status messages
    const transcriptText = transcriptArea.value
        .split('\n')
        .filter(line => !line.includes('Starting transcription') && 
                      !line.includes('Transcription paused') && 
                      !line.includes('Transcription completed') &&
                      !line.includes('Error:'))
        .join('\n')
        .trim();
    
    if (!transcriptText) {
        alert('Please wait for some content to be transcribed before translating.');
        return;
    }
    
    translatedArea.value = 'Translating...';
    
    fetch('fetch_and_translate.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            text: transcriptText,
            target: targetLanguage
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            translatedArea.value = data.translatedText;
        } else {
            translatedArea.value = 'Translation failed: ' + data.error;
        }
    })
    .catch(error => {
        translatedArea.value = 'Translation failed: ' + error.message;
    });
} 