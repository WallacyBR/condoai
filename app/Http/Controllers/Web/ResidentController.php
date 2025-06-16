<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Condominium;
use App\Models\Resident;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ResidentController extends Controller
{
    public function index(Request $request)
    {
        $query = Resident::with('condominium');

        if ($request->has('condominium_id')) {
            $query->where('condominium_id', $request->condominium_id);
        }

        $residents = $query->orderBy('name')->paginate(10);
        $condominiums = Condominium::pluck('name', 'id');

        return view('residents.index', compact('residents', 'condominiums'));
    }

    public function create()
    {
        $condominiums = Condominium::pluck('name', 'id');

        return view('residents.create', compact('condominiums'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|unique:residents',
            'condominium_id' => [
                'required',
                Rule::exists('condominiums', 'id')->where(fn ($q) => $q->where('active', true)),
            ],
            'active' => 'boolean',
        ]);

        $resident = Resident::create($validated);

        return redirect()
            ->route('residents.show', $resident)
            ->with('success', 'Morador criado com sucesso!');
    }

    public function show(Resident $resident)
    {
        $resident->load(['condominium', 'residingApartments.block.tower']);

        return view('residents.show', compact('resident'));
    }

    public function edit(Resident $resident)
    {
        $condominiums = Condominium::pluck('name', 'id');

        return view('residents.edit', compact('resident', 'condominiums'));
    }

    public function update(Request $request, Resident $resident)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => ['required', 'email', Rule::unique('residents')->ignore($resident->id)],
            'condominium_id' => [
                'required',
                Rule::exists('condominiums', 'id')->where(fn ($q) => $q->where('active', true)),
            ],
            'active' => 'boolean',
        ]);

        $resident->update($validated);

        return redirect()
            ->route('residents.show', $resident)
            ->with('success', 'Morador atualizado com sucesso!');
    }

    public function destroy(Resident $resident)
    {
        $resident->delete();

        return redirect()
            ->route('residents.index')
            ->with('success', 'Morador removido com sucesso!');
    }
}
