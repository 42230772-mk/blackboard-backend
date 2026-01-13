<?php
$password = "admin123"; // the password you want to set
$hash = password_hash($password, PASSWORD_DEFAULT);
echo $hash;
