<?php
session_start();
if (isset($_POST['lang']) && in_array($_POST['lang'], ['af', 'en', 'tn', 'ts', 've', 'xh', 'zu'])) {
	$_SESSION['lang'] = $_POST['lang'];
	header("Location: " . $_SERVER['PHP_SELF']);
	exit;
}
require_once __DIR__ . '/config/bootstrap.php';

$isLoggedIn = isset($_SESSION['uid']);

?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($_SESSION['lang'] ?? 'en'); ?>">
<head>
	<meta charset="utf-8"/>
	<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
	<title><?= trans('Settings') ?> - ReTrade</title>
	<script>
		(function() {
			var t = localStorage.getItem('theme');
			if (t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
				document.documentElement.setAttribute('data-theme', 'dark');
			} else {
				document.documentElement.removeAttribute('data-theme');
			}
		})();
	</script>
	<link rel="stylesheet" href="/assets/css/global.css">
	<link rel="stylesheet" href="/assets/css/settings.css">
</head>

<body class="antialiased min-h-screen flex" style="background-color: var(--bg);">
	
	<!-- Include the Navbar -->
	<?php include __DIR__ . '/templates/partial/navbar.php'; ?>

	<div id="main-content" class="main-content min-h-screen relative overflow-hidden transition-all duration-300">
		<main class="w-full h-full overflow-y-auto pt-[40px] pb-[64px] md:pt-0 md:pb-0 flex-grow flex flex-col content-center-wrapper" style="background-color: transparent;">
			<div class="settings-container">
				
				<!-- Header -->
				<div class="settings-page-header border-b border-light-border">
					<h1 class="text-display font-display"><?= trans('Settings') ?></h1>
				</div>

				<div class="flex flex-col settings-content-area">
					
					<!-- Appearance Section -->
					<section class="settings-section border-b border-light-border">
						<div class="settings-section-inner">
							<h2 class="text-h2 font-h2 mb-space-4"><?= trans('Appearance') ?></h2>
							<div class="appearance-toggle-group">
								<button class="toggle-btn" data-theme-val="light" onclick="setThemeSelection('light')"><?= trans('Light') ?></button>
								<button class="toggle-btn" data-theme-val="dark" onclick="setThemeSelection('dark')"><?= trans('Dark') ?></button>
								<button class="toggle-btn" data-theme-val="system" onclick="setThemeSelection('system')"><?= trans('System') ?></button>
							</div>
						</div>
					</section>

					<!-- Language Section -->
					<section class="settings-section border-b border-light-border">
						<div class="settings-section-inner-narrow">
							<form action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST" id="lang-form" class="w-full">
								<label for="lang-select" class="list-item-btn w-full py-space-5 flex items-center justify-between text-left hover:bg-surface-bright transition-colors px-2 -mx-2 cursor-pointer">
									<span class="text-body font-body text-on-surface"><?= trans('Language') ?></span>
									<div class="flex items-center gap-space-2 text-light-text-secondary">
										<select name="lang" id="lang-select" onchange="document.getElementById('lang-form').submit()" class="bg-transparent border-none outline-none text-right text-body-sm font-body-sm cursor-pointer appearance-none" style="color: var(--text-primary); appearance: none; -webkit-appearance: none; background: transparent; border: none; outline: none; cursor: pointer;">
											<option value="en" <?= ($_SESSION['lang'] ?? 'en') == 'en' ? 'selected' : '' ?>>English</option>
											<option value="af" <?= ($_SESSION['lang'] ?? 'en') == 'af' ? 'selected' : '' ?>>Afrikaans</option>
											<option value="tn" <?= ($_SESSION['lang'] ?? 'en') == 'tn' ? 'selected' : '' ?>>Setswana</option>
											<option value="ts" <?= ($_SESSION['lang'] ?? 'en') == 'ts' ? 'selected' : '' ?>>Xitsonga</option>
											<option value="ve" <?= ($_SESSION['lang'] ?? 'en') == 've' ? 'selected' : '' ?>>Tshivenda</option>
											<option value="xh" <?= ($_SESSION['lang'] ?? 'en') == 'xh' ? 'selected' : '' ?>>isiXhosa</option>
											<option value="zu" <?= ($_SESSION['lang'] ?? 'en') == 'zu' ? 'selected' : '' ?>>isiZulu</option>
										</select>
										<i data-lucide="chevron-right" class="w-4 h-4 text-text-secondary" style="width: 16px; height: 16px; color: var(--text-primary);"></i>
									</div>
								</label>
							</form>
						</div>
					</section>

					<!-- Account Section -->
					<section class="settings-section border-b border-light-border">
						<div class="settings-section-inner-narrow py-space-4">
							<h2 class="text-label font-label text-light-text-muted uppercase tracking-wider mb-space-2"><?= trans('Account') ?></h2>
								<a href="/pages/profile" class="list-item-btn w-full py-space-3 flex items-center justify-between text-left hover:bg-surface-bright transition-colors px-2 -mx-2">
									<span class="text-body font-body text-on-surface"><?= trans('Edit Profile') ?></span>
									<span class="text-light-text-secondary">
										<i data-lucide="chevron-right" class="w-4 h-4 text-text-secondary" style="width: 16px; height: 16px; color: var(--text-primary);"></i>
									</span>
								</a>
								<a href="https://admin.retrade.ndaedzo.com" target="_blank" rel="noopener noreferrer" class="list-item-btn w-full py-space-3 flex items-center justify-between text-left hover:bg-surface-bright transition-colors px-2 -mx-2">
									<span class="text-body font-body text-on-surface"><?= trans('Admin Dashboard') ?></span>
									<span class="text-light-text-secondary">
										<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
									</span>
								</a>
						</div>
					</section>

					<!-- About Section -->
					<section class="settings-section border-b border-light-border">
						<div class="settings-section-inner-narrow py-space-4">
							<h2 class="text-label font-label text-light-text-muted uppercase tracking-wider mb-space-2"><?= trans('About') ?></h2>
							<div class="py-space-3 flex items-center justify-between px-2">
								<span class="text-body font-body text-on-surface"><?= trans('Version') ?></span>
								<span class="text-body-sm font-body-sm text-light-text-secondary">0.0.1 Beta</span>
							</div>
							<a href="/Terms-of-service.php" class="list-item-btn w-full py-space-3 flex items-center justify-between text-left hover:bg-surface-bright transition-colors px-2 -mx-2">
								<span class="text-body font-body text-on-surface"><?= trans('Terms of Service') ?></span>
								<span class="text-light-text-secondary">
									<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
								</span>
								</a>
								<a href="/privacy-policy.php" class="list-item-btn w-full py-space-3 flex items-center justify-between text-left hover:bg-surface-bright transition-colors px-2 -mx-2">
									<span class="text-body font-body text-on-surface"><?= trans('Privacy Policy') ?></span>
									<span class="text-light-text-secondary">
									<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
								</span>
							</a>
						</div>
					</section>



					<?php if ($isLoggedIn): ?>
					<section class="settings-section my-space-8 border-none">
							<div class="settings-section-inner flex justify-center max-w-sm mx-auto">
								<a href="/logout/" class="btn-danger w-full max-w-[280px] py-space-3 flex items-center justify-center text-error hover:bg-error-container/20 transition-colors border border-error-container/50 hover:border-error-container rounded-lg">
									<span class="text-body font-body font-medium"><?= trans('Log Out') ?></span>
								</a>
							</div>
						</section>
						<?php endif; ?>
					</div>
				</div>
			</main>
		</div>
	<script>
		function updateActiveToggle() {
			const savedTheme = localStorage.getItem('theme') || 'system';
			document.querySelectorAll('.toggle-btn').forEach(btn => {
				btn.classList.remove('active');
				if (btn.getAttribute('data-theme-val') === savedTheme) {
					btn.classList.add('active');
				}
			});
		}

		function setThemeSelection(mode) {
			localStorage.setItem('theme', mode);
			
			if (mode === 'dark') {
				document.documentElement.setAttribute('data-theme', 'dark');
			} else if (mode === 'light') {
				document.documentElement.removeAttribute('data-theme');
			} else {
				// System mode
				if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
					document.documentElement.setAttribute('data-theme', 'dark');
				} else {
					document.documentElement.removeAttribute('data-theme');
				}
			}
			updateActiveToggle();
		}

		// Listen for system theme changes if set to system
		window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
			if (localStorage.getItem('theme') === 'system') {
				if (e.matches) {
					document.documentElement.setAttribute('data-theme', 'dark');
				} else {
					document.documentElement.removeAttribute('data-theme');
				}
			}
		});

		document.addEventListener('DOMContentLoaded', updateActiveToggle);
	</script>
	<script>
		// Removed local lucide.createIcons() as it is now handled in navbar.php
	</script>
</body>
</html>