<?php
// Mendapatkan nama file dari URL untuk pengecekan kondisional
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGURU - Sistem Informasi Kehadiran Guru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script>
        (function() {
            const theme = localStorage.getItem('theme');
            if (theme === 'dark' || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
            }
        })();
    </script>
</head>

<body>
    <div class="mobile-container">
        <div class="header">
            <div class="header-brand">
                <?php if ($current_page == 'index.php'): ?>
                    <div class="header-logo">SIGURU</div>
                    <div class="header-title">SISTEM INFORMASI KEHADIRAN GURU</div>
                <?php else: ?>
                    <img src="/siguruGemini/includes/logo.png" alt="Logo SIGURU" class="header-app-logo">
                <?php endif; ?>
            </div>
            <button id="theme-toggle-btn" class="theme-toggle">🌙</button>
        </div>