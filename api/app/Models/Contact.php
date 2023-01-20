<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Traits\Tappable;

class Contact extends Model
{
    use HasFactory, HasUlids, SoftDeletes, Tappable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'prenom',
        'nom',
        'email',
        'telephone',
        'remarque',
    ];

    /**
     * Relation établie par la clé `user_id`
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    } 

    /**
     * Relation établie par la clé `adresse_id`
     *
     * @return BelongsTo
     */
    public function adresse(): BelongsTo
    {
        return $this->belongsTo(Adresse::class);
    }
}
