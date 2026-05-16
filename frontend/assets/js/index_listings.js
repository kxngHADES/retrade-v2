document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('see-more-btn');
    let currentShown = 20;

    if (btn) {
        btn.addEventListener('click', function() {
            const hiddenCards = document.querySelectorAll('.card.hidden, .home-card.hidden');
            const toShow = Array.from(hiddenCards).slice(0, 20); // Show next 20
            
            toShow.forEach(card => card.classList.remove('hidden'));
            currentShown += toShow.length;

            if(document.querySelectorAll('.card.hidden, .home-card.hidden').length === 0) {
                btn.classList.add('hidden');
                // In a real scenario, here you would fetch page 2 via ajax and append to grid
            }
        });
    }
});