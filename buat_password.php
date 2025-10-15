<?php
// File ini hanya untuk membuat hash password baru.
// Setelah digunakan, file ini boleh dihapus.

$passwordAsli = '123';
$hashBaru = password_hash($passwordAsli, PASSWORD_DEFAULT);

echo "<h1>Buat Password Hash Baru</h1>";
echo "<p>Gunakan kode hash di bawah ini untuk menggantikan password di database.</p>";
echo "<hr>";
echo "<b>Password Asli:</b> " . htmlspecialchars($passwordAsli) . "<br>";
echo "<b>Kode Hash Baru:</b> <pre>" . htmlspecialchars($hashBaru) . "</pre>";
echo "<hr>";
echo "<p><b>Instruksi:</b> Copy 'Kode Hash Baru', buka phpMyAdmin, edit salah satu user (misal: Guru1), dan paste kode ini ke kolom 'password'.</p>";
