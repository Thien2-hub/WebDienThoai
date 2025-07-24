function displayOrderHistory() {
    const container = document.getElementById('order-history-container');
    const orders = JSON.parse(localStorage.getItem('orders') || '[]');
    
    if (orders.length === 0) {
        container.innerHTML = '<div class="empty-orders">Bạn chưa có đơn hàng nào!</div>';
        return;
    }
    
    let html = '';
    orders.forEach(order => {
        let statusClass = '';
        switch(order.status.toLowerCase()) {
            case 'chờ xử lý': statusClass = 'pending'; break;
            case 'đang giao': statusClass = 'shipping'; break;
            case 'đã hoàn thành': statusClass = 'completed'; break;
            case 'đã hủy': statusClass = 'cancelled'; break;
            default: statusClass = '';
        }
        
        let itemsHtml = '';
        order.items.forEach(item => {
            const imagePath = item.image || 'anh/default-product.png';
            
            itemsHtml += `
                <div class="order-item">
                    <img src="${imagePath}" alt="${item.name}" class="order-item-image">
                    <div class="order-item-details">
                        <div class="order-item-name">${item.name}</div>
                        <div class="order-item-price">${formatCurrency(item.price)} x ${item.quantity}</div>
                    </div>
                </div>
            `;
        });
        
        const orderDate = new Date(order.date);
        const formattedDate = orderDate.toLocaleDateString('vi-VN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        html += `
            <div class="order-card" data-id="${order.id}">
                <div class="order-header">
                    <div class="order-id">Đơn hàng #${order.id}</div>
                    <div class="order-date">${formattedDate}</div>
                    <div class="order-status ${statusClass}">${order.status}</div>
                </div>
                <div class="order-items">
                    ${itemsHtml}
                </div>
                <div class="order-footer">
                    <div class="order-total">
                        <span>Tổng tiền:</span>
                        <span class="total-amount">${formatCurrency(order.total)}</span>
                    </div>
                    <div class="order-actions">
                        ${order.status === 'Chờ xử lý' ? 
                            `<button class="cancel-order-btn" data-id="${order.id}">Hủy đơn hàng</button>` : ''}
                        <button class="view-order-btn" data-id="${order.id}">Xem chi tiết</button>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
    
    document.querySelectorAll('.cancel-order-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.getAttribute('data-id');
            cancelOrder(orderId);
        });
    });
    
    document.querySelectorAll('.view-order-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.getAttribute('data-id');
            viewOrderDetails(orderId);
        });
    });
}

function cancelOrder(orderId) {
    if (!confirm('Bạn có chắc chắn muốn hủy đơn hàng này?')) {
        return;
    }
    
    fetch(`api/cancel_order.php?id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                loadOrdersFromAPI();
            } else {
                alert(data.message || 'Không thể hủy đơn hàng!');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Đã xảy ra lỗi khi hủy đơn hàng!');
        });
}

function viewOrderDetails(orderId) {
    const orders = JSON.parse(localStorage.getItem('orders') || '[]');
    const order = orders.find(o => o.id == orderId);
    
    if (!order) {
        alert('Không tìm thấy thông tin đơn hàng!');
        return;
    }
    
    const modal = document.createElement('div');
    modal.className = 'order-details-modal';
    
    const orderDate = new Date(order.date);
    const formattedDate = orderDate.toLocaleDateString('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    
    let itemsHtml = '';
    let totalItems = 0;
    
    order.items.forEach(item => {
        totalItems += item.quantity;
        const imagePath = item.image || 'anh/default-product.png';
        
        itemsHtml += `
            <div class="detail-item">
                <img src="${imagePath}" alt="${item.name}" class="detail-item-image">
                <div class="detail-item-info">
                    <div class="detail-item-name">${item.name}</div>
                    <div class="detail-item-price">${formatCurrency(item.price)} x ${item.quantity}</div>
                </div>
                <div class="detail-item-total">${formatCurrency(item.price * item.quantity)}</div>
            </div>
        `;
    });
    
    const shipping = order.shipping || {};
    
    modal.innerHTML = `
        <div class="order-details-content">
            <div class="order-details-header">
                <h2>Chi tiết đơn hàng #${order.id}</h2>
                <span class="close-modal">&times;</span>
            </div>
            <div class="order-details-body">
                <div class="order-info">
                    <div class="order-info-item">
                        <span>Trạng thái:</span>
                        <span class="status ${getStatusClass(order.status)}">${order.status}</span>
                    </div>
                    <div class="order-info-item">
                        <span>Ngày đặt:</span>
                        <span>${formattedDate}</span>
                    </div>
                    <div class="order-info-item">
                        <span>Phương thức thanh toán:</span>
                        <span>${order.payment || 'Thanh toán khi nhận hàng'}</span>
                    </div>
                </div>
                
                <div class="shipping-info">
                    <h3>Thông tin giao hàng</h3>
                    <div class="shipping-details">
                        <p><strong>Người nhận:</strong> ${shipping.name || ''}</p>
                        <p><strong>Số điện thoại:</strong> ${shipping.phone || ''}</p>
                        <p><strong>Địa chỉ:</strong> ${shipping.address || ''}</p>
                        <p><strong>Ghi chú:</strong> ${order.notes || 'Không có'}</p>
                    </div>
                </div>
                
                <div class="order-items-details">
                    <h3>Sản phẩm (${totalItems})</h3>
                    <div class="detail-items-container">
                        ${itemsHtml}
                    </div>
                </div>
                
                <div class="order-summary">
                    <div class="summary-item">
                        <span>Tổng tiền hàng:</span>
                        <span>${formatCurrency(order.total - (order.shipping_fee || 0))}</span>
                    </div>
                    <div class="summary-item">
                        <span>Phí vận chuyển:</span>
                        <span>${formatCurrency(order.shipping_fee || 0)}</span>
                    </div>
                    <div class="summary-item total">
                        <span>Tổng thanh toán:</span>
                        <span>${formatCurrency(order.total)}</span>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    modal.querySelector('.close-modal').addEventListener('click', function() {
        document.body.removeChild(modal);
    });
    
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            document.body.removeChild(modal);
        }
    });
}

function getStatusClass(status) {
    switch(status.toLowerCase()) {
        case 'chờ xử lý': return 'pending';
        case 'đang giao': return 'shipping';
        case 'đã hoàn thành': return 'completed';
        case 'đã hủy': return 'cancelled';
        default: return '';
    }
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}
