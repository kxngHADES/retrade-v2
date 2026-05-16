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
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="/assets/css/chat.css">
    <script src="/assets/js/global.js" defer></script>
</head>
<body>
    <?php require_once __DIR__ . '/../../templates/partial/navbar.php'; ?>
    <main class="main-content" id="main-content">
        <div class="chat-page">
            <section class="chat-panel">
                <div class="chat-header">
                    <h1 class="chat-title">Chats</h1>
                </div>

                <div class="chat-list" id="chat-list">
                    <?php if (empty($rooms)): ?>
                        <div class="chat-empty">
                            <p>No chats found yet.</p>
                            <p class="chat-empty-note">Once someone messages you, the conversation will appear here.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($rooms as $room): ?>
                            <a href="/pages/chat/room/?room_id=<?= urlencode($room['room_id']) ?>" class="chat-item" id="room-item-<?= htmlspecialchars($room['room_id']) ?>">
                                <div class="chat-avatar">
                                    <?php
                                    if (!empty($room['profile_image_url'])): ?>
                                        <img src="<?= htmlspecialchars($room['profile_image_url']) ?>" alt="<?= htmlspecialchars($room['full_name'] ?: 'Chat user') ?>">
                                    <?php else:
                                        $initials = '';
                                        if (!empty($room['full_name'])) {
                                            $parts = explode(' ', trim($room['full_name']));
                                            $initials = strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
                                        }
                                        echo htmlspecialchars($initials ?: 'RT');
                                    endif;
                                    ?>
                                </div>
                                <div class="chat-item-content">
                                    <div class="chat-item-top">
                                        <p class="chat-name"><?= htmlspecialchars($room['full_name'] ?: 'Unknown') ?></p>
                                        <?php if (!empty($room['updated_at'])): ?>
                                            <span class="chat-time"><?= htmlspecialchars(date('M j', strtotime($room['updated_at']))) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="chat-preview" id="preview-<?= htmlspecialchars($room['room_id']) ?>">
                                        <?php if (!empty($room['last_message']) || !empty($room['attachment_id'])): ?>
                                            <?php if (!empty($room['attachment_id'])): ?>
                                                <span class="chat-preview-icon" aria-hidden="true">📷</span>
                                            <?php endif; ?>
                                            <span><?= htmlspecialchars($room['last_message'] ?: 'Sent an attachment') ?></span>
                                        <?php else: ?>
                                            <span>Start chatting!</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if (!empty($room['unread_count']) && $room['unread_count'] > 0): ?>
                                    <div class="chat-unread"><?= (int)$room['unread_count'] ?></div>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>

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
                        let text = msg.message_text;
                        if (!text && msg.attachment_id) {
                            text = "📷 Attachment";
                        }
                        previewEl.textContent = text;

                        const chatList = document.getElementById('chat-list');
                        if (chatList.firstChild !== roomItem) {
                            chatList.insertBefore(roomItem, chatList.firstChild);
                        }
                    } else {
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
