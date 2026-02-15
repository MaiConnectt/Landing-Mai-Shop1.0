<?php
// Generate VERIFIED hashes
$admin_hash = password_hash('admin123', PASSWORD_BCRYPT);
$vendor_hash = password_hash('vendedor123', PASSWORD_BCRYPT);

// Verify they work
$admin_ok = password_verify('admin123', $admin_hash);
$vendor_ok = password_verify('vendedor123', $vendor_hash);

echo "Admin hash (60 chars): $admin_hash\n";
echo "Admin verified: " . ($admin_ok ? "YES" : "NO") . "\n\n";

echo "Vendor hash (60 chars): $vendor_hash\n";
echo "Vendor verified: " . ($vendor_ok ? "YES" : "NO") . "\n";
