<?php

namespace Hwkdo\IntranetAppPacktrack\Models;

use Hwkdo\IntranetAppPacktrack\Database\Factories\PacketdienstFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Packetdienst extends Model
{
    /** @use HasFactory<PacketdienstFactory> */
    use HasFactory;

    protected $table = 'packtrack_packetdienste';

    protected $guarded = [];

    public function pakete(): HasMany
    {
        return $this->hasMany(Paket::class);
    }
}
