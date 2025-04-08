<?php
session_start();
require('connection.php');

if (!isset($_SESSION['uid'])) {
    exit('Not authorized');
}

$deid = $_SESSION['uid'];

// Fetch messages where either:
// 1. Messages sent by delivery boy (lid = delivery boy id)
// 2. Messages sent by admin (sender_id = 16) to this delivery boy (receiver_id = delivery boy id)
$query = "SELECT * FROM chatmessage 
          WHERE (lid = ? AND sender_id = ?) 
          OR (sender_id = 16 AND receiver_id = ?)
          ORDER BY send_at ASC";

$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, 'iii', $deid, $deid, $deid);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Track displayed messages
$displayed_messages = array();

while ($row = mysqli_fetch_assoc($result)) {
    if (in_array($row['id'], $displayed_messages)) {
        continue;
    }
    
    $displayed_messages[] = $row['id'];
    $is_sent = $row['sender_id'] == $deid;
    ?>
    <div class="message <?php echo $is_sent ? 'sent' : 'received'; ?>" id="msg-<?php echo $row['id']; ?>">
        <div class="message-content">
            <?php if ($row['message_type'] == 'image' && $row['image_path']) : ?>
                <div class="image-wrapper">
                    <img src="<?php echo htmlspecialchars($row['image_path']); ?>" class="message-image" onclick="openImageViewer(this.src)">
                </div>
            <?php endif; ?>
            
            <?php if ($row['message']) : ?>
                <p class="message-text"><?php echo htmlspecialchars($row['message']); ?></p>
            <?php endif; ?>
            
            <div class="message-info">
                <span class="time"><?php echo date('h:i A', strtotime($row['send_at'])); ?></span>
                <?php if ($is_sent) : ?>
                    <span class="status"><?php echo $row['is_read'] ? '✓✓' : '✓'; ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}

// Mark messages from admin as read
$update_query = "UPDATE chatmessage 
                SET is_read = 1 
                WHERE receiver_id = ? 
                AND sender_id = 16 
                AND is_read = 0";
$stmt = mysqli_prepare($con, $update_query);
mysqli_stmt_bind_param($stmt, 'i', $deid);
mysqli_stmt_execute($stmt);

mysqli_close($con);
?>

<style>
.message {
    margin-bottom: 10px;
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
    border-top-right-radius: 0;
}

.message.received .message-content {
    background: white;
    border-top-left-radius: 0;
}

.message-text {
    margin: 0;
    white-space: pre-wrap;
    word-break: break-word;
    font-size: 14px;
    line-height: 1.4;
}

.image-wrapper {
    max-width: 200px;
    margin-bottom: 4px;
    border-radius: 8px;
    overflow: hidden;
}

.message-image {
    width: 100%;
    height: auto;
    display: block;
    cursor: pointer;
}

.message-info {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 4px;
    margin-top: 2px;
    font-size: 11px;
    color: #667781;
}

.time {
    opacity: 0.7;
}

.status {
    color: #64B5F6;
}
</style>

<script>
function openImageViewer(src) {
    const viewer = document.createElement('div');
    viewer.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        cursor: pointer;
    `;
    
    const img = document.createElement('img');
    img.src = src;
    img.style.cssText = `
        max-width: 90%;
        max-height: 90vh;
        object-fit: contain;
    `;
    
    viewer.appendChild(img);
    document.body.appendChild(viewer);
    
    viewer.onclick = () => viewer.remove();
}

$(document).ready(function() {
    let lastMessageId = 0;
    
    function updateChat() {
        $.get('get_messages.php', function(response) {
            $('#chatMessages').html(response);
            scrollToBottom();
        });
    }
    
    function scrollToBottom() {
        const chatMessages = document.getElementById('chatMessages');
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // Update every 3 seconds
    setInterval(updateChat, 3000);
    
    // Initial scroll to bottom
    scrollToBottom();
});
</script>
