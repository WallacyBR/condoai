<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Visitor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VisitorController extends Controller
{
    /**
     * Listar visitantes
     */
    public function index(Request $request)
    {
        $query = Visitor::query();

        if ($request->has('apartment_id')) {
            $query->where('apartment_id', $request->apartment_id);
        }

        return $query->with(['apartment'])->get();
    }

    /**
     * Cadastrar visitante
     */
    public function store(Request $request)
    {
        $condoId = $request->condominium_id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'document' => 'required|string|max:255',
            'photo_path' => 'nullable|string|max:255',
            'apartment_id' => [
                'required',
                Rule::exists('apartments', 'id')->where(fn($q) => $q->where('condominium_id', $condoId)),
            ],
            'condominium_id' => [
                'required',
                Rule::exists('condominiums', 'id'),
            ],
            'visit_date' => 'required|date',
        ]);

        $visitor = Visitor::create($validated);

        return $visitor->load('apartment');
    }

    /**
     * Detalhar visitante
     */
    public function show(Visitor $visitor)
    {
        return $visitor->load('apartment');
    }

    /**
     * Atualizar visitante
     */
    public function update(Request $request, Visitor $visitor)
    {
        $condoId = $visitor->condominium_id;

        $validated = $request->validate([
            'name' => 'string|max:255',
            'document' => 'string|max:255',
            'photo_path' => 'nullable|string|max:255',
            'apartment_id' => [
                Rule::exists('apartments', 'id')->where(fn($q) => $q->where('condominium_id', $condoId)),
            ],
            'visit_date' => 'date',
        ]);

        $visitor->update($validated);
        return $visitor->load('apartment');
    }

    /**
     * Remover visitante
     */
    public function destroy(Visitor $visitor)
    {
        $visitor->delete();
        return response()->json(null, 204);
    }
}
