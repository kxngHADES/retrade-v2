<?php
require_once __DIR__ . '/../../../config/bootstrap.php';
require_once __DIR__ . '/../../../utils/protected_route.php';

use Lib\services\Chat_services;

$roomId = $_GET['room_id'] ?? null;
$uid = $_SESSION['uid'] ?? null;

if (!$roomId || !$uid) {
    header("Location: /pages/chat/");
    exit;
}

$chatService = new Chat_services();
$roomExists = $chatService->getRoom($roomId);
if (!$roomExists) {
    header("Location: /pages/chat/");
    exit;
}

$otherUser = $chatService->getRoomOtherUser($roomId, $uid);
$fullName = "Unknown User";
if ($otherUser) {
    // Determine who is the buyer/seller logically or just load current user's listings to propose
    $fullName = htmlspecialchars($otherUser['firstName'] . " " . $otherUser['lastName']);
}

require_once __DIR__ . '/../../../lib/services/ApiService.php';
$apiService = new \Lib\services\ApiService();
$myListings = $apiService->get_user_listings($uid);

$messages = $chatService->getRoomMessages($roomId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $fullName ?> - Retrade Chat</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #e5e5e5; display: flex; flex-direction: column; height: 100vh; }
        header { background: #075E54; color: white; padding: 15px; display: flex; align-items: center; gap: 10px; }
        header a { color: white; text-decoration: none; font-size: 20px; }
        header h2 { margin: 0; font-size: 1.2em; }
        .chat-container { flex-grow: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 10px; }
        .message { max-width: 70%; padding: 10px 15px; border-radius: 10px; font-size: 0.95em; line-height: 1.4; word-wrap: break-word; }
        .message.sent { background: #DCF8C6; margin-left: auto; border-bottom-right-radius: 0; }
        .message.received { background: white; margin-right: auto; border-bottom-left-radius: 0; }
        .message .time { font-size: 0.7em; color: #999; margin-top: 5px; text-align: right; display: block; }
        .message img, .message video { max-width: 100%; border-radius: 5px; margin-bottom: 5px; }
        .input-area { background: #f0f0f0; padding: 10px; display: flex; align-items: center; gap: 10px; }
        .input-area input[type="text"] { flex-grow: 1; padding: 12px; border: 1px solid #ccc; border-radius: 20px; outline: none; }
        .input-area button { background: #128C7E; color: white; border: none; padding: 10px 20px; border-radius: 20px; cursor: pointer; }
        .input-area button:hover { background: #075E54; }
        .file-btn { background: #bbb; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: white; font-weight: bold; font-size: 20px; }
        .order-btn { background: #ff9800; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; color: white; font-weight: bold; font-size: 20px; border: none; }
        #file-input { display: none; }
        .order-modal { display: none; position: fixed; bottom: 80px; left: 10px; width: 300px; background: white; border: 1px solid #ccc; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); padding: 15px; }
        .order-modal h3 { margin-top: 0; }
        .order-modal select { width: 100%; padding: 10px; border-radius: 5px; margin-bottom: 10px; }
        .order-modal button { background: #ff9800; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer; width: 100%; }
        .order-prompt { border: 1px solid #ccc; background: #fff3e0; padding: 10px; border-radius: 5px; margin-top: 5px; text-align: center; }
        .order-prompt button { margin-top: 10px; }
    </style>
</head>
<body>
    <header>
        <a href="/pages/chat/">&#8592;</a>
        <h2><?= $fullName ?></h2>
    </header>
    
    <div class="chat-container" id="chat-container">
        <?php foreach ($messages as $msg): ?>
            <?php $isSent = ($msg['sender_id'] === $uid); ?>
            <div class="message <?= $isSent ? 'sent' : 'received' ?>">
                <?php if ($msg['attachment_id']): ?>
                    <?php if (preg_match('/^image/', $msg['file_type'])): ?>
                        <img src="<?= htmlspecialchars($msg['attachment_url']) ?>" alt="Attachment" />
                    <?php elseif (preg_match('/^video/', $msg['file_type'])): ?>
                        <video src="<?= htmlspecialchars($msg['attachment_url']) ?>" controls></video>
                    <?php else: ?>
                        <a href="<?= htmlspecialchars($msg['attachment_url']) ?>" target="_blank">Download <?= htmlspecialchars($msg['file_type']) ?></a>
                        <br>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php if ($msg['message_text']): ?>
                    <?php if (strpos($msg['message_text'], '[ORDER_PROPOSAL:') === 0): ?>
                        <?php 
                        $parts = explode(':', rtrim($msg['message_text'], ']'));
                        $listingId = $parts[1] ?? '';
                        $price = $parts[2] ?? '';
                        $listingName = isset($parts[3]) ? urldecode($parts[3]) : 'Item';
                        ?>
                        <?php if ($isSent): ?>
                            <div class="order-prompt"><b>Order Proposal Sent</b><br>Item: <?= htmlspecialchars($listingName) ?><br>Price: R<?= htmlspecialchars($price) ?></div>
                        <?php else: ?>
                            <div class="order-prompt"><b>Order Proposal Received</b><br>Item: <?= htmlspecialchars($listingName) ?><br>Price: R<?= htmlspecialchars($price) ?><br><button onclick="window.location.href='/pages/pay/initiate.php?amount=<?= urlencode($price) ?>&listing_id=<?= urlencode($listingId) ?>&order_type=marketplace&seller_uid=<?= urlencode($msg['sender_id']) ?>'">Pay Now</button></div>
                        <?php endif; ?>
                    <?php else: ?>
                        <span><?= htmlspecialchars($msg['message_text']) ?></span>
                    <?php endif; ?>
                <?php endif; ?>
                
                <span class="time"><?= date('H:i', strtotime($msg['sent_at'])) ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="input-area">
        <button class="order-btn" id="show-order-modal">
            <i data-lucide="package" width="20" height="20"></i>
        </button>
        <label class="file-btn" for="file-input">+</label>
        <input type="file" id="file-input" />
        <input type="text" id="message-input" placeholder="Type a message..." autocomplete="off">
        <button id="send-btn">Send</button>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Order Modal -->
    <div class="order-modal" id="order-modal">
        <h3>Create Order for Listing</h3>
        <?php if (!empty($myListings['listings'] ?? $myListings)): ?>
        <select id="listing-select">
            <?php 
            $listArr = $myListings['listings'] ?? $myListings;
            foreach ($listArr as $listing): 
            ?>
                <option value="<?= htmlspecialchars($listing['_id']) ?>" data-price="<?= htmlspecialchars($listing['price']) ?>" data-name="<?= htmlspecialchars($listing['name']) ?>">
                    <?= htmlspecialchars($listing['name']) ?> (R<?= htmlspecialchars($listing['price']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <input type="number" id="custom-price" placeholder="Set Price" min="0" step="0.01" style="width: 100%; padding: 10px; border-radius: 5px; margin-bottom: 10px; border: 1px solid #ccc; box-sizing: border-box;" />
        <button id="create-order-btn">Send Order Proposal</button>
        <?php else: ?>
        <p>You have no listings to sell.</p>
        <button onclick="document.getElementById('order-modal').style.display='none'">Close</button>
        <?php endif; ?>
    </div>

    <script>
        const chatContainer = document.getElementById('chat-container');
        const sendBtn = document.getElementById('send-btn');
        const msgInput = document.getElementById('message-input');
        const fileInput = document.getElementById('file-input');
        
        const roomId = "<?= htmlspecialchars($roomId) ?>";
        const currentUid = "<?= htmlspecialchars($uid) ?>";

        function scrollToBottom() {
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
        scrollToBottom();

        // Function to create message element
        function appendMessage(msg, isSent) {
            const div = document.createElement('div');
            div.className = `message ${isSent ? 'sent' : 'received'}`;
            
            let html = '';
            if (msg.attachment_url) {
                if (msg.file_type.startsWith('image/')) {
                    html += `<img src="${msg.attachment_url}" alt="Attachment" />`;
                } else if (msg.file_type.startsWith('video/')) {
                    html += `<video src="${msg.attachment_url}" controls></video>`;
                } else {
                    html += `<a href="${msg.attachment_url}" target="_blank">Download Document</a><br>`;
                }
            }
            
            if (msg.message_text) {
                if (msg.message_text.startsWith('[ORDER_PROPOSAL:')) {
                    const parts = msg.message_text.replace(']', '').split(':');
                    const listingId = parts[1];
                    const price = parts[2];
                    const listingName = parts[3] ? decodeURIComponent(parts[3]) : 'Item';
                    
                    if (isSent) {
                        html += `<div class="order-prompt"><b>Order Proposal Sent</b><br>Item: ${listingName}<br>Price: R${price}</div>`;
                    } else {
                        html += `<div class="order-prompt"><b>Order Proposal Received</b><br>Item: ${listingName}<br>Price: R${price}<br><button onclick="window.location.href='/pages/pay/initiate.php?amount=${price}&listing_id=${listingId}&order_type=marketplace&seller_uid=${msg.sender_id}'">Pay Now</button></div>`;
                    }
                } else {
                    const textNode = document.createTextNode(msg.message_text);
                    const wrapper = document.createElement('span');
                    wrapper.innerText = msg.message_text;
                    html += wrapper.outerHTML;
                }
            }
            
            let timeStr = new Date(msg.sent_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            html += `<span class="time">${timeStr}</span>`;
            
            div.innerHTML = html;
            chatContainer.appendChild(div);
            scrollToBottom();
        }

        async function uploadFile(file) {
            const timestamp = Date.now();
            const ext = file.name.split('.').pop();
            const path = `${roomId}/${timestamp}.${ext}`;
            
            const formData = new FormData();
            formData.append('file', file);
            
            // upload.php endpoint expects a `path` query param
            const res = await fetch(`/lib/services/upload.php?path=${encodeURIComponent(path)}`, {
                method: 'POST',
                body: formData
            });
            const data = await res.json();
            if (!res.ok) throw new Error(data.error || 'Upload failed');
            return data.url;
        }

        const showOrderModalBtn = document.getElementById('show-order-modal');
        const orderModal = document.getElementById('order-modal');
        const createOrderBtn = document.getElementById('create-order-btn');
        const listingSelect = document.getElementById('listing-select');
        const customPriceInput = document.getElementById('custom-price');

        if (showOrderModalBtn) {
            showOrderModalBtn.addEventListener('click', () => {
                orderModal.style.display = orderModal.style.display === 'block' ? 'none' : 'block';
                if (orderModal.style.display === 'block' && listingSelect && listingSelect.options.length > 0 && customPriceInput) {
                    customPriceInput.value = listingSelect.options[listingSelect.selectedIndex].getAttribute('data-price');
                }
            });
        }

        if (listingSelect && customPriceInput) {
            listingSelect.addEventListener('change', () => {
                customPriceInput.value = listingSelect.options[listingSelect.selectedIndex].getAttribute('data-price');
            });
        }

        if (createOrderBtn) {
            createOrderBtn.addEventListener('click', async () => {
                if (!listingSelect) return;
                const option = listingSelect.options[listingSelect.selectedIndex];
                const listingId = option.value;
                const listingName = option.getAttribute('data-name');
                const price = customPriceInput ? customPriceInput.value : option.getAttribute('data-price');

                if (!price || price < 0) {
                    alert("Please enter a valid price.");
                    return;
                }

                const proposalText = `[ORDER_PROPOSAL:${listingId}:${price}:${encodeURIComponent(listingName)}]`;

                createOrderBtn.disabled = true;
                try {
                    // Send message via existing endpoint
                    const res = await fetch('/pages/chat/api/send_message.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            room_id: roomId,
                            message_text: proposalText
                        })
                    });
                    if (res.ok) {
                        orderModal.style.display = 'none';
                    } else {
                        alert('Failed to send order proposal');
                    }
                } catch(e) {
                    alert('Error: ' + e);
                } finally {
                    createOrderBtn.disabled = false;
                }
            });
        }

        async function sendMessage() {
            const text = msgInput.value.trim();
            const file = fileInput.files[0];
            
            if (!text && !file) return;

            // Optimistic UI could be added here, but going simple for now
            sendBtn.disabled = true;

            try {
                let attachmentUrl = null;
                let fileType = null;

                if (file) {
                    attachmentUrl = await uploadFile(file);
                    fileType = file.type || 'application/octet-stream';
                }

                const res = await fetch('/pages/chat/api/send_message.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        room_id: roomId,
                        message_text: text,
                        attachment_url: attachmentUrl,
                        file_type: fileType
                    })
                });
                
                const responseData = await res.json();
                if (res.ok) {
                    msgInput.value = '';
                    fileInput.value = '';
                    // Local append handled by SSE mostly, but to ensure instant feedback:
                    // SSE will reflect it back, so we skip appending manually for now to avoid duplicates.
                } else {
                    alert('Error sending message: ' + (responseData.error || ''));
                }
            } catch (err) {
                alert(err.message);
                console.error(err);
            } finally {
                sendBtn.disabled = false;
            }
        }

        sendBtn.addEventListener('click', sendMessage);
        msgInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendMessage();
        });

        // Setup real-time updates via EventSource
        const evtSource = new EventSource("/pages/chat/stream.php");
        evtSource.onmessage = function(event) {
            try {
                const data = JSON.parse(event.data);
                if (data.type === 'new_message' && data.room_id === roomId) {
                    const msg = data.message;
                    // append it 
                    appendMessage(msg, msg.sender_id === currentUid);
                }
            } catch (e) {
                console.error("SSE parse error", e);
            }
        };

        // Initialize Lucide icons
        lucide.createIcons();
    </script>
</body>
</html>
