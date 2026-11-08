<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayableAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'condominium_id',
        'payable_id',
        'file_path',
        'original_name',
        'file_type',
        'mime_type',
        'file_size',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function payable()
    {
        return $this->belongsTo(Payable::class);
    }
}
