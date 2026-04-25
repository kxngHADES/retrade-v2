<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../utils/protected_route.php';

use Lib\services\Chat_services;

$chatService = new Chat_services();
$uid = $_SESSION['uid'] ?? null;
if (!$uid) {
    header("Location: /pages/login/");
    exit;
}

$rooms = $chatService->getUserRooms($uid);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chats - Retrade</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f4f4f4; }
        .chat-list { max-width: 600px; margin: 20px auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .chat-item { display: flex; align-items: center; padding: 15px; border-bottom: 1px solid #eee; text-decoration: none; color: inherit; transition: background 0.2s; }
        .chat-item:last-child { border-bottom: none; }
        .chat-item:hover { background: #f9f9f9; }
        .chat-avatar { width: 50px; height: 50px; border-radius: 50%; background: #ccc; margin-right: 15px; flex-shrink: 0; overflow: hidden; }
        .chat-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .chat-info { flex-grow: 1; overflow: hidden; }
        .chat-name { font-weight: bold; margin: 0 0 5px; font-size: 1.1em; color: #333; }
        .chat-preview { margin: 0; color: #777; font-size: 0.9em; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: flex; align-items: center; gap: 5px; }
        header { background: #333; color: white; padding: 15px; text-align: center; }
        header h2 { margin: 0; }
    </style>
</head>
<body>
    <header>
        <h2>Chats</h2>
    </header>
    <div class="chat-list" id="chat-list">
        <?php if (empty($rooms)): ?>
            <div style="padding: 20px; text-align: center; color: #777;">No chats found.</div>
        <?php else: ?>
            <?php foreach ($rooms as $room): ?>
                <a href="/pages/chat/room/?room_id=<?= urlencode($room['room_id']) ?>" class="chat-item" id="room-item-<?= htmlspecialchars($room['room_id']) ?>">
                    <div class="chat-avatar">
                        <!-- Future image placeholder -->
                    </div>
                    <div class="chat-info">
                        <p class="chat-name"><?= htmlspecialchars($room['full_name']) ?></p>
                        <p class="chat-preview" id="preview-<?= htmlspecialchars($room['room_id']) ?>">
                            <?= htmlspecialchars($room['last_message'] ?: 'Start chatting!') ?>
                        </p>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script>
        const evtSource = new EventSource("/pages/chat/stream.php");
        
        evtSource.onmessage = function(event) {
            try {
                const data = JSON.parse(event.data);
                if (data.type === 'new_message') {
                    const roomId = data.room_id;
                    const msg = data.message;
                    const previewEl = document.getElementById('preview-' + roomId);
                    const roomItem = document.getElementById('room-item-' + roomId);
                    
                    if (previewEl && roomItem) {
                        // update preview
                        let text = msg.message_text;
                        if (!text && msg.attachment_id) {
                            text = "📷 Attachment";
                        }
                        previewEl.textContent = text;
                        
                        // Bring to top
                        const chatList = document.getElementById('chat-list');
                        if (chatList.firstChild !== roomItem) {
                            chatList.insertBefore(roomItem, chatList.firstChild);
                        }
                    } else {
                        // A new room message arrived that we don't have listed, refresh
                        window.location.reload();
                    }
                }
            } catch (e) {
                console.error("Error parsing event", e);
            }
        };

        evtSource.onerror = function() {
            console.log("EventSource failed. Reconnecting...");
        };
    </script>
</body>
</html>