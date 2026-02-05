
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

        // Toggle product status
        document.querySelectorAll('.toggle-status').forEach(function(toggle) {
            toggle.addEventListener('change', function() {
                const productId = this.dataset.productId;
                const isActive = this.checked;
                const statusText = this.closest('td').querySelector('.status-text');

                fetch(`/my-products/${productId}/toggle-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ is_active: isActive })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        statusText.textContent = isActive ? '上架中' : '已下架';
                        statusText.className = 'status-text ' + (isActive ? 'status-active' : 'status-inactive');
                    } else {
                        toggle.checked = !isActive;
                        alert('狀態更新失敗，請重試');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    toggle.checked = !isActive;
                    alert('狀態更新失敗，請重試');
                });
            });
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
   