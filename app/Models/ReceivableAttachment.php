<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceivableAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'condominium_id',
        'receivable_id',
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

    public function receivable()
    {
        return $this->belongsTo(Receivable::class);
    }
}
