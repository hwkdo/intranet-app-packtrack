<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packtrack_pakete', function (Blueprint $table) {
            $table->id();
            $table->string('nr')->unique();
            $table->foreignId('empfaenger_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('annehmer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('packetdienst_id')->constrained('packtrack_packetdienste')->cascadeOnDelete();
            $table->string('lieferant')->nullable();
            $table->text('bemerkung')->nullable();
            $table->string('geloescht_kommentar')->nullable();
            $table->foreignId('geloescht_von')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packtrack_pakete');
    }
};
