<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Payable;
use Illuminate\Http\Request;

class PayableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payables = Payable::all();
        return response()->json($payables);
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
            'paid_at' => 'nullable|date',
        ]);

        $payable = Payable::create($request->all());
        return response()->json($payable, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Payable $payable)
    {
        return response()->json($payable);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payable $payable)
    {
        $request->validate([
            'condominium_id' => 'required|exists:condominiums,id',
            'chart_account_id' => 'required|exists:chart_accounts,id',
            'bank_id' => 'nullable|exists:banks,id',
            'due_date' => 'required|date',
            'amount' => 'required|numeric',
            'status' => 'required|string',
            'description' => 'nullable|string',
            'paid_at' => 'nullable|date',
        ]);

        $payable->update($request->all());
        return response()->json($payable);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payable $payable)
    {
        $payable->delete();
        return response()->json(null, 204);
    }
}