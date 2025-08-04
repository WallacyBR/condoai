<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Receivable;
use Illuminate\Http\Request;

class ReceivableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $receivables = Receivable::all();
        return response()->json($receivables);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'condominium_id' => 'required|exists:condominiums,id',
            'chart_account_id' => 'required|exists:chart_accounts,id',
            'bank_id' => 'nullable|exists:banks,id',
            'due_date' => 'required|date',
            'amount' => 'required|numeric',
            'status' => 'required|string',
            'description' => 'nullable|string',
            'received_at' => 'nullable|date',
        ]);

        $receivable = Receivable::create($request->all());
        return response()->json($receivable, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Receivable $receivable)
    {
        return response()->json($receivable);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Receivable $receivable)
    {
        $request->validate([
            'condominium_id' => 'required|exists:condominiums,id',
            'chart_account_id' => 'required|exists:chart_accounts,id',
            'bank_id' => 'nullable|exists:banks,id',
            'due_date' => 'required|date',
            'amount' => 'required|numeric',
            'status' => 'required|string',
            'description' => 'nullable|string',
            'received_at' => 'nullable|date',
        ]);

        $receivable->update($request->all());
        return response()->json($receivable);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Receivable $receivable)
    {
        $receivable->delete();
        return response()->json(null, 204);
    }
}