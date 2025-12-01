document.addEventListener("DOMContentLoaded", function () {
  const deleteButtons = document.querySelectorAll(".btn-hapus");
  deleteButtons.forEach((button) => {
    button.addEventListener("click", function (e) {
      const confirmation = confirm(
        "Apakah Anda yakin ingin menghapus data ini?"
      );
      if (!confirmation) {
        e.preventDefault();
      }
    });
  });

  const notif = document.querySelector(".notif-autohide");
  if (notif) {
    setTimeout(() => {
      notif.style.display = "none";
    }, 3000);
  }

  const themeToggleBtn = document.getElementById("theme-toggle");
  const body = document.body;

  function applyTheme(theme) {
    if (theme === "dark") {
      body.classList.add("dark-mode");
    } else {
      body.classList.remove("dark-mode");
    }
  }

  const currentTheme = localStorage.getItem("theme");
  applyTheme(currentTheme);

  if (themeToggleBtn) {
    themeToggleBtn.addEventListener("click", function () {
      body.classList.toggle("dark-mode");
      let theme = "light";
      if (body.classList.contains("dark-mode")) {
        theme = "dark";
      }
      localStorage.setItem("theme", theme);
    });
  }

  const menuToggleBtn = document.getElementById("mobile-menu-toggle");
  const sidebar = document.querySelector(".sidebar");
  const mainContent = document.querySelector(".main-content");

  if (menuToggleBtn) {
    menuToggleBtn.addEventListener("click", function (e) {
      e.stopPropagation();
      sidebar.classList.toggle("active");
    });
  }

  if (mainContent) {
    mainContent.addEventListener("click", function () {
      if (window.innerWidth <= 768 && sidebar.classList.contains("active")) {
        sidebar.classList.remove("active");
      }
    });
  }
});

document.addEventListener("DOMContentLoaded", function () {
  document.body.addEventListener("click", function (e) {
    if (e.target && e.target.classList.contains("btn-tolak")) {
      e.preventDefault();

      const idKoreksi = e.target.getAttribute("data-id_koreksi");

      const alasan = prompt("Harap masukkan alasan penolakan:");

      if (alasan !== null) {
        if (alasan.trim() !== "") {
          window.location.href = `proses_koreksi.php?id=${idKoreksi}&aksi=tolak&alasan_admin=${encodeURIComponent(
            alasan
          )}`;
        } else {
          alert("Alasan penolakan tidak boleh kosong.");
        }
      }
    }
  });
});
