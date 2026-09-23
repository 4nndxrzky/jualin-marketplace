/**
 * ============================================================================
 * OLX CLONE — CLIENT-SIDE JAVASCRIPT (VANILLA JS)
 * Mendukung interaktivitas:
 * 1. Toggle Password Visibility (Login & Register)
 * 2. Real-time Password Match & Validation (Register)
 * 3. User Navigation Dropdown (Header Auth State)
 * 4. Auto-dismiss Flash Alerts
 * 5. Interactive Photo Gallery (Detail Page)
 * 6. Live Photo Preview & Drag-and-Drop (Pasang Iklan)
 * 7. Real-time Character Counter (Pasang Iklan)
 * ============================================================================
 */

document.addEventListener("DOMContentLoaded", () => {
  // ==========================================================================
  // 1. TOGGLE PASSWORD VISIBILITY
  // ==========================================================================
  const toggleButtons = document.querySelectorAll(".toggle-password");

  toggleButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const inputWrapper = this.closest(".input-wrapper");
      if (!inputWrapper) return;

      const input = inputWrapper.querySelector("input");
      if (!input) return;

      const isPassword = input.type === "password";
      input.type = isPassword ? "text" : "password";
      this.innerHTML = isPassword
        ? '<i class="fa-solid fa-eye-slash"></i>'
        : '<i class="fa-solid fa-eye"></i>';
      this.setAttribute(
        "aria-label",
        isPassword ? "Sembunyikan kata sandi" : "Tampilkan kata sandi",
      );
    });
  });

  // ==========================================================================
  // 2. REAL-TIME PASSWORD CONFIRMATION MATCH (REGISTER PAGE)
  // ==========================================================================
  const passwordInput = document.getElementById("password");
  const confirmInput = document.getElementById("password_confirmation");

  if (passwordInput && confirmInput) {
    // Buat elemen indikator pencocokan jika belum ada
    let indicator = document.querySelector(".password-indicator");
    if (!indicator) {
      indicator = document.createElement("div");
      indicator.className = "password-indicator";
      confirmInput.closest(".form-group").appendChild(indicator);
    }

    function checkPasswordMatch() {
      const pass = passwordInput.value;
      const confirm = confirmInput.value;

      if (!confirm) {
        indicator.style.display = "none";
        return;
      }

      indicator.style.display = "block";
      if (pass === confirm) {
        indicator.className = "password-indicator match";
        indicator.innerHTML =
          '<i class="fa-solid fa-circle-check"></i> Kata sandi cocok';
      } else {
        indicator.className = "password-indicator mismatch";
        indicator.innerHTML =
          '<i class="fa-solid fa-circle-xmark"></i> Kata sandi tidak cocok';
      }
    }

    passwordInput.addEventListener("input", checkPasswordMatch);
    confirmInput.addEventListener("input", checkPasswordMatch);
  }

  // ==========================================================================
  // 3. USER PROFILE DROPDOWN MENU (HEADER AUTH STATE)
  // ==========================================================================
  const userMenuBtn = document.querySelector(".user-menu-btn");
  const userDropdown = document.querySelector(".user-dropdown");

  if (userMenuBtn && userDropdown) {
    userMenuBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      userDropdown.classList.toggle("show");
    });

    document.addEventListener("click", (e) => {
      if (!userDropdown.contains(e.target) && !userMenuBtn.contains(e.target)) {
        userDropdown.classList.remove("show");
      }
    });

    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") {
        userDropdown.classList.remove("show");
      }
    });
  }

  // ==========================================================================
  // 4. AUTO-DISMISS & CLOSE BUTTON FOR ALERTS
  // ==========================================================================
  const alerts = document.querySelectorAll(".alert");

  alerts.forEach((alert) => {
    // Tombol close manual
    const closeBtn = alert.querySelector(".alert-close");
    if (closeBtn) {
      closeBtn.addEventListener("click", () => {
        dismissAlert(alert);
      });
    }

    // Auto-dismiss alert sukses setelah 5 detik
    if (alert.classList.contains("alert-success")) {
      setTimeout(() => {
        dismissAlert(alert);
      }, 5000);
    }
  });

  function dismissAlert(alertElement) {
    alertElement.style.opacity = "0";
    alertElement.style.transform = "translateY(-8px)";
    setTimeout(() => {
      alertElement.remove();
    }, 300);
  }

  // ==========================================================================
  // 5. INTERACTIVE IMAGE GALLERY (DETAIL PAGE)
  // ==========================================================================
  const galleryThumbs = document.querySelectorAll(".gallery-thumb-item");
  const galleryMain = document.querySelector(".gallery-main");
  const galleryCounter = document.querySelector(".gallery-counter");

  if (galleryThumbs.length > 0 && galleryMain) {
    galleryThumbs.forEach((thumb, index) => {
      thumb.addEventListener("click", function () {
        // Hapus kelas aktif dari semua thumbnail
        galleryThumbs.forEach((t) => {
          t.classList.remove("active");
          t.setAttribute("aria-selected", "false");
        });

        // Set thumbnail yang diklik menjadi aktif
        this.classList.add("active");
        this.setAttribute("aria-selected", "true");

        // Update counter teks (misal: 1 / 5)
        if (galleryCounter) {
          galleryCounter.innerHTML = `<i class="fa-solid fa-camera"></i> ${index + 1} / ${galleryThumbs.length}`;
        }

        // Jika ada tag <img> di dalam thumbnail, ganti src gambar utama
        const thumbImg = this.querySelector("img");
        const mainImg = galleryMain.querySelector("img");
        const mainPlaceholder = galleryMain.querySelector(
          ".gallery-placeholder",
        );

        if (thumbImg && mainImg) {
          mainImg.style.opacity = "0.4";
          setTimeout(() => {
            mainImg.src = thumbImg.src;
            mainImg.style.opacity = "1";
          }, 150);
        } else if (mainPlaceholder) {
          const iconEl = this.querySelector("i");
          const iconClass = iconEl ? iconEl.className : "fa-solid fa-image";
          mainPlaceholder.innerHTML = `<i class="${iconClass}" style="font-size: 5rem; color: var(--primary);"></i><span>Foto Tampilan ${index + 1}</span>`;
        }
      });
    });
  }

  // ==========================================================================
  // 6. LIVE PHOTO PREVIEW & DRAG-AND-DROP (PASANG IKLAN PAGE)
  // ==========================================================================
  const photoInput = document.getElementById("ad-images-input");
  const photoDropzone = document.querySelector(".photo-upload-zone");
  const photoSlots = document.querySelectorAll(".photo-slot");

  if (photoInput && photoSlots.length > 0) {
    // Handler file yang dipilih
    photoInput.addEventListener("change", function () {
      handleFiles(this.files);
    });

    // Drag & drop support
    if (photoDropzone) {
      ["dragenter", "dragover"].forEach((eventName) => {
        photoDropzone.addEventListener(eventName, (e) => {
          e.preventDefault();
          photoDropzone.style.borderColor = "var(--primary)";
          photoDropzone.style.backgroundColor = "rgba(35, 229, 219, 0.12)";
        });
      });

      ["dragleave", "drop"].forEach((eventName) => {
        photoDropzone.addEventListener(eventName, (e) => {
          e.preventDefault();
          photoDropzone.style.borderColor = "";
          photoDropzone.style.backgroundColor = "";
        });
      });

      photoDropzone.addEventListener("drop", (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        photoInput.files = files;
        handleFiles(files);
      });
    }

    function handleFiles(files) {
      if (!files || files.length === 0) return;

      const maxSlots = photoSlots.length;
      const count = Math.min(files.length, maxSlots);

      for (let i = 0; i < count; i++) {
        const file = files[i];
        if (!file.type.startsWith("image/")) continue;

        const reader = new FileReader();
        const slot = photoSlots[i];

        reader.onload = (e) => {
          slot.innerHTML = `<img src="${e.target.result}" alt="Preview Foto ${i + 1}">`;
          slot.style.borderStyle = "solid";
          slot.style.borderColor = "var(--primary)";
        };

        reader.readAsDataURL(file);
      }
    }
  }

  // ==========================================================================
  // 7. REAL-TIME CHARACTER COUNTER (PASANG IKLAN PAGE)
  // ==========================================================================
  const titleInput = document.getElementById("title");
  const titleCounter = document.getElementById("title-counter");

  if (titleInput && titleCounter) {
    titleInput.addEventListener("input", function () {
      const currentLength = this.value.length;
      titleCounter.textContent = `${currentLength} / 50 karakter`;
      if (currentLength >= 50) {
        titleCounter.style.color = "var(--danger)";
      } else {
        titleCounter.style.color = "var(--text-muted)";
      }
    });
  }
});
