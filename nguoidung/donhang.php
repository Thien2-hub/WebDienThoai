<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="style/style_donhang.css" />
    <title>Đơn hàng đã mua</title>
</head>

<body>
    <header>
        <div class="daudo">
            <div class="dau">
                <div class="logo">
                    <a id="lam" href="index.html">NH<span class="chu_o">o</span>M7</a>
                </div>
                <div class="search-container">
                    <div class="search-box">
                        <span class="search-icon" aria-label="search icon">🔍</span>
                        <input type="text" placeholder="Tìm kiếm ở đây" id="searchInput" aria-label="search input" />
                        <button id="searchButton" type="button">Tìm Kiếm</button>
                    </div>
                </div>

                <script>
                document.getElementById("searchButton").addEventListener("click", function() {
                    let searchValue = document.getElementById("searchInput").value.trim();
                    if (searchValue) {
                        alert("Bạn đã tìm kiếm: " + searchValue);
                    } else {
                        alert("Vui lòng nhập từ khóa tìm kiếm.");
                    }
                });
                </script>
            </div>
            <div class="giohang_taikhoan">
                <a href="giohang.html" class="giohang_taikhoan-item" aria-label="Giỏ hàng">
                    <div class="giohang-icon">
                        🛒
                        <span class="cart-count" aria-live="polite" aria-atomic="true">0</span>
                    </div>
                    <p>Giỏ Hàng</p>
                </a>

                <a href="taikhoan.html" class="giohang_taikhoan-item" aria-label="Tài khoản">
                    <img id="userAvatar" class="user-avatar" src="anh/avt_trang.png" alt="Avatar" />
                    <p id="usernameDisplay">Tài khoản</p>
                </a>
            </div>
        </div>
        <script src="script.js"></script>
    </header>

    <div class="breadcrumb" style="font-family: 'Inter', sans-serif; margin-top: 20px; padding: 0 0 30px 80px;">
        <a href="#" style="font-size: 28px; padding-right: 10px; color: black;">ĐƠN HÀNG ĐÃ MUA</a>
        <a href="index.html" style="color: rgba(0, 0, 0, 0.388); font-size: 10px;">TRANG CHỦ</a> /
        <a href="taikhoan.html" style="color: rgba(0, 0, 0, 0.388); font-size: 10px;">TÀI KHOẢN</a> /
        <span style="color: black; font-weight: bold; font-size: 12px;">ĐƠN HÀNG ĐÃ MUA</span>
    </div>

    <div class="all">
        <div id="order-list"></div>
    </div>

    <script src="orders.js"></script>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        let orders = JSON.parse(localStorage.getItem("orders")) || [];
        let orderList = document.getElementById("order-list");

        if (orders.length === 0) {
            orderList.innerHTML = '<p class="no-orders">Chưa có đơn hàng nào.</p>';
            return;
        }

        function renderOrders() {
            orderList.innerHTML = "";
            orders.forEach((order, index) => {
                let orderItem = document.createElement("div");
                orderItem.classList.add("order-item");
                orderItem.innerHTML = `
                        <img src="${order.image}" alt="${order.name}" />
                        <p><strong>${order.name} (${order.storage})</strong></p>
                        <p>Giá: ${(Number(order.price) || 0).toLocaleString()}đ</p>
                        <p>Ngày mua: ${order.date}</p>
                        <button class="buy-again" data-index="${index}" type="button">Mua lại</button>
                        <button class="delete-order" data-index="${index}" type="button">Xóa</button>
                    `;
                orderList.appendChild(orderItem);
            });
            attachEventListeners();
        }

        function attachEventListeners() {
            document.querySelectorAll(".delete-order").forEach(button => {
                button.addEventListener("click", function() {
                    let index = parseInt(this.getAttribute("data-index"));
                    if (confirm("Bạn có chắc chắn muốn xóa đơn hàng này?")) {
                        orders.splice(index, 1);
                        localStorage.setItem("orders", JSON.stringify(orders));
                        renderOrders();
                    }
                });
            });

            document.querySelectorAll(".buy-again").forEach(button => {
                button.addEventListener("click", function() {
                    let index = parseInt(this.getAttribute("data-index"));
                    let selectedOrder = orders[index];

                    localStorage.setItem("checkoutCart", JSON.stringify([selectedOrder]));
                    alert("Sản phẩm đã được thêm vào giỏ hàng thanh toán.");
                    window.location.href = "thanhtoan.html";
                });
            });
        }

        renderOrders();
    });
    </script>

</body>

</html>