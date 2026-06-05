<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = \App\Models\User::create([
    'name' => 'Dr. Ari',
    'email' => 'dokter@test.com',
    'password' => bcrypt('password123'),
    'role' => 'dokter',
]);

echo "✓ Dokter berhasil dibuat!\n";
echo "Email: dokter@test.com\n";
echo "Password: password123\n";
echo "Role: dokter\n";
