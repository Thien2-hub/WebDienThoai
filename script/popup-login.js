function createLoginPopup() {
    const popupContainer = document.createElement('div');
    popupContainer.id = 'login-popup';
    popupContainer.className = 'popup-container';
    popupContainer.style.display = 'none';
    
    popupContainer.innerHTML = `
        <div class="popup-content">
            <div class="popup-header">
                <h3>Smember</h3>
                <span class="close-popup">&times;</span>
            </div>
            <div class="popup-body">
                <img src="anh/smember-icon.png" alt="Smember" style="width: 80px; height: auto; margin: 10px auto; display: block;">
                <p>Vui lòng đăng nhập tài khoản Smember để xem ưu đãi và thanh toán dễ dàng hơn.</p>
            </div>
            <div class="popup-footer">
                <button id="register-btn" class="btn-outline">Đăng ký</button>
                <button id="login-btn" class="btn-primary">Đăng nhập</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(popupContainer);
    
    const style = document.createElement('style');
    style.textContent = `
        .popup-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .popup-content {
            background-color: white;
            border-radius: 10px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        
        .popup-header {
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
        }
        
        .popup-header h3 {
            margin: 0;
            color: #e63946;
            font-size: 20px;
        }
        
        .close-popup {
            font-size: 24px;
            cursor: pointer;
            color: #888;
        }
        
        .popup-body {
            padding: 20px;
            text-align: center;
        }
        
        .popup-body p {
            margin: 10px 0;
            font-size: 16px;
            line-height: 1.5;
            color: #333;
        }
        
        .popup-footer {
            padding: 15px;
            display: flex;
            justify-content: space-between;
            border-top: 1px solid #eee;
        }
        
        .btn-outline, .btn-primary {
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            width: 48%;
        }
        
        .btn-outline {
            background-color: white;
            border: 1px solid #e63946;
            color: #e63946;
        }
        
        .btn-primary {
            background-color: #ff5945;
            border: none;
            color: white;
        }
    `;
    
    document.head.appendChild(style);
    
    document.querySelector('.close-popup').addEventListener('click', closeLoginPopup);
    document.getElementById('login-btn').addEventListener('click', redirectToLogin);
    document.getElementById('register-btn').addEventListener('click', redirectToRegister);
}

function showLoginPopup() {
    if (!document.getElementById('login-popup')) {
        createLoginPopup();
    }
    
    document.getElementById('login-popup').style.display = 'flex';
}

function closeLoginPopup() {
    const popup = document.getElementById('login-popup');
    if (popup) {
        popup.style.display = 'none';
    }
}

function redirectToLogin() {
    window.location.href = 'dangnhap.html?redirect=' + encodeURIComponent(window.location.href);
}

function redirectToRegister() {
    window.location.href = 'dangky.html?redirect=' + encodeURIComponent(window.location.href);
}

function addLoginPopupEvents() {
    const cartButtons = document.querySelectorAll('.cart-button');
    if (cartButtons.length > 0) {
        cartButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                showLoginPopup();
            });
        });
    }
    
    const addToCartBtn = document.querySelector('.add-to-cart');
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function(e) {
            e.preventDefault();
            showLoginPopup();
        });
    }
    
    const buyNowBtn = document.querySelector('.buy-now');
    if (buyNowBtn) {
        buyNowBtn.addEventListener('click', function(e) {
            e.preventDefault();
            showLoginPopup();
        });
    }
    
    const cartLink = document.querySelector('a[href="giohang.html"]');
    if (cartLink) {
        cartLink.addEventListener('click', function(e) {
            e.preventDefault();
            showLoginPopup();
        });
    }
    
    const accountLink = document.querySelector('a[href="taikhoan.html"]');
    if (accountLink) {
        accountLink.addEventListener('click', function(e) {
            e.preventDefault();
            showLoginPopup();
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    addLoginPopupEvents();
});