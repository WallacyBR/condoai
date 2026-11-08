<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcurementAward extends Model
{
    use HasFactory;

    protected $fillable = ['procurement_request_id', 'procurement_item_id', 'supplier_quote_id', 'selected_by', 'criterion', 'justification'];

    public function quote()
    {
        return $this->belongsTo(SupplierQuote::class, 'supplier_quote_id');
    }

    public function item()
    {
        return $this->belongsTo(ProcurementItem::class, 'procurement_item_id');
    }
}
