<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Testimonial extends Model
{
    use HasFactory;
    protected $fillable = [
        'property_id', 'name', 'occupation', 'avatar',
        'rating', 'content', 'order', 'is_active', 'tenant_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rating'    => 'integer',
        'order'     => 'integer',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('order');
    }
}
