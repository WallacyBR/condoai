<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierQuote extends Model
{
    use HasFactory;

    protected $fillable = ['procurement_invitation_id', 'supplier_id', 'supplier_name', 'supplier_document', 'supplier_email', 'supplier_phone', 'freight_amount', 'payment_terms', 'delivery_terms', 'notes', 'status', 'submitted_at'];

    protected $casts = ['freight_amount' => 'decimal:2', 'submitted_at' => 'datetime'];

    public function invitation()
    {
        return $this->belongsTo(ProcurementInvitation::class, 'procurement_invitation_id');
    }

    public function items()
    {
        return $this->hasMany(SupplierQuoteItem::class);
    }

    public function attachments()
    {
        return $this->hasMany(SupplierQuoteAttachment::class);
    }

    public function total(): float
    {
        return (float) $this->items->sum(fn ($item) => $item->unit_price * $item->quantity) + (float) $this->freight_amount;
    }
}
