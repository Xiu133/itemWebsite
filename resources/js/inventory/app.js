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

// Inventory adjustment functions
window.adjustStock = function(productId, direction) {
    const input = document.getElementById('adjust-' + productId);
    const amount = parseInt(input.value) || 1;
    const change = direction * amount;

    fetch(`/inventory/${productId}/adjust`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ change: change })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('調整失敗');
        }
    })
    .catch(() => alert('調整失敗'));
};

window.openAdjustModal = function(productId, productName, currentStock) {
    document.getElementById('modal-product-id').value = productId;
    document.getElementById('modal-product-name').textContent = productName;
    document.getElementById('modal-current-stock').textContent = currentStock;
    document.getElementById('modal-change').value = '';
    document.getElementById('modal-reason').value = '';
    document.getElementById('adjust-modal').style.display = 'flex';
};

window.closeModal = function() {
    document.getElementById('adjust-modal').style.display = 'none';
};

window.submitAdjust = function() {
    const productId = document.getElementById('modal-product-id').value;
    const change = parseInt(document.getElementById('modal-change').value);
    const reason = document.getElementById('modal-reason').value;

    if (isNaN(change) || change === 0) {
        alert('請輸入有效的調整數量');
        return;
    }

    fetch(`/inventory/${productId}/adjust`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ change: change, reason: reason })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('調整失敗');
        }
    })
    .catch(() => alert('調整失敗'));
};

// Close modal when clicking outside
document.getElementById('adjust-modal')?.addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
