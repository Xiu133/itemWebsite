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

    // 綁定全選 checkbox 事件
    const selectAll = document.getElementById('select-all');
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.order-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = this.checked;
            });
            updateSelectedCount();
        });
    }

    // 綁定個別 checkbox 事件（使用事件委派）
    const tableBody = document.querySelector('.products-table tbody');
    if (tableBody) {
        tableBody.addEventListener('change', function(e) {
            if (e.target.classList.contains('order-checkbox')) {
                updateSelectedCount();
            }
        });
    }

    // 初始化批次選擇狀態
    updateSelectedCount();
});

// 全選/取消全選（保留給 inline onclick 使用）
window.toggleSelectAll = function(checkbox) {
    const checkboxes = document.querySelectorAll('.order-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateSelectedCount();
}

// 更新已選擇數量
window.updateSelectedCount = function() {
    const checkboxes = document.querySelectorAll('.order-checkbox:checked');
    const count = checkboxes.length;
    const countSpan = document.getElementById('selected-count');
    const batchBtn = document.getElementById('batch-create-btn');
    const selectAll = document.getElementById('select-all');

    if (countSpan) {
        countSpan.textContent = `已選擇 ${count} 筆`;
    }

    // 啟用/禁用批次出貨按鈕
    if (batchBtn) {
        if (count > 0) {
            batchBtn.disabled = false;
            batchBtn.style.opacity = '1';
            batchBtn.style.cursor = 'pointer';
        } else {
            batchBtn.disabled = true;
            batchBtn.style.opacity = '0.5';
            batchBtn.style.cursor = 'not-allowed';
        }
    }

    // 更新全選框狀態
    if (selectAll) {
        const allCheckboxes = document.querySelectorAll('.order-checkbox');
        if (allCheckboxes.length > 0) {
            selectAll.checked = checkboxes.length === allCheckboxes.length;
            selectAll.indeterminate = checkboxes.length > 0 && checkboxes.length < allCheckboxes.length;
        }
    }
}

// 批次建立物流單
window.batchCreateShipment = async function() {
    const checkboxes = document.querySelectorAll('.order-checkbox:checked');
    const orderIds = Array.from(checkboxes).map(cb => parseInt(cb.value));

    if (orderIds.length === 0) {
        alert('請先選擇要出貨的訂單');
        return;
    }

    if (!confirm(`確定要為 ${orderIds.length} 筆訂單建立物流單嗎？`)) {
        return;
    }

    const button = document.getElementById('batch-create-btn');
    if (button) {
        button.textContent = '處理中...';
        button.disabled = true;
    }

    try {
        const response = await fetch('/manage-logistics/batch-create-shipment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ order_ids: orderIds })
        });

        const data = await response.json();

        if (data.success) {
            let message = data.message;

            // 顯示成功的訂單
            if (data.data.success.length > 0) {
                message += '\n\n成功建立：';
                data.data.success.forEach(item => {
                    message += `\n- ${item.order_number}`;
                });
            }

            // 顯示失敗的訂單
            if (data.data.failed.length > 0) {
                message += '\n\n建立失敗：';
                data.data.failed.forEach(item => {
                    message += `\n- ${item.order_number}: ${item.message}`;
                });
            }

            alert(message);
            window.location.reload();
        } else {
            alert('批次處理失敗：' + (data.message || '未知錯誤'));
            if (button) {
                button.textContent = '批次建立物流單';
                button.disabled = false;
            }
        }
    } catch (error) {
        console.error('Error:', error);
        alert('批次處理失敗：網路錯誤，請稍後再試');
        if (button) {
            button.textContent = '批次建立物流單';
            button.disabled = false;
        }
    }
}

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
