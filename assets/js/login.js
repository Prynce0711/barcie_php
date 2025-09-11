// Show overlay
document.getElementById("adminLoginBtn").addEventListener("click", function(e) {
    e.preventDefault();
    document.getElementById("loginOverlay").style.display = "flex";
});

// Hide overlay
document.getElementById("closeLogin").addEventListener("click", function() {
    document.getElementById("loginOverlay").style.display = "none";
});

// Client-side validation
document.getElementById("loginForm").addEventListener("submit", function(e) {
    let email = this.email.value.trim();
    let password = this.password.value.trim();
    let emailPattern = /^[a-zA-Z0-9._%+-]+@email\.lcup\.edu\.ph$/;
    let passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[#?!]).{8,}$/;

    if (!emailPattern.test(email)) {
        alert("Email must end with @email.lcup.edu.ph");
        e.preventDefault();
    } else if (!passwordPattern.test(password)) {
        alert("Password must have at least 8 characters, include uppercase, lowercase, a number, and a special character (#, ?, !)");
        e.preventDefault();
    }
});
