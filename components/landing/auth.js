// Authentication Functionality
// User Login/Signup and Admin Login

document.addEventListener("DOMContentLoaded", function () {
  // Only initialize admin auth on the landing page. Guest/user login/signup
  // UI and handlers have been removed per project requirements.
  initializeAdminAuth();
});

function initializeAdminAuth() {
  // Admin password toggle
  const toggleAdminPasswordBtn = document.getElementById("toggleAdminPassword");
  const adminPasswordInput = document.getElementById("admin-password");

  if (toggleAdminPasswordBtn && adminPasswordInput) {
    toggleAdminPasswordBtn.addEventListener("click", () => {
      const icon = toggleAdminPasswordBtn;
      if (adminPasswordInput.type === "password") {
        adminPasswordInput.type = "text";
        icon.textContent = "🙈";
      } else {
        adminPasswordInput.type = "password";
        toggleAdminPasswordBtn.textContent = "👁️";
      }
    });
  }

  // Admin AJAX Login
  setupAdminLogin();
}

function setupAdminLogin() {
  const adminForm = document.getElementById("admin-login-form");
  const adminError = document.getElementById("admin-login-error");

  console.log("🎯 setupAdminLogin called");
  console.log("📋 adminForm:", adminForm);
  console.log("⚠️ adminError:", adminError);

  if (adminForm && adminError) {
    console.log("✅ Admin form and error div found, adding submit listener");
    adminForm.addEventListener("submit", async (e) => {
      console.log("🚀 Form submit event triggered!");
      e.preventDefault();
      adminError.classList.add("d-none");
      adminError.textContent = "";

      const submitBtn = e.target.querySelector('button[type="submit"]');
      const originalText = submitBtn.innerHTML;
      submitBtn.innerHTML = '<div class="loading-spinner mx-auto"></div>';
      submitBtn.disabled = true;

      const formData = new FormData(adminForm);

      // Production login endpoint
      const appBasePath =
        typeof window.APP_BASE_PATH === "string" &&
        window.APP_BASE_PATH.trim() !== ""
          ? window.APP_BASE_PATH.replace(/\/+$/, "")
          : "";
      const loginUrl = `${appBasePath}/database/index.php?endpoint=admin_login`;
      console.log("🔍 Sending login request to:", loginUrl);

      try {
        const res = await fetch(loginUrl, {
          method: "POST",
          body: formData,
          headers: {
            Accept: "application/json",
          },
        });

        // Check if response is OK
        if (!res.ok) {
          throw new Error(
            `HTTP error! status: ${res.status} - ${res.statusText}`,
          );
        }

        const contentType = res.headers.get("content-type");
        if (!contentType || !contentType.includes("application/json")) {
          const text = await res.text();
          console.error(
            "❌ Non-JSON response received:",
            text.substring(0, 200),
          );
          throw new Error(
            "Server returned HTML instead of JSON. Check if admin_login.php exists.",
          );
        }

        const data = await res.json();

        console.log("📥 Response received:", data);

        if (data.success) {
          console.log("✅ Login successful!");
          console.log("📍 Redirect URL:", data.redirect);
          submitBtn.innerHTML = '<i class="fas fa-check me-2"></i>Success!';

          // Close the modal first
          if (typeof closeSection === "function") {
            console.log("🚪 Closing admin-login modal");
            try {
              closeSection("admin-login");
            } catch (err) {
              console.error("Error closing section:", err);
            }
          }

          // Immediate redirect - no delay needed since session_write_close is called in PHP
          console.log("🌐 Current location:", window.location.href);
          const baseRedirect =
            data.redirect && String(data.redirect).trim() !== ""
              ? String(data.redirect)
              : "index.php?view=dashboard";
          const redirectUrl = baseRedirect.includes("#")
            ? `${appBasePath ? `${appBasePath}/` : ""}${baseRedirect}`
            : `${appBasePath ? `${appBasePath}/` : ""}${baseRedirect}#dashboard`;
          console.log("🔄 Redirecting NOW to:", redirectUrl);

          // Try multiple redirect methods to ensure it works
          try {
            window.location.href = redirectUrl;
          } catch (err) {
            console.error("location.href failed:", err);
            try {
              window.location.replace(redirectUrl);
            } catch (err2) {
              console.error("location.replace failed:", err2);
              window.location = redirectUrl;
            }
          }
        } else {
          console.error("❌ Login failed:", data.message);
          if (data.debug) {
            console.log("🔍 Debug info:", data.debug);
          }
          adminError.textContent = data.message || "Login failed.";
          adminError.classList.remove("d-none");
          submitBtn.innerHTML = originalText;
          submitBtn.disabled = false;
        }
      } catch (error) {
        console.error("❌ Fetch error:", error);
        adminError.textContent = "Something went wrong. Try again.";
        adminError.classList.remove("d-none");
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
      }
    });
  }
}
