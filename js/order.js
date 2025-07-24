document.addEventListener("DOMContentLoaded", function() {
    loadOrders();
    
    setupFilterButtons();
    
    updateCartCount();
});

function loadOrders(filter = 'all') {
    const orderListContainer = document.getElementById('order-list');
    const orders = JSON.parse(localStorage.getItem('orders') || '[]');
    
    const filteredOrders = filter === 'all' 
        ? orders 
        : orders.filter(order => order.status === filter);
    
    filteredOrders.sort((a, b) => new Date(b.date) - new Date(a.date));
    
    if (filteredOrders.length === 0) {
        orderListContainer.innerHTML = `
            <div class="empty-orders">
                <i>📦</i>
                <h3>Không có đơn hàng nào</h3>
                <p>Bạn chưa có đơn hàng nào trong danh sách này.</p>
                <a href="index.html" class="continue-shopping-btn">Tiếp tục mua sắm</a>
            </div>
        `;
        return;
    }
    
    let ordersHTML = '';
    
    filteredOrders.forEach(order => {
        let statusClass = '';
        switch (order.status) {
            case 'Chờ xử lý':
                statusClass = 'pending';
                break;
            case 'Đang giao':
                statusClass = 'shipping';
                break;
            case 'Đã hoàn thành':
                statusClass = 'completed';
                break;
            case 'Đã hủy':
                statusClass = 'cancelled';
                break;
            default:
                statusClass = 'pending';
        }
        
        const displayItems = order.items.slice(0, 2);
        const remainingItems = order.items.length - 2;
        
        let productsHTML = '';
        displayItems.forEach(item => {
            productsHTML += `
                <div class="order-product">
                    <img src="${item.image}" alt="${item.name}" class="product-image">
                    <div class="product-info">
                        <h4>${item.name}</h4>
                        <p>${item.color || ''} ${item.storage || ''}</p>
                        <p>${item.quantity} x ${formatCurrency(item.price)}</p>
                    </div>
                </div>
            `;
        });
        
      if (remainingItems > 0) {
            productsHTML += `
                <div class="more-products">
                    và ${remainingItems} sản phẩm khác
                </div>
            `;
        }
        
        ordersHTML += `
            <div class="order-card">
                <div class="order-header">
                    <div class="order-info">
                        <div class="order-id">Đơn hàng #${order.id}</div>
                        <div class="order-date">${new Date(order.date).toLocaleDateString('vi-VN')}</div>
                    </div>
                    <div class="order-status ${statusClass}">${order.status}</div>
                </div>
                <div class="order-content">
                    <div class="order-products">
                        ${productsHTML}
                    </div>
                    <div class="order-summary">
                        <div class="order-total">
                            Tổng tiền: <span class="total-amount">${formatCurrency(order.total)}</span>
                        </div>
                        <div class="order-actions">
                            <a href="chitietdonhang.html?id=${order.id}" class="view-detail-btn">Xem chi tiết</a>
                            ${order.status === 'Chờ xử lý' ? `<button class="cancel-btn" onclick="cancelOrder('${order.id}')">Hủy đơn hàng</button>` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    orderListContainer.innerHTML = ordersHTML;
}

function setupFilterButtons() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            filterButtons.forEach(btn => btn.classList.remove('active'));
            
            this.classList.add('active');
            
            const filter = this.getAttribute('data-filter');
            loadOrders(filter);
        });
    });
}

function cancelOrder(orderId) {
    if (confirm("Bạn có chắc chắn muốn hủy đơn hàng này?")) {
        let orders = JSON.parse(localStorage.getItem("orders") || "[]");
        
        const orderIndex = orders.findIndex(order => order.id === orderId);
        
        if (orderIndex !== -1) {
            orders[orderIndex].status = "Đã hủy";
            
            localStorage.setItem("orders", JSON.stringify(orders));
            
            loadOrders();
            
            alert("Đơn hàng đã được hủy thành công!");
        }
    }
}

function buyAgain(orderId) {
  const orders = JSON.parse(localStorage.getItem("orders") || "[]");
  
  const order = orders.find(order => order.id === orderId);
  
  if (!order || !order.items) {
    alert("Không tìm thấy thông tin đơn hàng!");
    return;
  }
  
  // Lấy gỏ hàng hiện tại
  let cart = JSON.parse(localStorage.getItem("cart") || "[]");
  
  // Thêm sản phẩm vào giỏ hàng
  order.items.forEach(item => {
    // Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa
    const existingItemIndex = cart.findIndex(cartItem => 
      cartItem.id === item.id && 
      cartItem.color === item.color && 
      cartItem.storage === item.storage
    );
    
    if (existingItemIndex !== -1) {
      // Nếu đã có, tăng số lượng
      cart[existingItemIndex].quantity += (item.quantity || 1);
    } else {
      // Nếu chưa có, thêm mới
      cart.push({
        id: item.id,
        name: item.name || item.tensanpham,
        price: item.price || item.dongia,
        image: item.image || item.hinhanh || "anh/default-product.png",
        quantity: item.quantity || item.soluong || 1,
        color: item.color,
        storage: item.storage
      });
    }
  });
  
  localStorage.setItem("cart", JSON.stringify(cart));
  
  updateCartCount();
  
  alert("Đã thêm sản phẩm vào giỏ hàng!");
  
  window.location.href = "giohang.html";
}

function trackOrder(orderId) {
  const trackingData = [
    { status: "Đơn hàng đã được xác nhận", location: "Kho hàng trung tâm", time: "10:30 25/05/2023" },
    { status: "Đơn hàng đã được đóng gói", location: "Kho hàng trung tâm", time: "15:45 25/05/2023" },
    { status: "Đơn hàng đã được giao cho đơn vị vận chuyển", location: "Kho hàng trung tâm", time: "08:15 26/05/2023" },
    { status: "Đơn hàng đang được vận chuyển", location: "Trung tâm phân phối", time: "14:20 26/05/2023" },
    { status: "Đơn hàng đang được giao đến địa chỉ của bạn", location: "Đang giao", time: "09:30 27/05/2023" }
  ];
  
  const trackingHtml = `
    <div class="tracking-modal">
      <div class="tracking-content">
        <div class="tracking-header">
          <h2>Theo dõi đơn hàng #${orderId}</h2>
          <span class="close-tracking">&times;</span>
        </div>
        <div class="tracking-body">
          <div class="tracking-timeline">
            ${trackingData.map(update => `
              <div class="timeline-item">
                <div class="timeline-time">${update.time}</div>
                <div class="timeline-content">
                  <div class="timeline-status">${update.status}</div>
                  <div class="timeline-location">${update.location}</div>
                </div>
              </div>
            `).join('')}
          </div>
        </div>
      </div>
    </div>
  `;
  
  const trackingModal = document.createElement('div');
  trackingModal.innerHTML = trackingHtml;
  document.body.appendChild(trackingModal);
  
  document.querySelector('.close-tracking').addEventListener('click', function() {
    document.body.removeChild(trackingModal);
  });
}

function updateCartCount() {
  const cart = JSON.parse(localStorage.getItem("cart") || "[]");
  const count = cart.reduce((total, item) => total + (parseInt(item.quantity) || 1), 0);
  
  const cartCountElement = document.querySelector(".cart-count");
  if (cartCountElement) {
    cartCountElement.textContent = count;
  }
}

function checkLoginStatus() {
  const user = JSON.parse(localStorage.getItem("user") || "{}");
  
  if (!user.isLoggedIn) {
    window.location.href = "dangnhap.html";
  }
}

function updateUserUI() {
  const user = JSON.parse(localStorage.getItem("user") || "{}");
  const usernameDisplay = document.getElementById("usernameDisplay");
  const userAvatar = document.getElementById("userAvatar");

  if (user.isLoggedIn && usernameDisplay) {
    usernameDisplay.textContent = user.username || "Tài khoản";
    if (user.avatar && userAvatar) {
      userAvatar.src = user.avatar;
    } else if (userAvatar) {
      userAvatar.src = "anh/avatar.png";
    }

    const loginLink = document.getElementById("login-link");
    const registerLink = document.getElementById("register-link");
    const logoutLink = document.getElementById("logout-link");
    const orderHistoryLink = document.getElementById("order-history-link");

    if (loginLink) loginLink.style.display = "none";
    if (registerLink) registerLink.style.display = "none";
    if (logoutLink) logoutLink.style.display = "block";
    if (orderHistoryLink) orderHistoryLink.style.display = "block";
  }
}

function syncLoginInfo() {
  const loggedInUser = localStorage.getItem("loggedInUser");
  const user = JSON.parse(localStorage.getItem("user") || "{}");
  
  if (loggedInUser && !user.isLoggedIn) {
    localStorage.setItem("user", JSON.stringify({
      id: Date.now(), 
      username: loggedInUser,
      isLoggedIn: true
    }));
  } 
  else if (user.isLoggedIn && !loggedInUser) {
    localStorage.setItem("loggedInUser", user.username);
  }

  updateUserUI();
}

function formatCurrency(amount) {
  return new Intl.NumberFormat("vi-VN", {
    style: "currency",
    currency: "VND",
  }).format(amount);
}

function cancelOrder(orderId) {
  if (confirm("Bạn có chắc chắn muốn hủy đơn hàng này?")) {
    let orders = JSON.parse(localStorage.getItem("orders") || "[]");
    
    const orderIndex = orders.findIndex(order => order.id === orderId);
    
    if (orderIndex !== -1) {
      orders[orderIndex].status = "Đã hủy";
      localStorage.setItem("orders", JSON.stringify(orders));
      
      loadOrders();
      
      alert("Đơn hàng đã được hủy thành công!");
    }
  }
}

function buyAgain(orderId) {
  const orders = JSON.parse(localStorage.getItem("orders") || "[]");
  const order = orders.find(order => order.id === orderId);
  if (!order || !order.items) {
    alert("Không tìm thấy thông tin đơn hàng!");
    return;
  }
  
  let cart = JSON.parse(localStorage.getItem("cart") || "[]");
  
  order.items.forEach(item => {
    const existingItemIndex = cart.findIndex(cartItem => 
      cartItem.id === item.id && 
      cartItem.color === item.color && 
      cartItem.storage === item.storage
    );
    
    if (existingItemIndex !== -1) {
      cart[existingItemIndex].quantity += (item.quantity || 1);
    } else {
      cart.push({
        id: item.id,
        name: item.name || item.tensanpham,
        price: item.price || item.dongia,
        image: item.image || item.hinhanh || "anh/default-product.png",
        quantity: item.quantity || item.soluong || 1,
        color: item.color,
        storage: item.storage
      });
    }
  });
  
  localStorage.setItem("cart", JSON.stringify(cart));
  
  updateCartCount();
  
  alert("Đã thêm sản phẩm vào giỏ hàng!");
  
  window.location.href = "giohang.html";
}

function trackOrder(orderId) {
  const trackingData = [
    { status: "Đơn hàng đã được xác nhận", location: "Kho hàng trung tâm", time: "10:30 25/05/2023" },
    { status: "Đơn hàng đã được đóng gói", location: "Kho hàng trung tâm", time: "15:45 25/05/2023" },
    { status: "Đơn hàng đã được giao cho đơn vị vận chuyển", location: "Kho hàng trung tâm", time: "08:15 26/05/2023" },
    { status: "Đơn hàng đang được vận chuyển", location: "Trung tâm phân phối", time: "14:20 26/05/2023" },
    { status: "Đơn hàng đang được giao đến địa chỉ của bạn", location: "Đang giao", time: "09:30 27/05/2023" }
  ];
  
  const trackingHtml = `
    <div class="tracking-modal">
      <div class="tracking-content">
        <div class="tracking-header">
          <h2>Theo dõi đơn hàng #${orderId}</h2>
          <span class="close-tracking">&times;</span>
        </div>
        <div class="tracking-body">
          <div class="tracking-timeline">
            ${trackingData.map(update => `
              <div class="timeline-item">
                <div class="timeline-time">${update.time}</div>
                <div class="timeline-content">
                  <div class="timeline-status">${update.status}</div>
                  <div class="timeline-location">${update.location}</div>
                </div>
              </div>
            `).join('')}
          </div>
        </div>
      </div>
    </div>
  `;
  
  const trackingModal = document.createElement('div');
  trackingModal.innerHTML = trackingHtml;
  document.body.appendChild(trackingModal);
  
  document.querySelector('.close-tracking').addEventListener('click', function() {
    document.body.removeChild(trackingModal);
  });
}








