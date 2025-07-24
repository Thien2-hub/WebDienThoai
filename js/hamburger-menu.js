document.addEventListener("DOMContentLoaded", function() {
  document.getElementById("hamburgerMenu").addEventListener("click", function() {
    document.getElementById("mobileMenu").classList.add("active");
    document.getElementById("mobileMenuOverlay").classList.add("active");
    document.body.style.overflow = "hidden"; 
  });

  document.getElementById("closeMenu").addEventListener("click", function() {
    document.getElementById("mobileMenu").classList.remove("active");
    document.getElementById("mobileMenuOverlay").classList.remove("active");
    document.body.style.overflow = ""; 
  });

    document.getElementById("mobileMenuOverlay").addEventListener("click", function() {
    document.getElementById("mobileMenu").classList.remove("active");
    document.getElementById("mobileMenuOverlay").classList.remove("active");
    document.body.style.overflow = "";
  });

  function checkLoginStatus() {
    const isLoggedIn = localStorage.getItem("isLoggedIn") === "true";
    const username = localStorage.getItem("username");
    
    if (isLoggedIn) {
      document.getElementById("loginMobile").style.display = "none";
      document.getElementById("registerMobile").style.display = "none";
      document.getElementById("logoutMobile").style.display = "block";
    } else {
      document.getElementById("loginMobile").style.display = "block";
      document.getElementById("registerMobile").style.display = "block";
      document.getElementById("logoutMobile").style.display = "none";
    }
  }
  
  checkLoginStatus();
});

function logout() {
  localStorage.removeItem("isLoggedIn");
  localStorage.removeItem("username");
  window.location.href = "index.html";
}


