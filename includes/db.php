<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db_name = 'si_guru';

$conn = new mysqli($host, $user, $pass, $db_name);

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}
?>