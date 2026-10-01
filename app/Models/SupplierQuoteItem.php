<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierQuoteItem extends Model
{
    use HasFactory;

    protected $fillable = ['supplier_quote_id', 'procurement_item_id', 'unit_price', 'quantity', 'notes'];

    protected $casts = ['unit_price' => 'decimal:2', 'quantity' => 'decimal:3'];

    public function procurementItem()
    {
        return $this->belongsTo(ProcurementItem::class);
    }
}
