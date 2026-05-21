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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="/assets/css/chat.css">
    <script src="/assets/js/global.js" defer></script>
</head>
<body class="chat-room-page">
    <?php require_once __DIR__ . '/../../../templates/partial/navbar.php'; ?>
    <main class="main-content" id="main-content">
        <div class="chat-room-inner">
            <header class="chat-room-header">
                <a href="/pages/chat/" class="chat-room-back">&#8592;</a>
                <h2><?= $fullName ?></h2>
                <button type="button" class="chat-room-action" id="show-report-modal" aria-label="Report User" title="Report User">
                    <span style="color: red; font-size: 1.2rem;">&#9888;</span>
                </button>
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
                            <div class="order-prompt">
                                <div class="order-prompt-header">
                                    <span class="order-prompt-title">Order</span>
                                </div>
                                <strong>Order Proposal Sent</strong>
                                <p>Item: <?= htmlspecialchars($listingName) ?><br>Price: R<?= htmlspecialchars($price) ?></p>
                            </div>
                        <?php else: ?>
                            <div class="order-prompt">
                                <div class="order-prompt-header">
                                    <span class="order-prompt-title">Order</span>
                                </div>
                                <strong>Order Proposal Received</strong>
                                <p>Item: <?= htmlspecialchars($listingName) ?><br>Price: R<?= htmlspecialchars($price) ?></p>
                                <button onclick="window.location.href='/pages/pay/initiate.php?amount=<?= urlencode($price) ?>&listing_id=<?= urlencode($listingId) ?>&order_type=marketplace&seller_uid=<?= urlencode($msg['sender_id']) ?>'">Pay Now</button>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <span><?= htmlspecialchars($msg['message_text']) ?></span>
                    <?php endif; ?>
                <?php endif; ?>
                
                <span class="time"><?= date('H:i', strtotime($msg['sent_at'])) ?></span>
            </div>
        <?php endforeach; ?>
    </div>

            <div class="chat-input-area">
                <button class="chat-action-btn order-button" id="show-order-modal" type="button" aria-label="Start an order">
                    <i data-lucide="package" class="order-action-icon" aria-hidden="true"></i>
                    <span class="order-action-label">Order</span>
                </button>
                <textarea id="message-input" class="chat-input" placeholder="Type a message..." rows="1" autocomplete="off"></textarea>
                <button id="send-btn" class="chat-send-btn" type="button" aria-label="Send message">
                    <span>➤</span>
                </button>
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

    <!-- Report Modal -->
    <div class="order-modal" id="report-modal" style="display:none; z-index: 1000;">
        <h3 style="color: red;">Report <?= $fullName ?></h3>
        <select id="report-reason" style="width: 100%; padding: 10px; border-radius: 5px; margin-bottom: 10px; border: 1px solid #ccc; box-sizing: border-box;">
            <option value="">Select a reason...</option>
            <option value="scamming">Scamming / Fraud</option>
            <option value="harassment">Harassment</option>
            <option value="spamming">Spamming</option>
            <option value="fake_delivery">Fake Delivery</option>
            <option value="other">Other</option>
        </select>
        <textarea id="report-description" placeholder="Additional details..." rows="3" style="width: 100%; padding: 10px; border-radius: 5px; margin-bottom: 10px; border: 1px solid #ccc; box-sizing: border-box;"></textarea>
        <div style="display: flex; gap: 10px;">
            <button id="submit-report-btn" style="flex: 1; background: red; color: white; border: none; padding: 10px; border-radius: 5px; cursor: pointer;">Submit Report</button>
            <button onclick="document.getElementById('report-modal').style.display='none'" style="flex: 1; background: #ccc; border: none; padding: 10px; border-radius: 5px; cursor: pointer;">Cancel</button>
        </div>
    </div>

    <script>
        const chatContainer = document.getElementById('chat-container');
        const sendBtn = document.getElementById('send-btn');
        const msgInput = document.getElementById('message-input');
        
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
                        html += `<div class="order-prompt"><div class="order-prompt-header"><span class="order-prompt-symbol">🛒</span><span class="order-prompt-title">Order</span></div><strong>Order Proposal Sent</strong><p>Item: ${listingName}<br>Price: R${price}</p></div>`;
                    } else {
                        html += `<div class="order-prompt"><div class="order-prompt-header"><span class="order-prompt-symbol">🛒</span><span class="order-prompt-title">Order</span></div><strong>Order Proposal Received</strong><p>Item: ${listingName}<br>Price: R${price}</p><button onclick="window.location.href='/pages/pay/initiate.php?amount=${price}&listing_id=${listingId}&order_type=marketplace&seller_uid=${msg.sender_id}'">Pay Now</button></div>`;
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

        const showOrderModalBtn = document.getElementById('show-order-modal');
        const orderModal = document.getElementById('order-modal');
        const createOrderBtn = document.getElementById('create-order-btn');
        const listingSelect = document.getElementById('listing-select');
        const customPriceInput = document.getElementById('custom-price');

        const showReportModalBtn = document.getElementById('show-report-modal');
        const reportModal = document.getElementById('report-modal');
        const submitReportBtn = document.getElementById('submit-report-btn');
        const otherUserId = "<?= htmlspecialchars($otherUser['uid'] ?? '') ?>";

        if (showReportModalBtn) {
            showReportModalBtn.addEventListener('click', () => {
                reportModal.style.display = 'block';
            });
        }

        if (submitReportBtn) {
            submitReportBtn.addEventListener('click', async () => {
                const reason = document.getElementById('report-reason').value;
                const desc = document.getElementById('report-description').value;

                if (!reason) {
                    alert("Please select a reason for reporting.");
                    return;
                }

                submitReportBtn.disabled = true;
                submitReportBtn.innerText = "Submitting...";

                try {
                    const res = await fetch('/pages/chat/api/report_user.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            target_user_id: otherUserId,
                            reason: reason,
                            description: desc
                        })
                    });

                    if (res.ok) {
                        alert("Report submitted successfully. Admins will review it shortly.");
                        reportModal.style.display = 'none';
                        document.getElementById('report-reason').value = '';
                        document.getElementById('report-description').value = '';
                    } else {
                        const data = await res.json();
                        alert("Failed to submit report: " + (data.error || 'Unknown error'));
                    }
                } catch(e) {
                    alert('Error: ' + e);
                } finally {
                    submitReportBtn.disabled = false;
                    submitReportBtn.innerText = "Submit Report";
                }
            });
        }

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
            
            if (!text) return;

            sendBtn.disabled = true;

            try {
                const res = await fetch('/pages/chat/api/send_message.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        room_id: roomId,
                        message_text: text
                    })
                });
                
                const responseData = await res.json();
                if (res.ok) {
                    msgInput.value = '';
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
