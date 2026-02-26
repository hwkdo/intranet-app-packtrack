<?php

use Hwkdo\IntranetAppPacktrack\Models\Packetdienst;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packtrack_packetdienste', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        collect(['DHL', 'UPS', 'GLS', 'Amazon Logistics', 'DPD', 'Hermes', 'FedEx'])->each(
            fn (string $name) => Packetdienst::create(['name' => $name])
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('packtrack_packetdienste');
    }
};
