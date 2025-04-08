<?php
session_start();
require('connection.php');

if (!isset($_SESSION['uid'])) {
    header('Location: login.php');
    exit();
}

$deid = $_SESSION['uid'];

// Fetch delivery boy's name
$query = "SELECT name FROM deliveryboy WHERE lid = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, 'i', $deid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$delivery_boy = mysqli_fetch_assoc($result);

// Get unread message count
$unread_query = "SELECT COUNT(*) as unread_count 
                 FROM chatmessage 
                 WHERE lid = ? 
                 AND sender_id = 16 
                 AND is_read = 0";
$stmt = mysqli_prepare($con, $unread_query);
mysqli_stmt_bind_param($stmt, 'i', $deid);
mysqli_stmt_execute($stmt);
$unread_result = mysqli_stmt_get_result($stmt);
$unread_count = mysqli_fetch_assoc($unread_result)['unread_count'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Chat with Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background: #003366;
            color: #fff;
            z-index: 100;
            display: flex;
            flex-direction: column;
        }

        .welcome-section {
            text-align: center;
            padding: 30px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .welcome-section h2 {
            font-size: 28px;
            margin-bottom: 10px;
            font-weight: normal;
        }

        .welcome-section p {
            font-size: 20px;
            margin: 0;
            opacity: 0.9;
        }

        .nav-link {
            color: #fff !important;
            padding: 15px 25px;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-left: 4px solid transparent;
            transition: 0.3s;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            border-left-color: #fff;
            text-decoration: none;
        }

        .nav-link i {
            margin-right: 10px;
            width: 20px;
            font-size: 18px;
        }

        .nav-link-content {
            display: flex;
            align-items: center;
        }

        .badge-danger {
            background-color: #dc3545;
            color: white;
            padding: 4px 8px;
            border-radius: 10px;
            font-size: 12px;
        }

        .logout-section {
            margin-top: auto;
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logout-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 10px;
            width: 100%;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.3s;
        }

        .logout-btn:hover {
            background: #c82333;
            text-decoration: none;
            color: white;
        }

        .logout-btn i {
            margin-right: 10px;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            height: 100vh;
        }

        .chat-container {
            margin-left: 250px;
            height: 100vh;
            display: flex;
            flex-direction: column;
            background: #efeae2;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            scroll-behavior: smooth;
        }

        .message {
            margin-bottom: 12px;
            max-width: 70%;
            clear: both;
        }

        .message.sent {
            float: right;
        }

        .message.received {
            float: left;
        }

        .message-content {
            padding: 8px 12px;
            border-radius: 12px;
            position: relative;
            box-shadow: 0 1px 0.5px rgba(0,0,0,0.13);
        }

        .message.sent .message-content {
            background: #dcf8c6;
            margin-left: auto;
        }

        .message.received .message-content {
            background: white;
        }

        .message-image {
            max-width: 200px;
            border-radius: 5px;
            cursor: pointer;
        }

        .message-audio {
            width: 200px;
        }

        .message-info {
            font-size: 11px;
            color: #667781;
            margin-top: 2px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }

        .chat-input {
            padding: 15px;
            background: #f0f2f5;
            border-top: 1px solid #e0e0e0;
        }

        #previewContainer {
            margin-top: 10px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .preview-image {
            max-height: 100px;
            border-radius: 5px;
        }

        /* Custom scrollbar styling */
        .chat-messages::-webkit-scrollbar {
            width: 6px;
        }

        .chat-messages::-webkit-scrollbar-track {
            background: transparent;
        }

        .chat-messages::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 3px;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="welcome-section">
        <h2>Welcome</h2>
        <p><?php echo htmlspecialchars($delivery_boy['name']); ?></p>
    </div>
    <div class="nav flex-column">
        <a class="nav-link" href="deliveryindex.php">
            <div class="nav-link-content">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </div>
        </a>
        <a class="nav-link" href="delivery_profile.php">
            <div class="nav-link-content">
                <i class="fas fa-user"></i>
                <span>My Profile</span>
            </div>
        </a>
        <a class="nav-link" href="viewdeliveryassignment.php">
            <div class="nav-link-content">
                <i class="fas fa-truck"></i>
                <span>View Assignments</span>
            </div>
        </a>
        <a class="nav-link active" href="delivery_chat.php">
            <div class="nav-link-content">
                <i class="fas fa-comments"></i>
                <span>Chat with Admin</span>
            </div>
            <?php if ($unread_count > 0): ?>
                <span class="badge badge-danger"><?php echo $unread_count; ?></span>
            <?php endif; ?>
        </a>
        <a class="nav-link" href="notificationdelivery.php">
            <div class="nav-link-content">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
            </div>
        </a>
    </div>
    <div class="logout-section">
        <a href="logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </div>
</div>

<div class="chat-container">
    <div class="chat-messages" id="chatMessages">
        <?php
        $query = "SELECT * FROM chatmessage WHERE lid = ? ORDER BY send_at ASC";
        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, 'i', $deid);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_assoc($result)) {
            $is_sent = $row['sender_id'] == $deid;
            ?>
            <div class="message <?php echo $is_sent ? 'sent' : 'received'; ?>">
                <div class="message-content">
                    <?php if ($row['message_type'] == 'image' && $row['image_path']) : ?>
                        <img src="<?php echo htmlspecialchars($row['image_path']); ?>" class="message-image">
                    <?php endif; ?>
                    
                    <?php if ($row['message_type'] == 'voice' && $row['voice_path']) : ?>
                        <audio controls class="message-audio">
                            <source src="<?php echo htmlspecialchars($row['voice_path']); ?>" type="audio/mpeg">
                        </audio>
                    <?php endif; ?>
                    
                    <?php if ($row['message']) : ?>
                        <p><?php echo htmlspecialchars($row['message']); ?></p>
                    <?php endif; ?>
                </div>
                <div class="message-info">
                    <span class="time"><?php echo date('H:i', strtotime($row['send_at'])); ?></span>
                    <?php if ($is_sent) : ?>
                        <span class="status"><?php echo $row['is_read'] ? '✓✓' : '✓'; ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
    
    <div class="chat-input">
        <form id="chatForm" enctype="multipart/form-data">
            <div class="input-group">
                <input type="text" class="form-control" id="messageInput" placeholder="Type a message...">
                <div class="input-group-append">
                    <label class="btn btn-outline-secondary" for="imageInput">
                        <i class="fas fa-image"></i>
                        <input type="file" id="imageInput" accept="image/*" style="display: none;">
                    </label>
                    <label class="btn btn-outline-secondary" for="voiceInput">
                        <i class="fas fa-microphone"></i>
                        <input type="file" id="voiceInput" accept="audio/*" style="display: none;">
                    </label>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
            <div id="previewContainer" style="display: none;">
                <div class="preview-content"></div>
                <button type="button" class="btn btn-sm btn-danger mt-2" id="cancelUpload">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    let currentFile = null;
    let currentFileType = null;
    let lastMessageId = 0;
    let isScrolling = false;

    // Improved scroll to bottom function
    function scrollToBottom(smooth = true) {
        const chatMessages = document.getElementById('chatMessages');
        if (!chatMessages) return;

        if (smooth) {
            chatMessages.scrollTo({
                top: chatMessages.scrollHeight,
                behavior: 'smooth'
            });
        } else {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    }

    // Check if user is near bottom
    function isNearBottom() {
        const chatMessages = document.getElementById('chatMessages');
        if (!chatMessages) return true;

        const threshold = 100; // pixels from bottom
        return (chatMessages.scrollHeight - chatMessages.scrollTop - chatMessages.clientHeight) < threshold;
    }

    // Update chat with scroll position check
    function updateChat() {
        const wasNearBottom = isNearBottom();
        
        $.get('get_messages.php', function(response) {
            $('#chatMessages').html(response);
            
            // Only scroll if user was already near bottom
            if (wasNearBottom) {
                scrollToBottom(true);
            }
        });
    }

    // Handle new message submission
    $('#chatForm').on('submit', function(e) {
        e.preventDefault();
        
        const messageInput = $('#messageInput');
        const message = messageInput.val().trim();
        
        if (!message && !currentFile) return;

        const formData = new FormData();
        if (message) formData.append('message', message);
        if (currentFile) {
            formData.append(currentFileType, currentFile);
        }

        // Show message immediately
        const tempMessage = `
            <div class="message sent">
                <div class="message-content">
                    ${currentFile && currentFileType === 'image' ? 
                        `<div class="image-wrapper">
                            <img src="${URL.createObjectURL(currentFile)}" class="message-image">
                         </div>` : ''}
                    ${message ? `<p class="message-text">${message}</p>` : ''}
                    <div class="message-info">
                        <span class="time">${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
                        <span class="status">✓</span>
                    </div>
                </div>
            </div>
        `;
        $('#chatMessages').append(tempMessage);
        scrollToBottom(true);

        $.ajax({
            url: 'send_message.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                messageInput.val('');
                $('#cancelUpload').click();
                updateChat();
            },
            error: function(error) {
                console.error('Error:', error);
                alert('Failed to send message. Please try again.');
            }
        });
    });

    // Handle image selection
    $('#imageInput').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            currentFile = file;
            currentFileType = 'image';
            showPreview(file);
        }
    });

    function showPreview(file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#previewContainer').show();
            $('.preview-content').html(`<img src="${e.target.result}" class="preview-image">`);
        }
        reader.readAsDataURL(file);
    }

    $('#cancelUpload').on('click', function() {
        currentFile = null;
        currentFileType = null;
        $('#previewContainer').hide();
        $('#imageInput').val('');
    });

    // Handle scroll events
    $('#chatMessages').on('scroll', function() {
        isScrolling = true;
        clearTimeout($.data(this, 'scrollTimer'));
        $.data(this, 'scrollTimer', setTimeout(function() {
            isScrolling = false;
        }, 250));
    });

    // Initial load
    updateChat();
    scrollToBottom(false);

    // Regular updates
    setInterval(function() {
        if (!isScrolling) {
            updateChat();
        }
    }, 3000);
});
</script>

</body>
</html>