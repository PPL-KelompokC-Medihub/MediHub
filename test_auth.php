<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = \App\Models\User::where('email', 'dokter@test.com')->first();

if ($user) {
    echo "User found!\n";
    echo "ID: " . $user->id . "\n";
    echo "Email: " . $user->email . "\n";
    echo "Role: " . $user->role . "\n";
    echo "Password hash: " . substr($user->password, 0, 20) . "...\n";
    
    if (password_verify('password123', $user->password)) {
        echo "✓ Password verification PASSED!\n";
    } else {
        echo "✗ Password verification FAILED!\n";
    }
} else {
    echo "User not found!\n";
}
