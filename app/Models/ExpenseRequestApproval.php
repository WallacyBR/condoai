<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpenseRequestApproval extends Model
{
    use HasFactory;

    protected $fillable = ['expense_request_id', 'expense_approval_rule_id', 'approver_id', 'approval_order', 'decision', 'justification', 'decided_at'];

    protected $casts = ['decided_at' => 'datetime'];

    public function expenseRequest()
    {
        return $this->belongsTo(ExpenseRequest::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
