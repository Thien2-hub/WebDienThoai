function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem("cart")) || [];
    const count = cart.reduce((total, item) => total + item.quantity, 0);
    
    const cartCountElements = document.querySelectorAll(".cart-count");
    cartCountElements.forEach(element => {
        if (element) {
            element.textContent = count;
        }
    });
}

document.addEventListener("DOMContentLoaded", function() {
    updateCartCount();
});