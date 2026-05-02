<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wallet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'parent_id', 'type'];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Wallet::class, 'parent_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'wallet_id');
    }

    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(Transaction::class, 'to_wallet_id');
    }
}
