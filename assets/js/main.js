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

  if (menuToggleBtn) {
    menuToggleBtn.addEventListener("click", function () {
      sidebar.classList.toggle("active");
    });
  }
});

document.addEventListener("DOMContentLoaded", function () {
  const allBtnTolak = document.querySelectorAll(".btn-tolak");

  allBtnTolak.forEach((button) => {
    button.addEventListener("click", function () {
      const idKoreksi = this.getAttribute("data-id_koreksi");

      const alasan = prompt("Harap masukkan alasan penolakan:");

      if (alasan !== null && alasan.trim() !== "") {
        window.location.href = `proses_koreksi.php?id=${idKoreksi}&status=Ditolak&alasan_admin=${encodeURIComponent(
          alasan
        )}`;
      } else if (alasan !== null) {
        alert("Alasan penolakan tidak boleh kosong.");
      }
    });
  });
});
