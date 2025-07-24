const products = [
  {
    id: 1,
    name: "iPhone 15 Pro Max",
    price: 29990000,
    oldPrice: 34990000,
    image: "anh/ip15pl.jpg",
    category: "dienthoai",
    brand: "Apple",
    description: "iPhone 15 Pro Max 256GB chính hãng VN/A",
  },
  {
    id: 2,
    name: "Samsung Galaxy S23 Ultra",
    price: 23990000,
    oldPrice: 31990000,
    image: "anh/samsung-galaxy-s23-ultra.jpg",
    category: "dienthoai",
    brand: "Samsung",
    description: "Samsung Galaxy S23 Ultra 256GB chính hãng",
  },
  {
    id: 3,
    name: "Xiaomi 14 Ultra",
    price: 21990000,
    oldPrice: 24990000,
    image: "anh/xiaomi14.jpg",
    category: "dienthoai",
    brand: "Xiaomi",
    description: "Xiaomi 14 Ultra 5G 12GB/256GB",
  },
  {
    id: 4,
    name: "OPPO Find X6 Pro",
    price: 19990000,
    oldPrice: 22990000,
    image: "anh/oppo-find-x6.jpg",
    category: "dienthoai",
    brand: "OPPO",
    description: "OPPO Find X6 Pro 12GB/256GB",
  },
  {
    id: 5,
    name: "MacBook Pro M3 Pro",
    price: 49990000,
    oldPrice: 52990000,
    image: "anh/macbook-pro.jpg",
    category: "laptop",
    brand: "Apple",
    description: "MacBook Pro 14 inch M3 Pro 2023",
  },
  {
    id: 6,
    name: "Dell XPS 15",
    price: 42990000,
    oldPrice: 45990000,
    image: "anh/dell-xps.jpg",
    category: "laptop",
    brand: "Dell",
    description: "Dell XPS 15 9530 i9 13900H/32GB/1TB/RTX 4070",
  },
  {
    id: 7,
    name: "iPad Pro M2",
    price: 23990000,
    oldPrice: 27990000,
    image: "anh/ipad-pro.jpg",
    category: "tablet",
    brand: "Apple",
    description: "iPad Pro M2 12.9 inch WiFi 256GB",
  },
  {
    id: 8,
    name: "Apple Watch Ultra 2",
    price: 19990000,
    oldPrice: 22990000,
    image: "anh/apple-watch-ultra.jpg",
    category: "smartwatch",
    brand: "Apple",
    description: "Apple Watch Ultra 2 GPS + Cellular 49mm",
  },
];

function formatCurrency(amount) {
  return amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + "₫";
}

function createProductCard(product) {
  return `
        <div class="product-card">
            ${
              product.oldPrice > product.price
                ? `<div class="discount-label">-${Math.round(
                    ((product.oldPrice - product.price) / product.oldPrice) *
                      100
                  )}%</div>`
                : ""
            }
            <a href="product.html?id=${product.id}">
                <img src="${product.image}" alt="${product.name}">
                <div class="product-name">${product.name}</div>
                <div class="product-price">
                    ${formatCurrency(product.price)}
                    ${
                      product.oldPrice > product.price
                        ? `<span class="old-price">${formatCurrency(
                            product.oldPrice
                          )}</span>`
                        : ""
                    }
                </div>
                <div class="stars">★★★★★</div>
            </a>
            <button class="cart-button" onclick="addToCart(${
              product.id
            })">Thêm vào giỏ hàng</button>
        </div>
    `;
}

function handleSearch() {
  const searchInput = document.getElementById("searchInput");
  const searchTerm = searchInput.value.trim().toLowerCase();

  if (searchTerm === "") {
    return;
  }

  const searchResults = products.filter((product) => {
    return (
      product.name.toLowerCase().includes(searchTerm) ||
      product.description.toLowerCase().includes(searchTerm) ||
      product.brand.toLowerCase().includes(searchTerm) ||
      product.category.toLowerCase().includes(searchTerm)
    );
  });

  localStorage.setItem("searchResults", JSON.stringify(searchResults));
  localStorage.setItem("searchTerm", searchTerm);

  window.location.href = "timkiem.html";
}

document.addEventListener("DOMContentLoaded", function () {
  const searchButton = document.getElementById("searchButton");
  const searchInput = document.getElementById("searchInput");

  if (searchButton) {
    searchButton.addEventListener("click", handleSearch);
  }

  if (searchInput) {
    searchInput.addEventListener("keypress", function (e) {
      if (e.key === "Enter") {
        handleSearch();
      }
    });
  }
});

function addToCart(productId) {
  let cart = JSON.parse(localStorage.getItem("cart")) || [];

  const product = products.find((p) => p.id === productId);

  if (product) {
    const existingProductIndex = cart.findIndex(
      (item) => item.id === productId
    );

    if (existingProductIndex !== -1) {
      cart[existingProductIndex].quantity += 1;
    } else {
      cart.push({
        id: product.id,
        name: product.name,
        price: product.price,
        image: product.image,
        quantity: 1,
      });
    }

    localStorage.setItem("cart", JSON.stringify(cart));

    updateCartCount();

    alert(`Đã thêm ${product.name} vào giỏ hàng!`);
  }
}

function updateCartCount() {
  const cart = JSON.parse(localStorage.getItem("cart")) || [];
  const totalItems = cart.reduce((total, item) => total + item.quantity, 0);

  const cartCount = document.getElementById("cartCount");
  if (cartCount) {
    cartCount.textContent = totalItems;
    cartCount.style.display = totalItems > 0 ? "block" : "none";
  }
}

document.addEventListener("DOMContentLoaded", function () {
  updateCartCount();
});
