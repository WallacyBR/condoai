<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcurementInvitation extends Model
{
    use HasFactory;

    protected $fillable = ['procurement_request_id', 'supplier_id', 'supplier_name', 'supplier_email', 'token', 'expires_at', 'last_opened_at'];

    protected $hidden = ['token'];

    protected $casts = ['expires_at' => 'datetime', 'last_opened_at' => 'datetime'];

    public function procurementRequest()
    {
        return $this->belongsTo(ProcurementRequest::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function quote()
    {
        return $this->hasOne(SupplierQuote::class);
    }
}
