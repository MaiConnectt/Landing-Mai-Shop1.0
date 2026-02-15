<?php
/**
 * TEST SCRIPT - Verify fixed hashes work
 */

// These are the EXACT hashes from the SQL file
$admin_hash = '$2y$10$cnwQTD8nHIx2Z1qIUrCaouWcDtyyoVkGzE4TNfXlrByIgLUSV5/0S';
$vendor_hash = '$2y$10$mXYW56m2us6UIU/d7l36Supd193Puln2wsHbk8Jzqpbq.xb25L2lK';

echo "=== VERIFICACIÓN DE HASHES FIJOS EN SQL ===\n\n";

echo "Admin Hash:\n$admin_hash\n";
echo "Length: " . strlen($admin_hash) . " chars\n";
$admin_ok = password_verify('admin123', $admin_hash);
echo "password_verify('admin123'): " . ($admin_ok ? "✓ PASS" : "✗ FAIL") . "\n\n";

echo "Vendor Hash:\n$vendor_hash\n";
echo "Length: " . strlen($vendor_hash) . " chars\n";
$vendor_ok = password_verify('vendedor123', $vendor_hash);
echo "password_verify('vendedor123'): " . ($vendor_ok ? "✓ PASS" : "✗ FAIL") . "\n\n";

if ($admin_ok && $vendor_ok) {
    echo "✅ AMBOS HASHES VERIFICADOS CORRECTAMENTE\n";
    echo "El SQL ahora es idempotente - puedes ejecutarlo múltiples veces sin romper el login.\n";
} else {
    echo "❌ ERROR: Los hashes no verifican\n";
}
