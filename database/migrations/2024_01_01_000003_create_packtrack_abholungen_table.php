<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packtrack_abholungen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paket_id')->constrained('packtrack_pakete')->cascadeOnDelete();
            $table->foreignId('ausgeber_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('abholer_id')->constrained('users')->cascadeOnDelete();
            $table->text('bemerkung')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packtrack_abholungen');
    }
};
