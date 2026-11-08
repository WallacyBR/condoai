<?php

namespace App\Http\Controllers\API;

use App\Enums\TransactionType;
use App\Http\Controllers\Concerns\ResolvesTenantCondominium;
use App\Http\Controllers\Controller;
use App\Models\CashFlow;
use App\Models\Payable;
use App\Models\Receivable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    use ResolvesTenantCondominium;

    public function index(Request $request)
    {
        $type = $request->query('type');
        $query = $request->query('query');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $payables = collect();
        $receivables = collect();

        if (! $type || $type === 'payable') {
            $payablesQuery = Payable::query();
            if ($query) {
                $payablesQuery->where('description', 'like', '%'.$query.'%');
            }
            if ($startDate) {
                $payablesQuery->whereDate('due_date', '>=', $startDate);
            }
            if ($endDate) {
                $payablesQuery->whereDate('due_date', '<=', $endDate);
            }
            $payables = $payablesQuery->get()->map(function ($item) {
                $item->type = 'payable';

                return $item;
            });
        }

        if (! $type || $type === 'receivable') {
            $receivablesQuery = Receivable::query();
            if ($query) {
                $receivablesQuery->where('description', 'like', '%'.$query.'%');
            }
            if ($startDate) {
                $receivablesQuery->whereDate('due_date', '>=', $startDate);
            }
            if ($endDate) {
                $receivablesQuery->whereDate('due_date', '<=', $endDate);
            }
            $receivables = $receivablesQuery->get()->map(function ($item) {
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
        $condoId = $this->tenantCondominiumId($request);

        $validated = $request->validate([
            'type' => 'required|in:payable,receivable',
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

            unset($validated['condominium_id']);
            $validated['condominium_id'] = $condoId;

            if ($request->type === 'payable') {
                $transaction = Payable::create(collect($validated)->except(['type', 'received_at'])->all());
                CashFlow::create([
                    'condominium_id' => $transaction->condominium_id,
                    'payable_id' => $transaction->id,
                    'date' => $transaction->due_date,
                    'amount' => $transaction->amount,
                    'type' => TransactionType::DESPESA,
                    'description' => $transaction->description,
                ]);
            } else {
                $transaction = Receivable::create(collect($validated)->except(['type', 'paid_at'])->all());
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
