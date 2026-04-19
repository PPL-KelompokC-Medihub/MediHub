<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'fullname')) {
                $table->string('fullname')->nullable()->after('id');
            }

            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role')->nullable()->after('fullname');
            }

            if (! Schema::hasColumn('users', 'firebase_uid')) {
                $table->string('firebase_uid')->nullable()->unique()->after('email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'firebase_uid')) {
                $table->dropUnique(['firebase_uid']);
                $table->dropColumn('firebase_uid');
            }

            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }

            if (Schema::hasColumn('users', 'fullname')) {
                $table->dropColumn('fullname');
            }
        });
    }
};
