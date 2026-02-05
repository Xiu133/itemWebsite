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

// Update order status
window.updateOrderStatus = function(orderId, selectElement) {
    const newStatus = selectElement.value;

    fetch(`/manage-orders/${orderId}/status`, {
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

// 建立物流單
window.createLogisticsShipment = async function(orderId, buttonElement) {
    if (!confirm('確定要建立物流單嗎？建立後訂單將標記為已完成。')) {
        return;
    }

    const button = buttonElement || document.getElementById('create-shipment-btn');
    if (button) {
        button.textContent = '處理中...';
        button.disabled = true;
    }

    try {
        const response = await fetch(`/manage-logistics/${orderId}/create-shipment`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success) {
            alert('物流單建立成功！訂單已標記為完成。');
            window.location.reload();
        } else {
            alert('建立失敗：' + (data.message || '未知錯誤'));
            if (button) {
                button.textContent = '建立物流單';
                button.disabled = false;
            }
        }
    } catch (error) {
        console.error('Error:', error);
        alert('建立失敗：網路錯誤，請稍後再試');
        if (button) {
            button.textContent = '建立物流單';
            button.disabled = false;
        }
    }
};

// 查詢物流狀態
window.queryLogisticsStatus = async function(orderId) {
    try {
        const response = await fetch(`/manage-logistics/${orderId}/status`, {
            headers: {
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success) {
            alert('物流狀態：' + data.data.order.logistics_status_text);
            window.location.reload();
        } else {
            alert('查詢失敗：' + (data.message || '未知錯誤'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('查詢失敗：網路錯誤');
    }
};
