<?php
// Generate correct hash for vendedor123
$password = 'vendedor123';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Password: $password\n";
echo "Hash: $hash\n\n";

// Verify it works
if (password_verify($password, $hash)) {
    echo "✓ Hash verified successfully\n";
} else {
    echo "✗ Hash verification failed\n";
}
