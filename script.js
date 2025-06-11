document.addEventListener("DOMContentLoaded", () => {
  const urlParams = new URLSearchParams(window.location.search);

  const game = urlParams.get("game");
  const userid = urlParams.get("userid");
  const server = urlParams.get("server");
  const jumlah = urlParams.get("jumlah");
  const metode = urlParams.get("metode");

  if (document.getElementById("summary")) {
    document.getElementById("summary-game").textContent = game || "";
    document.getElementById("summary-userid").textContent = userid || "";
    document.getElementById("summary-server").textContent = server || "";
    document.getElementById("summary-jumlah").textContent = jumlah || "";
    document.getElementById("summary-metode").textContent = metode || "";
  }

  // Toggle password visibility
  const togglePassword = document.querySelector(".toggle-password");
  if (togglePassword) {
    togglePassword.addEventListener("click", function () {
      const passwordInput = document.querySelector("#password");
      const type =
        passwordInput.getAttribute("type") === "password" ? "text" : "password";
      passwordInput.setAttribute("type", type);

      // Toggle icon
      this.classList.toggle("fa-eye");
      this.classList.toggle("fa-eye-slash");
    });
  }
});

const loadingContainer = document.querySelector(".loading-container");

setTimeout(() => {
  if (loadingContainer) {
    loadingContainer.style.display = "none";
  }
}, 1000);
