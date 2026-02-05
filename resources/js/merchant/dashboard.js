// Merchant Dashboard JavaScript

// Toggle expandable cards
window.toggleCard = function(cardId) {
    const card = document.getElementById(cardId);
    if (card) {
        card.classList.toggle('expanded');
    }
};

// User dropdown menu
document.addEventListener('DOMContentLoaded', function() {
    const userBtn = document.getElementById('user-btn');
    const userDropdown = document.getElementById('user-dropdown');

    if (userBtn && userDropdown) {
        userBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('active');
        });

        document.addEventListener('click', function(e) {
            if (!userDropdown.contains(e.target) && !userBtn.contains(e.target)) {
                userDropdown.classList.remove('active');
            }
        });
    }

    // Close expanded cards when clicking outside
    document.addEventListener('click', function(e) {
        const expandableCards = document.querySelectorAll('.management-card.expandable.expanded');
        expandableCards.forEach(function(card) {
            if (!card.contains(e.target)) {
                card.classList.remove('expanded');
            }
        });
    });
});
