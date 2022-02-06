<?php

namespace App\Http\Controllers;

use App\Models\DB\Serie;
use Illuminate\Http\Request;

class SerieController extends Controller
{
    public function series()
    {
        $series = Serie::all();
        return response()->json($series);
    }

    public function serie(Serie $serie)
    {
        $temporadas = $serie->temporadas;
        return view('serie.viewer', compact('temporadas', 'serie'));
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
            Serie::create(['nome' => $request->nomeSerie]);
        } else {
            $serie = Serie::find($request->id);
            $serie->nome = $request->nomeSerie;
            $serie->save();
        }

        return response()->json(['message' => 'Série salva com sucesso']);
    }

    public function delete(Serie $serie)
    {
        $serie->delete();
        return response()->json(['message' => 'Série removida com sucesso']);
    }
}
