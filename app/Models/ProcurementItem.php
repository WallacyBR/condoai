<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcurementItem extends Model
{
    use HasFactory;

    protected $fillable = ['procurement_request_id', 'description', 'specification', 'unit', 'quantity', 'position'];

    protected $casts = ['quantity' => 'decimal:3'];

    public function procurementRequest()
    {
        return $this->belongsTo(ProcurementRequest::class);
    }
}
