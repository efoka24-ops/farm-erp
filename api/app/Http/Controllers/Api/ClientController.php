<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Carnet clients (T062). */
class ClientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(
            Client::orderBy('nom')->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:150'],
            'telephone' => ['nullable', 'string', 'max:30'],
            'adresse' => ['nullable', 'string', 'max:255'],
        ]);

        $client = Client::create([...$data, 'exploitation_id' => $request->user()->exploitation_id]);

        return response()->json($client, 201);
    }
}
