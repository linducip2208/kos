<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomChecklist extends Model
{
    protected $fillable = [
        'lease_id', 'room_id', 'occupant_id', 'type',
        'items', 'photos', 'damage_cost', 'deposit_refund',
        'notes', 'signed_by', 'signed_at',
    ];

    protected $casts = [
        'items'          => 'array',
        'photos'         => 'array',
        'damage_cost'    => 'float',
        'deposit_refund' => 'float',
        'signed_at'      => 'datetime',
    ];

    public static array $defaultItems = [
        ['label' => 'Kunci Kamar',    'condition' => '', 'notes' => ''],
        ['label' => 'Pintu',          'condition' => '', 'notes' => ''],
        ['label' => 'Jendela',        'condition' => '', 'notes' => ''],
        ['label' => 'Lantai',         'condition' => '', 'notes' => ''],
        ['label' => 'Dinding',        'condition' => '', 'notes' => ''],
        ['label' => 'Plafon',         'condition' => '', 'notes' => ''],
        ['label' => 'Lampu',          'condition' => '', 'notes' => ''],
        ['label' => 'Stop Kontak',    'condition' => '', 'notes' => ''],
        ['label' => 'AC/Kipas Angin', 'condition' => '', 'notes' => ''],
        ['label' => 'Kamar Mandi',    'condition' => '', 'notes' => ''],
    ];

    public function lease(): BelongsTo    { return $this->belongsTo(Lease::class); }
    public function room(): BelongsTo     { return $this->belongsTo(Room::class); }
    public function occupant(): BelongsTo { return $this->belongsTo(Occupant::class); }
}
