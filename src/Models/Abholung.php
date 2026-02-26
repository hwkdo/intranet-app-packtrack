<?php

namespace Hwkdo\IntranetAppPacktrack\Models;

use App\Models\User;
use Hwkdo\IntranetAppPacktrack\Database\Factories\AbholungFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Abholung extends Model
{
    /** @use HasFactory<AbholungFactory> */
    use HasFactory;

    protected $table = 'packtrack_abholungen';

    protected $guarded = [];

    public function paket(): BelongsTo
    {
        return $this->belongsTo(Paket::class);
    }

    public function ausgeber(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ausgeber_id');
    }

    public function abholer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'abholer_id');
    }
}
