document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const orderId = urlParams.get('id');
    
    if (!orderId) {
        document.getElementById('order-details').innerHTML = '<div class="error">Không tìm thấy thông tin đơn hàng!</div>';
        return;
    }
    
    updateUserUI();
    
    loadOrderDetails(orderId);
});

function updateUserUI() {
    const user = JSON.parse(localStorage.getItem('user') || '{}');
    const usernameDisplay = document.getElementById('usernameDisplay');
    const userAvatar = document.getElementById('userAvatar');
    
    if (user.isLoggedIn && usernameDisplay) {
        usernameDisplay.textContent = user.username || 'Tài khoản';
        if (user.avatar && userAvatar) {
            userAvatar.src = user.avatar;
        } else if (userAvatar) {
            userAvatar.src = 'anh/avt_trang.png';
        }
        
        const loginLink = document.getElementById('login-link');
        const registerLink = document.getElementById('register-link');
        const logoutLink = document.getElementById('logout-link');
        const orderHistoryLink = document.getElementById('order-history-link');
        
        if (loginLink) loginLink.style.display = 'none';
        if (registerLink) registerLink.style.display = 'none';
        if (logoutLink) logoutLink.style.display = 'block';
        if (orderHistoryLink) orderHistoryLink.style.display = 'block';
    }
}

