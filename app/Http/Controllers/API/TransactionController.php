<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Payable;
use App\Enums\TransactionType;
use App\Models\CashFlow;
use App\Models\Receivable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type'); // 'payable' or 'receivable'
        $query = $request->query('query');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $payables = collect();
        $receivables = collect();

        if (!$type || $type === 'payable') {
            $payables = Payable::query();
            if ($query) {
                $payables->where('description', 'like', '%' . $query . '%');
            }
            if ($startDate) {
                $payables->whereDate('due_date', '>=', $startDate);
            }
            if ($endDate) {
                $payables->whereDate('due_date', '<=', $endDate);
            }
            $payables = $payables->get()->map(function ($item) {
                $item->type = 'payable';
                return $item;
            });
        }

        if (!$type || $type === 'receivable') {
            $receivables = Receivable::query();
            if ($query) {
                $receivables->where('description', 'like', '%' . $query . '%');
            }
            if ($startDate) {
                $receivables->whereDate('due_date', '>=', $startDate);
            }
            if ($endDate) {
                $receivables->whereDate('due_date', '<=', $endDate);
            }
            $receivables = $receivables->get()->map(function ($item) {
                $item->type = 'receivable';
                return $item;
            });
        }

        $transactions = $payables->merge($receivables)->sortByDesc(function ($transaction) {
            return $transaction->due_date;
        })->values();

        return response()->json($transactions);
    }

    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:payable,receivable',
            'condominium_id' => 'required|exists:condominiums,id',
            'chart_account_id' => 'required|exists:chart_accounts,id',
            'bank_id' => 'nullable|exists:banks,id',
            'due_date' => 'required|date',
            'amount' => 'required|numeric',
            'status' => 'required|string',
            'description' => 'nullable|string',
            'paid_at' => 'nullable|date',
            'received_at' => 'nullable|date',
        ]);

        try {
            DB::beginTransaction();

            if ($request->type === 'payable') {
                $transaction = Payable::create($request->except(['type', 'received_at']));
                CashFlow::create([
                    'condominium_id' => $transaction->condominium_id,
                    'payable_id' => $transaction->id,
                    'date' => $transaction->due_date,
                    'amount' => $transaction->amount,
                    'type' => TransactionType::DESPESA,
                    'description' => $transaction->description,
                ]);
            } else {
                $transaction = Receivable::create($request->except(['type', 'paid_at']));
                CashFlow::create([
                    'condominium_id' => $transaction->condominium_id,
                    'receivable_id' => $transaction->id,
                    'date' => $transaction->due_date,
                    'amount' => $transaction->amount,
                    'type' => TransactionType::RECEITA,
                    'description' => $transaction->description,
                ]);
            }

            DB::commit();
            return response()->json($transaction, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error creating transaction', 'error' => $e->getMessage()], 500);
        }
    }
}