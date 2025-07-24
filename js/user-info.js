document.addEventListener("DOMContentLoaded", function() {
  function updateUserInfo() {
    const isLoggedIn = localStorage.getItem("isLoggedIn") === "true";
    const username = localStorage.getItem("username");
    const avatar = localStorage.getItem("userAvatar") || "anh/avt_trang.png";
    
    if (document.getElementById("userAvatar")) {
      document.getElementById("userAvatar").src = avatar;
    }
    
    if (document.getElementById("usernameDisplay")) {
      document.getElementById("usernameDisplay").textContent = isLoggedIn && username ? username : "Tài khoản";
    }
    
    if (isLoggedIn) {
      if (document.getElementById("loginMobile")) {
        document.getElementById("loginMobile").style.display = "none";
      }
      if (document.getElementById("registerMobile")) {
        document.getElementById("registerMobile").style.display = "none";
      }
      if (document.getElementById("logoutMobile")) {
        document.getElementById("logoutMobile").style.display = "block";
      }
    } else {
      if (document.getElementById("loginMobile")) {
        document.getElementById("loginMobile").style.display = "block";
      }
      if (document.getElementById("registerMobile")) {
        document.getElementById("registerMobile").style.display = "block";
      }
      if (document.getElementById("logoutMobile")) {
        document.getElementById("logoutMobile").style.display = "none";
      }
    }
  }
  
  updateUserInfo();
});

function logout() {
  localStorage.removeItem("isLoggedIn");
  localStorage.removeItem("username");
  localStorage.removeItem("userAvatar");
  window.location.href = "index.html";
}