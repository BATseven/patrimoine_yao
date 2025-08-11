<?php
$password = 'yao'; // Remplacez par le mot de passe souhaité, par exemple "admin123"
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
echo $hashedPassword;
?>