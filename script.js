document.addEventListener("DOMContentLoaded", function () {
  var registerForm = document.getElementById("register-form");
  if (registerForm) {
    registerForm.addEventListener("submit", function (e) {
      e.preventDefault();

      var username = document.getElementById("username").value.trim();
      var sdt = document.getElementById("sdt").value.trim();
      var email = document.getElementById("email").value.trim();
      var password = document.getElementById("password").value;
      var passwords = document.getElementById("passwords").value;

      if (!username || !sdt || !email || !password || !passwords) {
        alert("Vui lòng điền đầy đủ thông tin!");
        return;
      }

      var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) {
        alert("Email không hợp lệ!");
        return;
      }

      var phoneRegex = /^[0-9]{10}$/;
      if (!phoneRegex.test(sdt)) {
        alert("Số điện thoại không hợp lệ! Vui lòng nhập 10 chữ số.");
        return;
      }

      if (password !== passwords) {
        alert("Mật khẩu nhập lại không khớp!");
        return;
      }

      var users = JSON.parse(localStorage.getItem("users")) || [];
      if (users.some((u) => u.username === username)) {
        alert("Tên đăng nhập đã tồn tại. Vui lòng chọn tên khác.");
        return;
      }
      if (users.some((u) => u.email === email)) {
        alert("Email đã được sử dụng. Vui lòng sử dụng email khác.");
        return;
      }

      users.push({ username, email, sdt, password });
      localStorage.setItem("users", JSON.stringify(users));

      alert("Đăng ký thành công!");
      registerForm.reset();
      window.location.href = "dangnhap.html";
    });
  }

  var loginForm = document.getElementById("register-form2");
var usernameDisplay = document.getElementById("usernameDisplay");
var loggedInUser = localStorage.getItem("loggedInUser");
var path = window.location.pathname;

if (
  usernameDisplay &&
  loggedInUser &&
  !path.includes("dangky.html") &&
  !path.includes("dangnhap.html") &&
  !path.includes("timkiem.html")
) {
  usernameDisplay.textContent = loggedInUser;
} else if (usernameDisplay) {
  usernameDisplay.textContent = "Tài Khoản";
}
  if (loginForm) {
    loginForm.addEventListener("submit", function (e) {
      e.preventDefault();
      var username = document.getElementById("username").value.trim();
      var password = document.getElementById("password").value;

      if (!username || !password) {
        alert("Vui lòng điền đầy đủ thông tin!");
        return;
      }

      var users = JSON.parse(localStorage.getItem("users")) || [];
      var foundUser = users.find((user) => user.username === username);

      if (!foundUser) {
        alert("Tài khoản không tồn tại!");
        return;
      }

      if (password === foundUser.password) {
        localStorage.setItem("loggedInUser", username);
        if (usernameDisplay) {
          usernameDisplay.textContent = username;
        }
        setTimeout(() => {
          window.location.href = "index.html";
        }, 500);
      } else {
        alert("Mật khẩu không đúng!");
      }
    });
  }

  // 🟢 Xử lý đăng xuất
  var logoutBtn = document.getElementById("logout");
  if (logoutBtn) {
    logoutBtn.addEventListener("click", function () {
      localStorage.removeItem("loggedInUser");
      if (usernameDisplay) {
        usernameDisplay.textContent = "Tài Khoản";
      }
      window.location.href = "dangnhap.html"; // Chuyển về trang đăng nhập
    });
  }

  // 🟢 Xử lý hiển thị và cập nhật avatar
  var loggedInUser = localStorage.getItem("loggedInUser");
  if (loggedInUser) {
    let userData = JSON.parse(
      localStorage.getItem(`userData_${loggedInUser}`)
    ) || {
      avatar: "anh/avatar.png",
    };
    const avatarImg = document.getElementById("avatar"); // Ảnh trên trang cá nhân
    const profileAvatarImg = document.getElementById("profile-avatar"); // Ảnh trên trang cá nhân
    const headerAvatar = document.getElementById("userAvatar"); // Ảnh trên góc phải (header)
    if (avatarImg) avatarImg.src = userData.avatar;
    if (profileAvatarImg) profileAvatarImg.src = userData.avatar;
    if (headerAvatar) headerAvatar.src = userData.avatar;
  }

  // 🟢 Xử lý cập nhật avatar
  const uploadInput = document.getElementById("avatar-upload");
  if (uploadInput) {
    uploadInput.addEventListener("change", function (event) {
      const file = event.target.files[0];
      if (file && loggedInUser) {
        const reader = new FileReader();
        reader.onload = function (e) {
          const newAvatar = e.target.result;
          // Cập nhật tất cả ảnh hiển thị
          const avatarImg = document.getElementById("avatar");
          const profileAvatarImg = document.getElementById("profile-avatar");
          const headerAvatar = document.getElementById("userAvatar");
          if (avatarImg) avatarImg.src = newAvatar;
          if (profileAvatarImg) profileAvatarImg.src = newAvatar;
          if (headerAvatar) headerAvatar.src = newAvatar;
          // Lưu ảnh vào localStorage theo từng tài khoản
          let userData = { avatar: newAvatar };
          localStorage.setItem(
            `userData_${loggedInUser}`,
            JSON.stringify(userData)
          );
        };
        reader.readAsDataURL(file);
      }
    });
  }

  // 🟢 Cập nhật số lượng giỏ hàng
  updateCartCount();
});

// Hàm cập nhật số lượng giỏ hàng
function updateCartCount() {
  let cart = JSON.parse(localStorage.getItem("cart")) || [];
  let totalCount = cart.reduce((sum, item) => sum + item.quantity, 0);
  let cartCountElements = document.querySelectorAll(".cart-count");
  cartCountElements.forEach((el) => {
    if (el) el.innerText = totalCount;
  });
}
