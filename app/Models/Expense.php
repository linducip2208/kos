<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = ['property_id', 'category', 'description', 'amount', 'expense_date', 'vendor', 'receipt_path', 'notes'];

    protected $casts = ['expense_date' => 'date', 'amount' => 'decimal:2'];

    public function property() { return $this->belongsTo(Property::class); }
}