function loadOrderDetails(orderId) {
    const orderDetailsContainer = document.getElementById('order-details');
    if (!orderDetailsContainer) return;
    
    orderDetailsContainer.innerHTML = '<div class="loading">Đang tải thông tin đơn hàng...</div>';
    
    fetch(`api/order_details.php?id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayOrderDetails(data.order);
            } else {
                const orders = JSON.parse(localStorage.getItem('orders') || '[]');
                const order = orders.find(o => o.id == orderId);
                
                if (order) {
                    displayOrderDetails(order);
                } else {
                    orderDetailsContainer.innerHTML = `<div class="error">${data.message || 'Không tìm thấy thông tin đơn hàng!'}</div>`;
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            const orders = JSON.parse(localStorage.getItem('orders') || '[]');
            const order = orders.find(o => o.id == orderId);
            
            if (order) {
                displayOrderDetails(order);
            } else {
                orderDetailsContainer.innerHTML = '<div class="error">Đã xảy ra lỗi khi tải thông tin đơn hàng!</div>';
            }
        });
}

function displayOrderDetails(order) {
    const orderDetailsContainer = document.getElementById('order-details');
    if (!orderDetailsContainer) return;
    
    const orderDate = new Date(order.date);
    const formattedDate = orderDate.toLocaleDateString('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    
    let statusClass = '';
    switch (order.status.toLowerCase()) {
        case 'chờ xử lý':
            statusClass = 'status-pending';
            break;
        case 'đang xử lý':
            statusClass = 'status-processing';
            break;
        case 'đang giao':
            statusClass = 'status-shipped';
            break;
        case 'đã hoàn thành':
            statusClass = 'status-delivered';
            break;
        case 'đã hủy':
            statusClass = 'status-cancelled';
            break;
        default:
            statusClass = '';
    }
    
    let itemsHtml = '';
    if (order.items && Array.isArray(order.items)) {
        order.items.forEach(item => {
            const imagePath = item.hinhanh || item.image || 'anh/default-product.png';
            
            itemsHtml += `
                <div class="order-item-detail">
                    <img src="${imagePath}" alt="${item.tensanpham || item.name}" class="item-image">
                    <div class="item-info">
                        <div class="item-name">${item.tensanpham || item.name}</div>
                        <div class="item-price">${formatCurrency(item.dongia || item.price)}</div>
                        <div class="item-quantity">Số lượng: ${item.soluong || item.quantity}</div>
                    </div>
                </div>
            `;
        });
    }
    
    const shippingInfo = order.shipping || {};
    const shippingHtml = `
        <div class="shipping-info">
            <h3>Thông tin giao hàng</h3>
            <p><strong>Người nhận:</strong> ${shippingInfo.name || ''}</p>
            <p><strong>Số điện thoại:</strong> ${shippingInfo.phone || ''}</p>
            <p><strong>Địa chỉ:</strong> ${shippingInfo.address || ''}</p>
        </div>
    `;
    
    const paymentHtml = `
        <div class="payment-info">
            <h3>Thông tin thanh toán</h3>
            <p><strong>Phương thức:</strong> ${order.payment || 'Thanh toán khi nhận hàng'}</p>
            <p><strong>Tổng tiền hàng:</strong> ${formatCurrency(order.total - (order.shipping_fee || 0))}</p>
            <p><strong>Phí vận chuyển:</strong> ${formatCurrency(order.shipping_fee || 0)}</p>
            <p><strong>Tổng thanh toán:</strong> ${formatCurrency(order.total)}</p>
        </div>
    `;
    
    // Tạo HTML cho ghi chú
    const notesHtml = order.notes ? `
        <div class="order-notes">
            <h3>Ghi chú</h3>
            <p>${order.notes}</p>
        </div>
    ` : '';
    
    // Tạo HTML tổng thể
    const html = `
        <div class="order-detail-header">
            <div class="order-id-detail">Đơn hàng #${order.id}</div>
            <div class="order-date-detail">Ngày đặt: ${formattedDate}</div>
            <div class="order-status-detail ${statusClass}">${order.status}</div>
        </div>
        
        <div class="order-detail-content">
            <div class="order-items-detail">
                <h3>Sản phẩm</h3>
                ${itemsHtml}
            </div>
            
            <div class="order-info-detail">
                ${shippingHtml}
                ${paymentHtml}
                ${notesHtml}
            </div>
        </div>
        
        <div class="order-actions-detail">
            <a href="donhang.html" class="back-button">Quay lại danh sách đơn hàng</a>
            ${order.status.toLowerCase() === 'chờ xử lý' ? 
                `<button class="cancel-order-btn" onclick="cancelOrder(${order.id})">Hủy đơn hàng</button>` : ''}
            ${order.status.toLowerCase() === 'đã hoàn thành' ? 
                `<button class="buy-again-btn" onclick="buyAgain(${order.id})">Mua lại</button>` : ''}
            ${order.status.toLowerCase() === 'đang giao' ? 
                `<button class="track-order-btn" onclick="trackOrder(${order.id})">Theo dõi đơn hàng</button>` : ''}
        </div>
    `;
    
    orderDetailsContainer.innerHTML = html;
}

function cancelOrder(orderId) {
    if (!confirm('Bạn có chắc chắn muốn hủy đơn hàng này?')) {
        return;
    }
    
    fetch(`api/cancel_order.php?id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Đơn hàng đã được hủy thành công!');
                loadOrderDetails(orderId);
            } else {
                alert(data.message || 'Không thể hủy đơn hàng!');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Đã xảy ra lỗi khi hủy đơn hàng!');
        });
}

function buyAgain(orderId) {
    const orders = JSON.parse(localStorage.getItem('orders') || '[]');
    const order = orders.find(o => o.id == orderId);
    
    if (!order) {
        alert('Không tìm thấy thông tin đơn hàng!');
        return;
    }
    
    let cart = JSON.parse(localStorage.getItem('cart') || '[]');
    
    if (order.items && Array.isArray(order.items)) {
        order.items.forEach(item => {
            cart.push({
                id: item.id_sp || item.id,
                name: item.tensanpham || item.name,
                price: item.dongia || item.price,
                image: item.hinhanh || item.image || 'anh/default-product.png',
                quantity: item.soluong || item.quantity
            });
        });
        
        localStorage.setItem('cart', JSON.stringify(cart));
        alert('Đã thêm sản phẩm vào giỏ hàng!');
        
        window.location.href = 'giohang.html';
    } else {
        alert('Không tìm thấy thông tin sản phẩm trong đơn hàng!');
    }
}

function trackOrder(orderId) {
    alert('Chức năng theo dõi đơn hàng đang được phát triển!');
}

function formatCurrency(amount) {
    if (!amount || isNaN(amount)) {
        amount = 0;
    }
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}