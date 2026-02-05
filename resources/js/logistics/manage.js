// 物流管理 JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // 用戶下拉選單
    const userBtn = document.getElementById('user-btn');
    const userDropdown = document.getElementById('user-dropdown');

    if (userBtn && userDropdown) {
        userBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('active');
        });

        document.addEventListener('click', function() {
            userDropdown.classList.remove('active');
        });
    }
});

// 建立物流單
window.createShipment = async function(orderId, buttonElement) {
    if (!confirm('確定要建立物流單嗎？建立後訂單將標記為已完成。')) {
        return;
    }

    const button = buttonElement || document.querySelector(`button[onclick*="createShipment(${orderId}"]`);
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
            alert('物流單建立成功！訂單已標記為完成。\n物流編號：' + (data.data?.all_pay_logistics_id || '處理中'));
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
}

// 查詢物流狀態
window.queryStatus = async function(orderId) {
    const modal = document.getElementById('status-modal');
    const content = document.getElementById('status-content');

    modal.style.display = 'flex';
    content.innerHTML = '載入中...';

    try {
        const response = await fetch(`/manage-logistics/${orderId}/status`, {
            headers: {
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success) {
            const order = data.data.order;
            content.innerHTML = `
                <div style="margin-bottom: 1rem;">
                    <div style="font-weight: 600; margin-bottom: 0.5rem;">訂單資訊</div>
                    <div style="font-size: 0.875rem; color: #6b7280;">
                        訂單編號：${order.order_number}<br>
                        物流編號：${order.all_pay_logistics_id || '-'}
                    </div>
                </div>
                <div style="margin-bottom: 1rem;">
                    <div style="font-weight: 600; margin-bottom: 0.5rem;">物流狀態</div>
                    <div style="display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; background: #d1fae5; color: #065f46;">
                        ${order.logistics_status_text}
                    </div>
                </div>
                ${order.shipped_at ? `
                <div>
                    <div style="font-weight: 600; margin-bottom: 0.5rem;">出貨時間</div>
                    <div style="font-size: 0.875rem; color: #6b7280;">
                        ${order.shipped_at}
                    </div>
                </div>
                ` : ''}
            `;
        } else {
            content.innerHTML = `<div style="color: #dc2626;">查詢失敗：${data.message}</div>`;
        }
    } catch (error) {
        console.error('Error:', error);
        content.innerHTML = '<div style="color: #dc2626;">查詢失敗：網路錯誤</div>';
    }
}

// 更新物流狀態
window.updateStatus = async function(orderId) {
    const select = document.getElementById(`status-select-${orderId}`);
    const newStatus = select.value;

    const statusText = {
        'created': '已建立',
        'picked_up': '已取件',
        'in_transit': '運送中',
        'delivered': '已送達'
    };

    if (!confirm(`確定要將狀態更新為「${statusText[newStatus]}」嗎？`)) {
        return;
    }

    try {
        const response = await fetch(`/manage-logistics/${orderId}/update-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ status: newStatus })
        });

        const data = await response.json();

        if (data.success) {
            alert('狀態更新成功！');
            window.location.reload();
        } else {
            alert('更新失敗：' + (data.message || '未知錯誤'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('更新失敗：網路錯誤，請稍後再試');
    }
}

// 關閉彈窗
window.closeModal = function() {
    document.getElementById('status-modal').style.display = 'none';
}

// 點擊彈窗外部關閉
document.addEventListener('click', function(e) {
    const modal = document.getElementById('status-modal');
    if (e.target === modal) {
        window.closeModal();
    }
});
