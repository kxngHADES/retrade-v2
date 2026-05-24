<?php
require_once __DIR__ . '/../../config/bootstrap.php';
require_once __DIR__ . '/../../utils/protected_route.php';
require_once __DIR__ . '/../../lib/services/background_jobs.php';

use Lib\services\background_jobs;

$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = $_POST['subject'] ?? '';
    $content = $_POST['content'] ?? '';

    if (empty($subject) || empty($content)) {
        $error = "Subject and Content are required.";
    } else {
        $jobs = new background_jobs();
        $result = $jobs->triggerMassBroadcast($subject, $content);

        if ($result['success']) {
            $message = $result['message'];
        } else {
            $error = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mass Broadcast - Admin Panel</title>
    <link rel="stylesheet" href="../../assets/css/global.css">
    <link rel="stylesheet" href="../../assets/css/forms.css">
    <link rel="stylesheet" href="../../assets/css/background-job.css">
</head>
<body>
    <?php require_once __DIR__ . '/../../templates/navbar.php'; ?>

    <div class="broadcast-container">
        <div class="page-header">
            <h1 class="page-title">Mass Email Broadcast</h1>
        </div>

        <?php if($message): ?>
            <div class="alert alert-success">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <?= $message ?>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="alert alert-error">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?= $error ?>
            </div>
        <?php endif; ?>

        <div class="broadcast-grid">
            <div class="form-section">
                <form method="POST" class="broadcast-form-card">
                    <div class="form-group">
                        <label class="form-label">Email Subject</label>
                        <input type="text" name="subject" class="form-input" id="subject" placeholder="e.g., Important Security Update" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">HTML Content (Variable: $content)</label>
                        <p class="text-micro text-muted" style="margin-bottom: 8px;">Use the toolbar to insert tags. Content is wrapped in the ReTrade System Template.</p>
                        
                        <div class="editor-toolbar">
                            <button type="button" class="editor-btn" onclick="insertTag('h1')">H1</button>
                            <button type="button" class="editor-btn" onclick="insertTag('h2')">H2</button>
                            <button type="button" class="editor-btn" onclick="insertTag('p')">Paragraph</button>
                            <button type="button" class="editor-btn" onclick="insertTag('b')">Bold</button>
                            <button type="button" class="editor-btn" onclick="insertTag('i')">Italic</button>
                            <button type="button" class="editor-btn" onclick="insertTag('a', 'href=\"#\"')">Link</button>
                            <button type="button" class="editor-btn" onclick="insertTag('ul')">List</button>
                            <button type="button" class="editor-btn" onclick="insertTag('li')">Item</button>
                            <button type="button" class="editor-btn" onclick="insertTag('br')">Break</button>
                        </div>
                        <textarea name="content" class="content-editor" id="content" oninput="updatePreview()" placeholder="<h1>Hello!</h1> <p>We have some updates...</p>" required></textarea>
                    </div>
                    
                    <button type="submit" id="broadcast-btn" class="btn" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 10px;">
                        <svg id="btn-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polyline points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        <span id="btn-text">Trigger Broadcast Engine</span>
                    </button>
                </form>
            </div>

            <div class="preview-pane">
                <div class="preview-header">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    Live Broadcast Preview
                </div>
                <div class="preview-content-area">
                    <div class="preview-browser-top">
                        <div class="browser-dot"></div>
                        <div class="browser-dot"></div>
                        <div class="browser-dot"></div>
                    </div>
                    <div style="background:#f4f4f4; padding: 20px;">
                        <div style="max-width: 600px; margin: 0 auto; background: #fff; border: 1px solid #eee; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                            <div style="background-color: #FF6B00; color: white; padding: 15px; text-align: center;">
                                <h2 style="margin:0; color: #ffffff; font-family: sans-serif;">ReTrade Updates</h2>
                            </div>
                            <div id="preview-body" style="padding: 20px; min-height: 200px; font-family: sans-serif; line-height: 1.6; color: #333;">
                                <div style="color: #999; text-align: center; margin-top: 50px;">Start typing in the editor to see your broadcast theme preview...</div>
                            </div>
                            <div style="background-color: #f8f9fa; color: #6c757d; padding: 15px; text-align: center; font-size: 11px; font-family: sans-serif;">
                                &copy; <?= date('Y') ?> kxngHADES ReTrade. All rights reserved.<br>
                                Legal Unsubscribe links managed via profile.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.querySelector('.broadcast-form-card').addEventListener('submit', function(e) {
            const btn = document.getElementById('broadcast-btn');
            const icon = document.getElementById('btn-icon');
            const text = document.getElementById('btn-text');

            btn.disabled = true;
            text.textContent = 'Processing Broadcast...';
            
            // Replace airplane icon with a spinner
            icon.innerHTML = '<path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>';
            icon.classList.add('spinning');
        });

        function updatePreview() {
            const content = document.getElementById('content').value;
            const previewBody = document.getElementById('preview-body');
            if (content.trim() === '') {
                previewBody.innerHTML = '<div style="color: #999; text-align: center; margin-top: 50px;">Start typing in the editor to see your broadcast theme preview...</div>';
            } else {
                previewBody.innerHTML = content;
            }
        }

        function insertTag(tag, attrs = '') {
            const textarea = document.getElementById('content');
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const text = textarea.value;
            const selectedText = text.substring(start, end);
            
            let tagOpen = `<${tag}${attrs ? ' ' + attrs : ''}>`;
            let tagClose = `</${tag}>`;
            
            // Special handling for lists
            if (tag === 'ul') {
                tagOpen = "<ul>\n  <li>";
                tagClose = "</li>\n</ul>";
            }
            
            // Self closing tags
            if (tag === 'br') {
                tagClose = '';
            }

            const replacement = tagOpen + selectedText + tagClose;
            textarea.value = text.substring(0, start) + replacement + text.substring(end);
            
            // Focus and trigger preview
            textarea.focus();
            textarea.setSelectionRange(start + tagOpen.length, start + tagOpen.length + selectedText.length);
            updatePreview();
        }
    </script>
</body>
</html>