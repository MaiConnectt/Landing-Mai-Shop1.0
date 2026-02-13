<?php
// Generate hash for admin123 to use in SQL file
$hash = password_hash('admin123', PASSWORD_DEFAULT);
echo $hash;
