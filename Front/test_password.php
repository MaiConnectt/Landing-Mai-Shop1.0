<?php
// Test password verification
$password = 'Admin@2026!';
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "Password: $password\n";
echo "Hash: $hash\n\n";

// Test verification
if (password_verify($password, $hash)) {
    echo "✓ Verification successful!\n";
} else {
    echo "✗ Verification failed!\n";
}

// Also test the hash in the SQL file
$sql_hash = '$2y$10$vLxKGJ8yZ9qH3mYqN0F8NeJxK7zQqH3mYqN0F8NeJxK7zQqH3mYqNe';
echo "\nTesting SQL hash:\n";
if (password_verify($password, $sql_hash)) {
    echo "✓ SQL hash works!\n";
} else {
    echo "✗ SQL hash DOES NOT work!\n";
}

// Test with 'password' (the old password)
echo "\nTesting with 'password':\n";
$old_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
if (password_verify('password', $old_hash)) {
    echo "✓ Old hash works with 'password'\n";
}
