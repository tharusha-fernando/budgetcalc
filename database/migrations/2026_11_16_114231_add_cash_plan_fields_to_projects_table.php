<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('cash_on_hand', 12, 2)->nullable()->after('agreed_amount');
            $table->decimal('cash_to_load', 12, 2)->nullable()->after('cash_on_hand');
            $table->decimal('expected_final_total', 12, 2)->nullable()->after('cash_to_load');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'cash_on_hand',
                'cash_to_load',
                'expected_final_total',
            ]);
        });
    }
};
