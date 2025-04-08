let currentVideoId = null;

function openVideoModal(videoPath, videoId) {
    currentVideoId = videoId;
    const videoPlayer = document.getElementById('videoPlayer');
    videoPlayer.src = videoPath;
    
    // Load content file if exists
    loadVideoContent(videoId);
    
    new bootstrap.Modal(document.getElementById('videoModal')).show();
}

async function loadVideoContent(videoId) {
    try {
        const response = await fetch(`get_video_content.php?id=${videoId}`);
        const data = await response.json();
        
        if (data.success && data.content) {
            document.getElementById('transcriptArea').value = data.content;
        }
    } catch (error) {
        console.error('Error loading content:', error);
    }
}

// Handle file upload
document.getElementById('fileUploadInput').addEventListener('change', async function(e) {
    const file = e.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('content_file', file);
    formData.append('video_id', currentVideoId);

    try {
        const response = await fetch('upload_content.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('transcriptArea').value = await file.text();
            alert('Content uploaded successfully!');
        } else {
            alert('Error uploading content: ' + data.error);
        }
    } catch (error) {
        alert('Error uploading file: ' + error);
    }
});

// Handle content download
document.getElementById('downloadContentBtn').addEventListener('click', async function() {
    if (!currentVideoId) return;
    
    try {
        const response = await fetch(`download_content.php?id=${currentVideoId}`);
        const blob = await response.blob();
        
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `video_content_${currentVideoId}.txt`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
    } catch (error) {
        alert('Error downloading content: ' + error);
    }
});

async function translateContent() {
    const text = document.getElementById('transcriptArea').value;
    const targetLang = document.getElementById('targetLanguage').value;
    
    if (!text.trim()) {
        alert('Please enter or upload some content first');
        return;
    }

    try {
        const response = await fetch('translate_content.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                text: text,
                targetLang: targetLang
            })
        });
        
        const data = await response.json();
        if (data.success) {
            document.getElementById('translatedArea').value = data.translatedText;
        } else {
            alert('Translation failed: ' + data.error);
        }
    } catch (error) {
        alert('Error during translation: ' + error);
    }
}
