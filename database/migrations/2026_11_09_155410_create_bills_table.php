<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('vendor');
            $table->string('bill_number')->nullable();
            $table->date('bill_date');
            $table->decimal('stated_total', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'bill_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
