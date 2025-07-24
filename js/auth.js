function checkLoginStatus() {
    const username = localStorage.getItem("username");
    const user = JSON.parse(localStorage.getItem("user") || "{}");
    
    return username || (user && user.isLoggedIn);
}

function login(username, password) {
    if (username && password) {
        localStorage.setItem("username", username);
        
        const user = {
            id: Date.now(),
            username: username,
            isLoggedIn: true
        };
        localStorage.setItem("user", JSON.stringify(user));
        
        updateUserUI();
        
        const urlParams = new URLSearchParams(window.location.search);
        const redirect = urlParams.get('redirect');
        
        if (redirect) {
            window.location.href = redirect;
        } else {
            window.location.href = "index.html";
        }
        
        return true;
    }
    
    return false;
}

function logout() {
    localStorage.removeItem("username");
    localStorage.removeItem("user");
    
    updateUserUI();
    
    window.location.href = "index.html";
}

function updateUserUI() {
    const isLoggedIn = checkLoginStatus();
    const username = localStorage.getItem("username");
    
    const userMenuElements = document.querySelectorAll(".user-menu");
    const guestMenuElements = document.querySelectorAll(".guest-menu");
    
    userMenuElements.forEach(element => {
        element.style.display = isLoggedIn ? "block" : "none";
    });
    
    guestMenuElements.forEach(element => {
        element.style.display = isLoggedIn ? "none" : "block";
    });
    
    const usernameElements = document.querySelectorAll(".username");
    usernameElements.forEach(element => {
        if (isLoggedIn) {
            element.textContent = username;
            element.style.display = "inline-block";
        } else {
            element.style.display = "none";
        }
    });
}

document.addEventListener("DOMContentLoaded", function() {
    updateUserUI();
    
    const loginForm = document.getElementById("login-form");
    if (loginForm) {
        loginForm.addEventListener("submit", function(e) {
            e.preventDefault();
            
            const username = document.getElementById("username").value;
            const password = document.getElementById("password").value;
            
            if (!username || !password) {
                alert("Vui lòng nhập đầy đủ thông tin đăng nhập!");
                return;
            }
            
            if (login(username, password)) {
                console.log("Đăng nhập thành công!");
            } else {
                alert("Tên đăng nhập hoặc mật khẩu không đúng!");
            }
        });
    }
    
    const logoutButtons = document.querySelectorAll(".logout-btn");
    logoutButtons.forEach(button => {
        button.addEventListener("click", function(e) {
            e.preventDefault();
            logout();
        });
    });
});





