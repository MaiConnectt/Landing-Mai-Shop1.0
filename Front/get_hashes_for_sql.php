<?php
// Get proper hashes for SQL file
$admin_hash = password_hash('admin123', PASSWORD_DEFAULT);
$vendor_hash = password_hash('vendedor123', PASSWORD_DEFAULT);

echo "=== HASHES PARA SQL ===\n\n";
echo "Admin Hash (admin123):\n$admin_hash\n\n";
echo "Vendedor Hash (vendedor123):\n$vendor_hash\n\n";

echo "Hash lengths:\n";
echo "Admin: " . strlen($admin_hash) . " chars\n";
echo "Vendedor: " . strlen($vendor_hash) . " chars\n";
