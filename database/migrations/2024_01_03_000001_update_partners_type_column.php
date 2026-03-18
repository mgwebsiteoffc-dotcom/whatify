<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE partners MODIFY COLUMN type ENUM('agency', 'reseller', 'influencer', 'technology', 'freelancer', 'consultant') DEFAULT 'reseller'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE partners MODIFY COLUMN type ENUM('agency', 'reseller', 'influencer', 'technology') DEFAULT 'reseller'");
    }
};