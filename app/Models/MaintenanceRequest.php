<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceRequest extends Model
{
    use HasFactory;
    protected $fillable = [
        'room_id', 'occupant_id', 'assigned_to',
        'title', 'description', 'photos',
        'priority', 'status',
        'estimated_cost', 'actual_cost',
        'resolution_notes', 'resolution_photos',
        'resolved_at',
    ];

    protected $casts = [
        'photos'            => 'array',
        'resolution_photos' => 'array',
        'estimated_cost'    => 'float',
        'actual_cost'       => 'float',
        'resolved_at'       => 'datetime',
    ];

    public function room(): BelongsTo       { return $this->belongsTo(Room::class); }
    public function occupant(): BelongsTo   { return $this->belongsTo(Occupant::class); }
    public function assignedTo(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => 'danger', 'high' => 'warning',
            'medium' => 'info',   'low'  => 'success', default => 'gray',
        };
    }

    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => 'Urgent', 'high' => 'Tinggi',
            'medium' => 'Sedang', 'low'  => 'Rendah', default => $this->priority,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'open'          => 'danger', 'in_progress' => 'warning',
            'waiting_parts' => 'info',   'resolved'    => 'success',
            'cancelled'     => 'gray',   default       => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'open'          => 'Terbuka',    'in_progress'   => 'Dikerjakan',
            'waiting_parts' => 'Tunggu Part','resolved'      => 'Selesai',
            'cancelled'     => 'Dibatalkan', default         => $this->status,
        };
    }
}
