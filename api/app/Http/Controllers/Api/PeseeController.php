<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Animal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PeseeController extends Controller
{
    public function index(Animal $animal): JsonResponse
    {
        return response()->json($animal->pesees()->get());
    }

    public function store(Request $request, Animal $animal): JsonResponse
    {
        $data = $request->validate([
            'poids_kg' => ['required', 'numeric', 'min:0', 'max:9999'],
            'date_pesee' => ['required', 'date'],
        ]);

        $pesee = $animal->pesees()->create([
            ...$data,
            'exploitation_id' => $animal->exploitation_id,
            'saisi_par' => $request->user()->id,
        ]);

        return response()->json($pesee, 201);
    }
}
