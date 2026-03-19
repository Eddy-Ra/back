<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class MailsgeneresController extends Controller
{
    protected $supabaseUrl;
    protected $supabaseKey;

    public function __construct()
    {
        $this->supabaseUrl = env('SUPABASE_URL');
        $this->supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY'); // clé service_role pour bypass RLS
    }

    /**
     * GET — Récupérer tous les mails générés
     */
    public function index()
    {
        $response = Http::withoutVerifying() // 👈 manque ici !
            ->withHeaders([
            'apikey' => $this->supabaseKey,
            'Authorization' => 'Bearer ' . $this->supabaseKey,
        ])->get("{$this->supabaseUrl}/rest/v1/b2b_generated_messages", [
            'select' => '*',
            'order' => 'id.desc',
        ]);

        if ($response->failed()) {
            return response()->json(['error' => $response->body()], 400);
        }

        return response()->json($response->json(), 200);
    }

    /**
     * POST — Insérer un nouveau mail généré
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'destinataire' => 'required|email',
            'sujet' => 'required|string',
            'contenu' => 'required|string',
            'categorie' => 'required|string',
            'statut' => 'required|string',
            'genereParIA' => 'required|boolean',
            'prompt_id' => 'nullable|integer', // ✅ ajout pris en compte
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
        ])->post("{$this->supabaseUrl}/rest/v1/b2b_generated_messages", $data);

        if ($response->failed()) {
            return response()->json(['error' => $response->body()], 400);
        }

        return response()->json([
            'message' => 'Insertion réussie',
            'data' => $response->json(),
        ], 201);
    }

    /**
     * PATCH — Mettre à jour un mail généré existant
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'destinataire' => 'sometimes|email',
            'sujet' => 'sometimes|string',
            'contenu' => 'sometimes|string',
            'categorie' => 'sometimes|string',
            'statut' => 'sometimes|string',
            'genereParIA' => 'sometimes|boolean',
            'prompt_id' => 'sometimes|nullable|integer', // ✅ support de la mise à jour du prompt_id
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
        ])->patch("{$this->supabaseUrl}/rest/v1/b2b_generated_messages?id=eq.$id", $data);

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

    /**
     * DELETE — Supprimer un mail généré
     */
    public function destroy($id)
    {
        $response = Http::withoutVerifying() // 👈 manque ici !
            ->withHeaders([
            'apikey' => $this->supabaseKey,
            'Authorization' => 'Bearer ' . $this->supabaseKey,
            'Content-Type' => 'application/json',
        ])->delete("{$this->supabaseUrl}/rest/v1/b2b_generated_messages?id=eq.$id");

        if ($response->failed()) {
            return response()->json([
                'error' => 'Erreur lors de la suppression',
                'details' => $response->json(),
            ], 400);
        }

        return response()->json(['message' => "Mail généré #$id supprimé avec succès"], 200);
    }
}
