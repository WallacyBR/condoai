<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Condominium;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index()
    {
        $condominiums = Condominium::where('active', true)->orderBy('name')->get();

        return view('units.index', compact('condominiums'));
    }
}
