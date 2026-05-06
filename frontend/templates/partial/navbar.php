<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$isLoggedIn = isset($_SESSION['uid']);
$firstName = $isLoggedIn ? ($_SESSION['firstName'] ?? '') : '';
$lastName = $isLoggedIn ? ($_SESSION['lastName'] ?? '') : '';
$email = $isLoggedIn ? ($_SESSION['email'] ?? '') : '';
$avatar = $isLoggedIn ? ($_SESSION['profile_image_url'] ?? null) : null;
$initials = $isLoggedIn ? strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1)) : '';
?>
<link rel="stylesheet" href="/assets/css/navbar.css">
<script src="https://unpkg.com/lucide@latest"></script>

<!-- Mobile Menu Button -->
<button id="mobile-menu-btn" class="mobile-menu-btn">
    <i data-lucide="menu"></i>
</button>

<!-- Left Sidebar Navigation -->
<nav class="sidebar" id="desktop-sidebar">
    <div class="logo">
        <div class="logo-text" id="sidebar-logo">Re<span>Trade</span></div>
        <button id="desktop-toggle-btn" style="background:none; border:none; color:var(--text-primary); cursor:pointer; margin-left:auto; display:flex; align-items:center; justify-content:center; padding:4px;">
            <i data-lucide="menu"></i>
        </button>
    </div>

    <div class="nav-label">Menu</div>
    <div class="nav-group">
        <a href="/" class="nav-item <?= $_SERVER['REQUEST_URI'] == '/' ? 'active' : '' ?>">
            <div class="nav-icon"><i data-lucide="home"></i></div>
            <span class="sidebar-text"><?= trans('Home') ?? 'Home' ?></span>
        </a>
        <a href="/search.php" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], 'search.php') !== false ? 'active' : '' ?>">
            <div class="nav-icon"><i data-lucide="search"></i></div>
            <span class="sidebar-text"><?= trans('Search') ?? 'Search' ?></span>
        </a>
        <a href="/pages/chat/" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], 'chat') !== false ? 'active' : '' ?>">
            <div class="nav-icon"><i data-lucide="message-square"></i></div>
            <span class="sidebar-text"><?= trans('Chats') ?? 'Chats' ?></span>
        </a>
        <a href="/pages/my-listings/" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], 'my-listings') !== false ? 'active' : '' ?>">
            <div class="nav-icon"><i data-lucide="list"></i></div>
            <span class="sidebar-text"><?= trans('Listings') ?? 'Listings' ?></span>
        </a>
        <a href="/pages/shop/" class="nav-item <?= strpos($_SERVER['REQUEST_URI'], 'shop') !== false ? 'active' : '' ?>">
            <div class="nav-icon"><i data-lucide="store"></i></div>
            <span class="sidebar-text"><?= trans('Shop') ?? 'Shop' ?></span>
        </a>
    </div>

    <div class="divider"></div>
    <div class="nav-label">Preferences</div>
    <div class="nav-group">
        <?php $isSettings = strpos($_SERVER['REQUEST_URI'], 'settings.php') !== false; ?>
        <a href="/settings.php" class="nav-item <?= $isSettings ? 'active' : '' ?>">
            <div class="nav-icon"><i data-lucide="settings"></i></div>
            <span class="sidebar-text"><?= trans('Settings') ?? 'Settings' ?></span>
        </a>
    </div>

    <div class="nav-spacer"></div>

    <?php if ($isLoggedIn): ?>
        <a href="/pages/profile/" class="user-panel" style="text-decoration: none; display: flex; align-items: center; gap: 10px; width: 100%;">
            <div class="avatar">
                <?php if ($avatar): ?>
                    <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar">
                <?php else: ?>
                    <?= htmlspecialchars($initials) ?>
                <?php endif; ?>
            </div>
            <div class="user-info sidebar-text">
                <div class="user-name"><?= htmlspecialchars($firstName . ' ' . $lastName) ?></div>
                <div class="user-email"><?= htmlspecialchars($email) ?></div>
            </div>
        </a>
        <div style="display: flex; justify-content: center; width: 100%; margin-top: 10px;">
            <a href="/logout/" class="btn-logout" title="<?= trans('Log Out') ?? 'Log Out' ?>" style="width: 100%; display: flex; justify-content: center;">
                <i data-lucide="log-out"></i>
                <span class="sidebar-text" style="margin-left: 10px;"><?= trans('Log Out') ?? 'Log Out' ?></span>
            </a>
        </div>
    <?php else: ?>
        <div class="auth-panel sidebar-text">
            <a href="/pages/login/" class="btn-auth btn-login"><?= trans('Login') ?? 'Login' ?></a>
            <a href="/pages/register/" class="btn-auth btn-register"><?= trans('Register') ?? 'Register' ?></a>
        </div>
    <?php endif; ?>
</nav>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const sidebar = document.getElementById("desktop-sidebar");
        const logo = document.getElementById("sidebar-logo");
        const mainContent = document.getElementById("main-content");
        const mobileMenuBtn = document.getElementById("mobile-menu-btn");
        const toggleBtn = document.getElementById("desktop-toggle-btn");
        
        let isOpen = localStorage.getItem("sidebarOpen") !== "false";
        let isMobile = window.innerWidth <= 768;
        
        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            isOpen = !isOpen;
            localStorage.setItem("sidebarOpen", isOpen);
            updateSidebar();
        });

        mobileMenuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            isOpen = !isOpen;
            updateSidebar();
        });

        window.addEventListener('resize', () => {
            const newIsMobile = window.innerWidth <= 768;
            if (newIsMobile !== isMobile) {
                isMobile = newIsMobile;
                updateSidebar();
            }
        });
        
        function updateSidebar() {
            if (isMobile) {
                if (isOpen) {
                    sidebar.classList.add('mobile-open');
                    mobileMenuBtn.innerHTML = '<i data-lucide="x"></i>';
                } else {
                    sidebar.classList.remove('mobile-open');
                    mobileMenuBtn.innerHTML = '<i data-lucide="menu"></i>';
                }
                sidebar.style.width = '';
                logo.style.display = "block";
                document.querySelectorAll(".sidebar-text").forEach(el => el.style.display = "");
                if (mainContent) mainContent.style.marginLeft = '0';
                toggleBtn.style.display = 'none';
            } else {
                sidebar.classList.remove('mobile-open');
                toggleBtn.style.display = 'flex';
                mobileMenuBtn.style.display = 'none';
                if (isOpen) {
                    sidebar.style.width = 'var(--nav-w)';
                    logo.style.display = "block";
                    document.querySelectorAll(".sidebar-text").forEach(el => el.style.display = "");
                    if (mainContent) mainContent.style.marginLeft = 'var(--nav-w)';
                    toggleBtn.innerHTML = '<i data-lucide="panel-left-close"></i>';
                } else {
                    sidebar.style.width = '80px';
                    logo.style.display = "none";
                    document.querySelectorAll(".sidebar-text").forEach(el => el.style.display = "none");
                    if (mainContent) mainContent.style.marginLeft = '80px';
                    toggleBtn.innerHTML = '<i data-lucide="menu"></i>';
                }
            }
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
        
        // Initialize state on load
        if (isMobile) {
            isOpen = false;
        }
        updateSidebar();
    });
</script>
