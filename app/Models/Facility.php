<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    protected $fillable = ['property_id', 'name', 'icon', 'description', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];

    public function property() { return $this->belongsTo(Property::class); }
    public function scopeActive($q) { return $q->where('is_active', true); }
}
