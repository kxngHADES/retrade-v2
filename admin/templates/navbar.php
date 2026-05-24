<?php
$current_page = $_SERVER['REQUEST_URI'];
?>
<nav class="admin-nav">
    <div class="nav-brand-container">
        <a href="/dashboard/" class="nav-brand">
            <span class="nav-logo-text">ReTrade Admin</span>
        </a>
        <button id="mobile-toggle" class="mobile-toggle" aria-label="Toggle navigation">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>
    </div>
    
    <div id="nav-content" class="nav-content">
        <div class="nav-menu">
            <a href="/dashboard/" class="nav-link <?= strpos($current_page, 'dashboard') !== false ? 'active' : '' ?>">Dashboard</a>
            <a href="/pages/user-management/" class="nav-link <?= strpos($current_page, 'user-management') !== false ? 'active' : '' ?>">User Management</a>
            <a href="/pages/disputes/" class="nav-link <?= strpos($current_page, 'disputes') !== false ? 'active' : '' ?>">Disputes</a>
            <a href="/pages/escrow-control/" class="nav-link <?= strpos($current_page, 'escrow-control') !== false ? 'active' : '' ?>">Escrow</a>
            <a href="/pages/background-job/" class="nav-link <?= strpos($current_page, 'background-job') !== false ? 'active' : '' ?>">Mass Email</a>
            
            <?php if (isset($_SESSION['rbac_role']) && (int)$_SESSION['rbac_role'] === 3): ?>
                <a href="/pages/register/" class="nav-link <?= strpos($current_page, 'register') !== false ? 'active' : '' ?>">Add Admin</a>
            <?php endif; ?>
        </div>
        
        <div class="nav-actions">
            <button id="theme-toggle" class="btn-theme" title="Toggle Dark/Light Mode">
                <svg class="sun-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                <svg class="moon-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
            </button>

            <div class="user-profile">
                <span><?= htmlspecialchars($_SESSION['firstName']) ?></span>
                <span class="user-role"><?= $_SESSION['rbac_role'] == 3 ? 'SuperAdmin' : 'Admin' ?></span>
            </div>
            <a href="/logout.php" class="logout-link">Logout</a>
        </div>
    </div>
</nav>

<script>
    // Theme Toggle Logic
    const themeToggle = document.getElementById('theme-toggle');
    const mobileToggle = document.getElementById('mobile-toggle');
    const navContent = document.getElementById('nav-content');
    const htmlElement = document.documentElement;
    
    // Check for saved theme preference
    const savedTheme = localStorage.getItem('theme') || 'light';
    htmlElement.setAttribute('data-theme', savedTheme);
    
    themeToggle.addEventListener('click', () => {
        const currentTheme = htmlElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        htmlElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
    });

    mobileToggle.addEventListener('click', () => {
        navContent.classList.toggle('show');
    });
</script>