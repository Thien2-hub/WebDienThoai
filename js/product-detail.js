document.addEventListener("DOMContentLoaded", function() {
    function checkLoginStatus() {
        const isLoggedIn = localStorage.getItem("username") || 
                          (localStorage.getItem("user") && JSON.parse(localStorage.getItem("user")).isLoggedIn);
        return isLoggedIn;
    }
    
    const addToCartBtn = document.querySelector('.add-to-cart');
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function() {
            if (!checkLoginStatus()) {
                alert("Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!");
                window.location.href = "dangnhap.html?redirect=" + window.location.href;
                return;
            }
            
            addToCart();
        });
    }
    
    const buyNowBtn = document.querySelector('.buy-now');
    if (buyNowBtn) {
        buyNowBtn.addEventListener('click', function() {
            if (!checkLoginStatus()) {
                alert("Vui lòng đăng nhập để mua sản phẩm!");
                window.location.href = "dangnhap.html?redirect=" + window.location.href;
                return;
            }
            
            addToCart(true);
        });
    }
    
    function addToCart(buyNow = false) {
        const productId = document.querySelector('[data-product-id]').getAttribute('data-product-id');
        const productName = document.querySelector('.product-title').textContent;
        const productPrice = parseInt(document.querySelector('.product-price').getAttribute('data-price'));
        const productImage = document.querySelector('.product-image img').src;
        
        const colorOptions = document.querySelectorAll('.color-option');
        const storageOptions = document.querySelectorAll('.storage-option');
        
        let selectedColor = '';
        let selectedStorage = '';
        
        colorOptions.forEach(option => {
            if (option.classList.contains('selected')) {
                selectedColor = option.getAttribute('data-color');
            }
        });
        
        storageOptions.forEach(option => {
            if (option.classList.contains('selected')) {
                selectedStorage = option.getAttribute('data-storage');
            }
        });
        
        const quantity = parseInt(document.querySelector('.quantity-input').value) || 1;
        
       const products = [
    { id: 1, name: "IPHONE 15 PLUS", price: 17990000, image: "anh/ip15pl.png" },
    { id: 2, name: "SAMSUNG GALAXY S23", price: 15990000, image: "anh/samsung-galaxy-s23-ultra.jpg" },
    { id: 3, name: "OPPO FIND X5 PRO", price: 13990000, image: "anh/oppo-find-x6.jpg" },
    { id: 4, name: "XIAOMI 13 PRO", price: 12990000, image: "anh/xiaomi14.jpg" },
    { id: 5, name: "VIVO V27", price: 9990000, image: "anh/vivo-v27.jpg" },
    { id: 6, name: "OPPO RENO 8T", price: 5990000, image: "anh/oppo-reno-8t.jpg" },
    { id: 7, name: "SAMSUNG GALAXY A54", price: 7990000, image: "anh/samsung-galaxy-a64.jpg" },
    { id: 8, name: "XIAOMI REDMI NOTE 12", price: 3990000, image: "anh/xiaomi-note12.jpg" },
    { id: 9, name: "IPHONE 14 PRO MAX", price: 25990000, image: "anh/ip15pl.png" },
    { id: 10, name: "SAMSUNG GALAXY Z FOLD 5", price: 32990000, image: "anh/samsung-galaxy-z.jpg" },
    { id: 11, name: "OPPO FIND N3", price: 22990000, image: "anh/oppo-find-13.jpg" },
    { id: 12, name: "XIAOMI 13T PRO", price: 14990000, image: "anh/xiaomi-13-pro.jpg" }
    // ...
];
        
        let cart = JSON.parse(localStorage.getItem('cart') || '[]');
        
        const existingProductIndex = cart.findIndex(item => 
            item.id === product.id && 
            item.color === product.color && 
            item.storage === product.storage
        );
        
        if (existingProductIndex !== -1) {
            cart[existingProductIndex].quantity += quantity;
        } else {
            cart.push(product);
        }
        
        localStorage.setItem('cart', JSON.stringify(cart));
        
        updateCartCount();
        
        if (buyNow) {
            window.location.href = 'thanhtoan.html';
        } else {
            alert('Đã thêm sản phẩm vào giỏ hàng!');
        }
    }
    
    function updateCartCount() {
        const cart = JSON.parse(localStorage.getItem('cart') || '[]');
        const count = cart.reduce((total, item) => total + (item.quantity || 1), 0);
        
        const cartCountElements = document.querySelectorAll('.cart-count');
        cartCountElements.forEach(element => {
            element.textContent = count;
        });
    }
});
