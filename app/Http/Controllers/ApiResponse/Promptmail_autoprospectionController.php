<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class Promptmail_autoprospectionController extends Controller
{
    protected $supabaseUrl;
    protected $supabaseKey;

    public function __construct()
    {
        $this->supabaseUrl = env('SUPABASE_URL');
        $this->supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY');
    }

    // GET — récupérer toutes les données
    public function index()
    {
        $response = Http::withoutVerifying() // 👈 manque ici !
            ->withHeaders([
            'apikey' => $this->supabaseKey,
            'Authorization' => 'Bearer ' . $this->supabaseKey,
        ])->get("{$this->supabaseUrl}/rest/v1/promptmail_autoprospection", [
            'select' => '*',
            'order' => 'id.desc',
        ]);

        if ($response->failed()) {
            return response()->json(['error' => $response->body()], 400);
        }

        return response()->json($response->json(), 200);
    }

    // POST — insérer une nouvelle ligne
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string',
            'contenu' => 'required|string',
            'categorie' => 'required|string',
            'utilise' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [$validator->validated()];

        $response = Http::withoutVerifying() // 👈 manque ici !
            ->withHeaders([
            'apikey' => $this->supabaseKey,
            'Authorization' => 'Bearer ' . $this->supabaseKey,
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ])->post("{$this->supabaseUrl}/rest/v1/promptmail_autoprospection", $data);

        if ($response->failed()) {
            return response()->json(['error' => $response->body()], 400);
        }

        return response()->json([
            'message' => 'Insertion réussie',
            'data' => $response->json(),
        ], 201);
    }

    // PATCH — mettre à jour une ligne existante
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'sometimes|string',
            'contenu' => 'sometimes|string',
            'categorie' => 'sometimes|string',
            'utilise' => 'sometimes|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        $response = Http::withoutVerifying() // 👈 manque ici !
            ->withHeaders([
            'apikey' => $this->supabaseKey,
            'Authorization' => 'Bearer ' . $this->supabaseKey,
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ])->patch("{$this->supabaseUrl}/rest/v1/promptmail_autoprospection?id=eq.$id", $data);

        if ($response->failed()) {
            return response()->json([
                'error' => 'Erreur lors de la mise à jour',
                'details' => $response->json(),
            ], 400);
        }

        return response()->json([
            'message' => 'Mise à jour réussie',
            'data' => $response->json(),
        ]);
    }

    // DELETE — supprimer une ligne
    public function destroy($id)
    {
        $response = Http::withoutVerifying() // 👈 manque ici !
            ->withHeaders([
            'apikey' => $this->supabaseKey,
            'Authorization' => 'Bearer ' . $this->supabaseKey,
            'Content-Type' => 'application/json',
        ])->delete("{$this->supabaseUrl}/rest/v1/promptmail_autoprospection?id=eq.$id");

        if ($response->failed()) {
            return response()->json([
                'error' => 'Erreur lors de la suppression',
                'details' => $response->json(),
            ], 400);
        }

        return response()->json(['message' => "Ligne #$id supprimée avec succès"], 200);
    }
}