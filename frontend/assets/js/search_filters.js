document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.home-filter-toggle').forEach(function (button) {
        const targetId = button.dataset.target;
        if (!targetId) return;

        const panel = document.getElementById(targetId);
        if (!panel) return;

        button.setAttribute('aria-expanded', 'false');

        button.addEventListener('click', function () {
            const expanded = panel.classList.toggle('home-search-filters--expanded');
            button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            button.classList.toggle('home-filter-toggle--active', expanded);
        });
    });
});
