<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorLog extends Model
{
    protected $fillable = ['property_id', 'tenant_id', 'visitor_name', 'visitor_phone', 'visitor_id_number', 'purpose', 'check_in', 'check_out', 'notes'];

    protected $casts = ['check_in' => 'datetime', 'check_out' => 'datetime'];

    public function property() { return $this->belongsTo(Property::class); }
    public function tenant() { return $this->belongsTo(Occupant::class, 'tenant_id'); }
}
