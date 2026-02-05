
    // User dropdown toggle
    const userBtn = document.getElementById('user-btn');
    const userDropdown = document.getElementById('user-dropdown');

    userBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        userDropdown.classList.toggle('active');
    });

    document.addEventListener('click', function(e) {
        if (!userDropdown.contains(e.target)) {
            userDropdown.classList.remove('active');
        }
    });

    // Confirm checkbox toggle delete button
    const confirmCheckbox = document.getElementById('confirm-delete');
    const deleteBtn = document.getElementById('btn-delete');

    confirmCheckbox.addEventListener('change', function() {
        deleteBtn.disabled = !this.checked;
    });

    // Header scroll effect
    window.addEventListener('scroll', function() {
        const header = document.querySelector('header');
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });
