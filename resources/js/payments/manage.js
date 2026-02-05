// User dropdown toggle
const userBtn = document.getElementById('user-btn');
const userDropdown = document.getElementById('user-dropdown');

if (userBtn && userDropdown) {
    userBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        userDropdown.classList.toggle('active');
    });

    document.addEventListener('click', function(e) {
        if (!userDropdown.contains(e.target)) {
            userDropdown.classList.remove('active');
        }
    });
}

// Update payment status
window.updatePaymentStatus = function(paymentId, selectElement) {
    const newStatus = selectElement.value;

    fetch(`/manage-payments/${paymentId}/status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('狀態更新失敗');
        }
    })
    .catch(() => alert('狀態更新失敗'));
};
