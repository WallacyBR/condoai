<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Condominium;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CondominiumController extends Controller
{
    public function index()
    {
        $condominiums = Condominium::orderBy('name')
            ->paginate(10);

        return view('condominiums.index', compact('condominiums'));
    }

    public function create()
    {
        return view('condominiums.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'document' => 'required|string|unique:condominiums',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:2',
            'zip_code' => 'required|string|max:9',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|unique:condominiums',
            'responsible' => 'required|string|max:255',
            'domain' => 'required|string|unique:condominiums|max:100',
            'active' => 'boolean',
        ]);

        $condominium = Condominium::create($validated);

        return redirect()
            ->route('condominiums.show', $condominium)
            ->with('success', 'Condomínio criado com sucesso!');
    }

    public function show(Condominium $condominium)
    {
        $condominium->load(['towers.blocks', 'blocks.apartments']);

        return view('condominiums.show', compact('condominium'));
    }

    public function edit(Condominium $condominium)
    {
        return view('condominiums.edit', compact('condominium'));
    }

    public function update(Request $request, Condominium $condominium)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'document' => ['required', 'string', Rule::unique('condominiums')->ignore($condominium)],
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:2',
            'zip_code' => 'required|string|max:9',
            'phone' => 'nullable|string|max:20',
            'email' => ['required', 'email', Rule::unique('condominiums')->ignore($condominium)],
            'responsible' => 'required|string|max:255',
            'domain' => ['required', 'string', 'max:100', Rule::unique('condominiums')->ignore($condominium)],
            'active' => 'boolean',
        ]);

        $condominium->update($validated);

        return redirect()
            ->route('condominiums.show', $condominium)
            ->with('success', 'Condomínio atualizado com sucesso!');
    }

    public function destroy(Condominium $condominium)
    {
        $condominium->delete();

        return redirect()
            ->route('condominiums.index')
            ->with('success', 'Condomínio removido com sucesso!');
    }
}
