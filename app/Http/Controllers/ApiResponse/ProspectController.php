<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProspectController extends Controller
{
    private $supabaseUrl;
    private $supabaseKey;

    public function __construct()
    {
        $this->supabaseUrl = env('SUPABASE_URL');
        $this->supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY');
    }

    // Récupérer tous les prospects
    public function index()
    {
        $response = Http::withoutVerifying() // 👈 manque ici !
            ->withHeaders([
            'apikey' => $this->supabaseKey,
            'Authorization' => 'Bearer ' . $this->supabaseKey,
        ])->get("{$this->supabaseUrl}/rest/v1/b2b_profils", [
            'select' => '*',
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Erreur de récupération des données'], 500);
        }

        return response()->json($response->json());
    }

    // Récupérer un prospect par ID
    public function show($id)
    {
        $response = Http::withoutVerifying() // 👈 manque ici !
            ->withHeaders([
            'apikey' => $this->supabaseKey,
            'Authorization' => 'Bearer ' . $this->supabaseKey,
        ])->get("{$this->supabaseUrl}/rest/v1/b2b_profils", [
            'id' => "eq.$id",
            'select' => '*',
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Prospect introuvable'], 404);
        }

        return response()->json($response->json());
    }

    // Créer un nouveau prospect
    public function store(Request $request)
    {
        $data = $request->only(['nom', 'email', 'societe']); // adapte selon les colonnes

        $response = Http::withoutVerifying() // 👈 manque ici !
            ->withHeaders([
            'apikey' => $this->supabaseKey,
            'Authorization' => 'Bearer ' . $this->supabaseKey,
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation'
        ])->post("{$this->supabaseUrl}/rest/v1/b2b_profils", $data);

        return response()->json($response->json());
    }

    // Mettre à jour un prospect
    public function update(Request $request, $id)
    {
        $data = $request->only(['nom', 'email', 'societe']);

        $response = Http::withoutVerifying() // 👈 manque ici !
            ->withHeaders([
            'apikey' => $this->supabaseKey,
            'Authorization' => 'Bearer ' . $this->supabaseKey,
            'Content-Type' => 'application/json'
        ])->patch("{$this->supabaseUrl}/rest/v1/b2b_profils?id=eq.$id", $data);

        return response()->json($response->json());
    }

    // Supprimer un prospect
    public function destroy($id)
    {
        $response = Http::withoutVerifying() // 👈 manque ici !
            ->withHeaders([
            'apikey' => $this->supabaseKey,
            'Authorization' => 'Bearer ' . $this->supabaseKey,
        ])->delete("{$this->supabaseUrl}/rest/v1/b2b_profils?id=eq.$id");

        return response()->json(['message' => 'Prospect supprimé avec succès']);
    }
}
