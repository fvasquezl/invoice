<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'email',
        'phone',
        'logo',
        'template_id',
        'created_by',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    protected function logo(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => is_null($value)
            ? null
            : $value,
        );
    }
}
