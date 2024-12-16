<?php
$password_plain = 'YourSecurePassword123!';
$hashed_password = password_hash($password_plain, PASSWORD_DEFAULT);
echo $hashed_password;
?>