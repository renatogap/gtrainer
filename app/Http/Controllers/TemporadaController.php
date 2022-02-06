<?php

namespace App\Http\Controllers;

use App\Models\DB\Serie;
use App\Models\DB\Temporada;
use Illuminate\Http\Request;

class TemporadaController extends Controller
{
    public function temporadas(Serie $serie)
    {
        return response()->json();
    }

    public function create()
    {
        return view('serie.create');
    }

    public function edit(Serie $serie)
    {
        return response()->json($serie);
    }

    public function store(Request $request)
    {
        if (!$request->id) {
            Temporada::create(['nome' => $request->temporadas, 'fk_serie' => $request->serie, 'episodios' => $request->episodios]);
        } else {
            $serie = Temporada::find($request->id);
            $serie->nome = $request->temporadas;
            $serie->episodios = $request->episodios;
            $serie->save();
        }

        return response()->json(['message' => 'Temporada salva com sucesso']);
    }

    public function delete(Serie $serie)
    {
        $serie->delete();
        return response()->json(['message' => 'Temporada removida com sucesso']);
    }
}
