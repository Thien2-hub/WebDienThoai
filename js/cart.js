document.addEventListener("DOMContentLoaded", function() {
    function checkLoginStatus() {
        const isLoggedIn = localStorage.getItem("username") || 
                          (localStorage.getItem("user") && JSON.parse(localStorage.getItem("user")).isLoggedIn);
        return isLoggedIn;
    }
    
    if (!checkLoginStatus()) {
        alert("Vui lòng đăng nhập để xem giỏ hàng!");
        window.location.href = "dangnhap.html?redirect=giohang.html";
        return;
    }
    
    displayCart();
    
    updateTotal();
    
    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function() {
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            if (cart.length === 0) {
                alert('Giỏ hàng trống, không thể thanh toán!');
                return;
            }
            
            window.location.href = 'thanhtoan.html';
        });
    }
});

function displayCart() {
    const cartContainer = document.getElementById('cart-items');
    const emptyCartMessage = document.getElementById('empty-cart');
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    
    if (cart.length === 0) {
        if (cartContainer) cartContainer.style.display = 'none';
        if (emptyCartMessage) emptyCartMessage.style.display = 'block';
        return;
    }
    
    if (cartContainer) cartContainer.style.display = 'block';
    if (emptyCartMessage) emptyCartMessage.style.display = 'none';
    
    cartContainer.innerHTML = '';
    
    cart.forEach((item, index) => {
        const cartItem = document.createElement('div');
        cartItem.className = 'cart-item';
        cartItem.innerHTML = `
            <div class="cart-item-image">
                <img src="${item.image}" alt="${item.name}">
            </div>
            <div class="cart-item-details">
                <h3 class="cart-item-name">${item.name}</h3>
                <p class="cart-item-variant">${item.color || ''} ${item.storage || ''}</p>
                <p class="cart-item-price">${formatCurrency(item.price)}</p>
                <div class="cart-item-quantity">
                    <button class="quantity-btn minus" onclick="updateQuantity(${index}, -1)">-</button>
                    <input type="number" value="${item.quantity}" min="1" onchange="updateQuantityInput(${index}, this.value)">
                    <button class="quantity-btn plus" onclick="updateQuantity(${index}, 1)">+</button>
                </div>
            </div>
            <div class="cart-item-total">
                <p>${formatCurrency(item.price * item.quantity)}</p>
                <button class="remove-btn" onclick="removeItem(${index})">Xóa</button>
            </div>
        `;
        
        cartContainer.appendChild(cartItem);
    });
}

function updateQuantity(index, change) {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    
    if (index >= 0 && index < cart.length) {
        cart[index].quantity = Math.max(1, (cart[index].quantity || 1) + change);
        localStorage.setItem('cart', JSON.stringify(cart));
        
        displayCart();
        updateTotal();
        updateCartCount();
    }
}

function updateQuantityInput(index, value) {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    
    if (index >= 0 && index < cart.length) {
        const newQuantity = parseInt(value) || 1;
        cart[index].quantity = Math.max(1, newQuantity);
        localStorage.setItem('cart', JSON.stringify(cart));
        
        displayCart();
        updateTotal();
        updateCartCount();
    }
}

function removeItem(index) {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    
    if (index >= 0 && index < cart.length) {
        cart.splice(index, 1);
        localStorage.setItem('cart', JSON.stringify(cart));
        
        displayCart();
        updateTotal();
        updateCartCount();
    }
}

function updateTotal() {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const subtotal = cart.reduce((total, item) => total + (item.price * item.quantity), 0);
    const shipping = cart.length > 0 ? 30000 : 0; 
    const total = subtotal + shipping;
    
    document.getElementById('subtotal').textContent = formatCurrency(subtotal);
    document.getElementById('shipping').textContent = formatCurrency(shipping);
    document.getElementById('total').textContent = formatCurrency(total);
}

function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const count = cart.reduce((total, item) => total + (item.quantity || 1), 0);
    
    const cartCountElements = document.querySelectorAll('.cart-count');
    cartCountElements.forEach(element => {
        element.textContent = count;
    });
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}


