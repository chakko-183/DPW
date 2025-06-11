document.addEventListener("DOMContentLoaded", () => {
  const urlParams = new URLSearchParams(window.location.search);

  const game = urlParams.get("game");
  const userid = urlParams.get("userid");
  const server = urlParams.get("server");
  const jumlah = urlParams.get("jumlah");
  const metode = urlParams.get("metode");

  const togglePassword = document.querySelector(".toggle-password");
  if (togglePassword) {
    togglePassword.addEventListener("click", function () {
      const passwordInput = document.querySelector("#password");
      if (passwordInput) {
        const type =
          passwordInput.getAttribute("type") === "password"
            ? "text"
            : "password";
        passwordInput.setAttribute("type", type);

        this.classList.toggle("fa-eye");
        this.classList.toggle("fa-eye-slash");
      }
    });
  }

  const alerts = document.querySelectorAll(".alert");
  alerts.forEach((alert) => {
    const closeBtn = document.createElement("span");
    closeBtn.innerHTML = "&times;";
    closeBtn.style.cursor = "pointer";
    closeBtn.style.float = "right";
    closeBtn.style.fontSize = "20px";
    closeBtn.style.fontWeight = "bold";
    closeBtn.style.marginLeft = "10px";
    closeBtn.onclick = function () {
      alert.style.opacity = "0";
      alert.style.transition = "opacity 0.5s ease-out";
      setTimeout(() => alert.remove(), 500);
    };
    alert.prepend(closeBtn);

    setTimeout(() => {
      alert.style.opacity = "0";
      alert.style.transition = "opacity 0.5s ease-out";
      setTimeout(() => alert.remove(), 500);
    }, 5000);
  });

  const starContainer = document.getElementById("rating-stars");
  const selectedRatingInput = document.getElementById("selected-rating");
  let currentRating =
    parseInt(selectedRatingInput ? selectedRatingInput.value : 0) || 0;

  if (starContainer && selectedRatingInput) {
    const stars = starContainer.querySelectorAll(".star");

    stars.forEach((star) => {
      star.addEventListener("click", function () {
        currentRating = parseInt(this.dataset.value);
        selectedRatingInput.value = currentRating;
        highlightStars(currentRating);
      });

      star.addEventListener("mouseover", function () {
        highlightStars(parseInt(this.dataset.value));
      });

      star.addEventListener("mouseout", function () {
        highlightStars(currentRating);
      });
    });
    highlightStars(currentRating);
  }

  function highlightStars(rating) {
    if (starContainer) {
      const stars = starContainer.querySelectorAll(".star");
      stars.forEach((star, index) => {
        if (index < rating) {
          star.classList.add("active");
        } else {
          star.classList.remove("active");
        }
      });
    }
  }

  const reviewForm = document.getElementById("review-form");
  if (reviewForm) {
    reviewForm.addEventListener("submit", function (event) {
      event.preventDefault();

      const rating = document.getElementById("selected-rating").value;
      const reviewText = document.getElementById("review-text").value;

      if (rating < 1 || rating > 5 || reviewText.trim() === "") {
        displayAlert("Mohon berikan rating dan tulis ulasan Anda.", "danger");
        return;
      }

      const formData = new FormData();
      formData.append("action", "submit_review");
      formData.append("rating", rating);
      formData.append("review_text", reviewText);

      fetch("ulasan.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => {
          // Check if response is JSON, otherwise, read as text and log
          const contentType = response.headers.get("content-type");
          if (contentType && contentType.indexOf("application/json") !== -1) {
            return response.json();
          } else {
            return response.text().then((text) => {
              console.error("Server response was not JSON:", text);
              throw new Error("Server returned non-JSON response.");
            });
          }
        })
        .then((data) => {
          if (data.success) {
            displayAlert(data.message, "success");
            reviewForm.reset();
            currentRating = 0;
            selectedRatingInput.value = 0;
            highlightStars(0);
            setTimeout(() => location.reload(), 1500);
          } else {
            displayAlert(data.message, "danger");
          }
        })
        .catch((error) => {
          console.error("Fetch error:", error);
          displayAlert(
            "Terjadi kesalahan koneksi atau server saat mengirim ulasan. Coba lagi.",
            "danger"
          );
        });
    });
  }

  function displayAlert(message, type) {
    const alertContainer = document.createElement("div");
    alertContainer.classList.add("alert", `alert-${type}`);
    alertContainer.textContent = message;

    const closeBtn = document.createElement("span");
    closeBtn.innerHTML = "&times;";
    closeBtn.style.cursor = "pointer";
    closeBtn.style.float = "right";
    closeBtn.style.fontSize = "20px";
    closeBtn.style.fontWeight = "bold";
    closeBtn.style.marginLeft = "10px";
    closeBtn.onclick = function () {
      alertContainer.style.opacity = "0";
      alertContainer.style.transition = "opacity 0.5s ease-out";
      setTimeout(() => alertContainer.remove(), 500);
    };
    alertContainer.prepend(closeBtn);

    const targetSection =
      document.querySelector(".review-section") ||
      document.querySelector(".login-section") ||
      document.querySelector("main");
    if (targetSection) {
      targetSection.prepend(alertContainer);
      setTimeout(() => {
        alertContainer.style.opacity = "0";
        alertContainer.style.transition = "opacity 0.5s ease-out";
        setTimeout(() => alertContainer.remove(), 500);
      }, 5000);
    }
  }

  // Wishlist Button Logic (Event Delegation)
  document.body.addEventListener("click", function (event) {
    if (
      event.target.classList.contains("add-to-wishlist") ||
      event.target.closest(".add-to-wishlist")
    ) {
      const button = event.target.classList.contains("add-to-wishlist")
        ? event.target
        : event.target.closest(".add-to-wishlist");
      handleWishlistAction(button, "add_to_wishlist");
    } else if (
      event.target.classList.contains("remove-from-wishlist") ||
      event.target.closest(".remove-from-wishlist")
    ) {
      const button = event.target.classList.contains("remove-from-wishlist")
        ? event.target
        : event.target.closest(".remove-from-wishlist");
      handleWishlistAction(button, "remove_from_wishlist");
    }
  });

  function handleWishlistAction(button, action) {
    const gameId = button.dataset.gameId;
    const currentIcon = button.querySelector("i");

    button.disabled = true;
    if (currentIcon) currentIcon.classList.add("fa-spin", "fa-spinner");

    const formData = new FormData();
    formData.append("action", action);
    formData.append("game_id", gameId);

    let targetUrl = "daftar-game.php"; // Default for adding/removing from daftar-game.php
    if (window.location.pathname.includes("wishlist.php")) {
      targetUrl = "wishlist.php"; // For removing from wishlist.php
    }

    fetch(targetUrl, {
      method: "POST",
      body: formData,
    })
      .then((response) => {
        // Check if response is JSON, otherwise, read as text and log
        const contentType = response.headers.get("content-type");
        if (contentType && contentType.indexOf("application/json") !== -1) {
          return response.json();
        } else {
          return response.text().then((text) => {
            console.error("Server response was not JSON for wishlist:", text);
            throw new Error("Server returned non-JSON response for wishlist.");
          });
        }
      })
      .then((data) => {
        if (data.success) {
          displayAlert(data.message, "success");
          // Dynamically update button state or remove item
          if (window.location.pathname.includes("wishlist.php")) {
            const wishlistItem = button.closest(".wishlist-item");
            if (wishlistItem) {
              wishlistItem.style.opacity = "0";
              wishlistItem.style.transition = "opacity 0.5s ease-out";
              setTimeout(() => wishlistItem.remove(), 500);
              // Reload or re-render if list becomes empty, or simply remove
              setTimeout(() => location.reload(), 500); // Reload to update "no records" or re-fetch list
            }
          } else {
            // On daftar-game.php
            if (data.status === "added") {
              button.classList.remove("btn-primary", "add-to-wishlist");
              button.classList.add("btn-danger", "remove-from-wishlist");
              if (currentIcon) {
                currentIcon.classList.remove("fa-heart");
                currentIcon.classList.add("fa-heart-broken");
              }
              button.innerHTML =
                '<i class="fas fa-heart-broken"></i> Hapus Wishlist';
            } else if (data.status === "removed") {
              button.classList.remove("btn-danger", "remove-from-wishlist");
              button.classList.add("btn-primary", "add-to-wishlist");
              if (currentIcon) {
                currentIcon.classList.remove("fa-heart-broken");
                currentIcon.classList.add("fa-heart");
              }
              button.innerHTML = '<i class="fas fa-heart"></i> Tambah Wishlist';
            }
          }
        } else {
          displayAlert(data.message, "danger");
        }
      })
      .catch((error) => {
        console.error("Wishlist fetch error:", error);
        displayAlert(
          "Terjadi kesalahan saat mengelola wishlist. Coba lagi.",
          "danger"
        );
      })
      .finally(() => {
        button.disabled = false;
        if (currentIcon) currentIcon.classList.remove("fa-spin", "fa-spinner");
      });
  }
}); // End DOMContentLoaded

const loadingContainer = document.querySelector(".loading-container");

setTimeout(() => {
  if (loadingContainer) {
    loadingContainer.style.opacity = "0";
    loadingContainer.style.transition = "opacity 0.5s ease-out";
    setTimeout(() => {
      loadingContainer.style.display = "none";
    }, 500);
  }
}, 1000);
