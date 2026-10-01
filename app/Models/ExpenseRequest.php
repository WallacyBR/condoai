<?php

namespace App\Models;

use App\Models\Scopes\CondominiumScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpenseRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::addGlobalScope(new CondominiumScope);
    }

    protected $fillable = [
        'condominium_id',
        'chart_account_id',
        'requested_by',
        'amount',
        'title',
        'reference',
        'due_date',
        'description',
        'paid_at',
        'payable_id',
        'submitted_at',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'approved_at' => 'datetime',
        'paid_at' => 'date',
        'submitted_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function attachments()
    {
        return $this->hasMany(ExpenseRequestAttachment::class);
    }

    public function approvals()
    {
        return $this->hasMany(ExpenseRequestApproval::class)->orderBy('approval_order');
    }

    public function payable()
    {
        return $this->belongsTo(Payable::class);
    }

    public function chartAccount()
    {
        return $this->belongsTo(ChartAccount::class);
    }
}
