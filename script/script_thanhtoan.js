function loadOrder() {
    let cart = JSON.parse(localStorage.getItem("cart")) || [];
    if (cart.length === 0) {
        document.getElementById("order-items").innerHTML = "<p>Không có sản phẩm nào.</p>";
        return;
    }

    let totalPrice = 0;
    let orderContainer = document.getElementById("order-items");
    orderContainer.innerHTML = "";

    cart.forEach(product => {
        totalPrice += product.price * product.quantity;
        orderContainer.innerHTML += `
            <div>
                <img src="${product.image}" alt="Sản phẩm" width="100">
                <p>${product.quantity} x ${product.name} - ${product.storage} - ${product.price.toLocaleString()} VND</p>
            </div>
        `;
    });

    document.getElementById("total-price").innerText = totalPrice.toLocaleString();
}

document.getElementById("agreeTerms").addEventListener("change", function() {
    document.getElementById("placeOrder").disabled = !this.checked;
});

document.getElementById("checkout-form").addEventListener("submit", function(e) {
    e.preventDefault();
    
    const fullName = document.getElementById("fullname").value;
    const phone = document.getElementById("phone").value;
    const address = document.getElementById("address").value;
    const paymentMethod = document.getElementById("payment");
    const note = document.getElementById("note").value;
    
    if (!fullName || !phone || !address) {
        alert("Vui lòng điền đầy đủ thông tin giao hàng!");
        return;
    }
    
    let cart = JSON.parse(localStorage.getItem("checkoutCart") || "[]");
    if (cart.length === 0) {
        alert("Giỏ hàng trống, không thể thanh toán!");
        return;
    }
    
    const user = JSON.parse(localStorage.getItem("user") || "{}");
    
    const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const shipping_fee = 30000;

const username = localStorage.getItem("username") || "guest";
const orderInfo = {
    id: "DH" + Date.now(),
    userId: user.id || username,
    username: username, 
    orderDate: new Date().toISOString(),
    date: new Date().toISOString(),
    items: cart,
    shippingInfo: {
        fullName,
        phone,
        address
    },
    paymentMethod: paymentMethod.value,
    notes: note,
    total: total + shipping_fee,
    shipping_fee,
    status: "Chờ xử lý"
};

    
    
    let orders = JSON.parse(localStorage.getItem("orders") || "[]");
    orders.push(orderInfo);
    localStorage.setItem("orders", JSON.stringify(orders));
    
    
    let mainCart = JSON.parse(localStorage.getItem("cart") || "[]");
    cart.forEach(checkoutItem => {
        mainCart = mainCart.filter(cartItem => 
            !(cartItem.id === checkoutItem.id && 
              cartItem.color === checkoutItem.color && 
              cartItem.storage === checkoutItem.storage)
        );
    });
    localStorage.setItem("cart", JSON.stringify(mainCart));
    
   
    localStorage.removeItem("checkoutCart");
    
    alert("Đặt hàng thành công!");
    window.location.href = "donhang.html";
});loadOrder();


