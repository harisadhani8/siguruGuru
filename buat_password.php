<?php
$password_plain = '123';
$hashed_password = password_hash($password_plain, PASSWORD_DEFAULT);
echo "Password Plain: " . $password_plain . "<br>";
echo "Password Hashed: " . $hashed_password;
