<?php

namespace Hwkdo\IntranetAppPacktrack\Models;

use App\Models\User;
use Hwkdo\IntranetAppPacktrack\Database\Factories\PaketFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Paket extends Model
{
    /** @use HasFactory<PaketFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'packtrack_pakete';

    protected $guarded = [];

    public function abholung(): HasOne
    {
        return $this->hasOne(Abholung::class);
    }

    public function packetdienst(): BelongsTo
    {
        return $this->belongsTo(Packetdienst::class);
    }

    public function empfaenger(): BelongsTo
    {
        return $this->belongsTo(User::class, 'empfaenger_id');
    }

    public function annehmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'annehmer_id');
    }

    public function geloeschtVon(): BelongsTo
    {
        return $this->belongsTo(User::class, 'geloescht_von');
    }

    public function istAbgeholt(): bool
    {
        return $this->abholung()->exists();
    }

    /** @return \Illuminate\Database\Eloquent\Builder<Paket> */
    public static function nichtAbgeholt(): \Illuminate\Database\Eloquent\Builder
    {
        return self::query()->whereDoesntHave('abholung');
    }

    /** @return \Illuminate\Database\Eloquent\Builder<Paket> */
    public static function nichtAbgeholtFuerUser(int $userId): \Illuminate\Database\Eloquent\Builder
    {
        return self::nichtAbgeholt()->where('empfaenger_id', $userId);
    }
}
