<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierQuoteAttachment extends Model
{
    use HasFactory;

    protected $fillable = ['supplier_quote_id', 'file_path', 'original_name', 'mime_type', 'file_size'];
}
