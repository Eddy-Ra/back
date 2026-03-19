<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class B2b_response_mailController extends Controller
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
            ->withoutVerifying() // 👈 manque ici !
            ->withHeaders([
            'apikey' => $this->supabaseKey,
            'Authorization' => 'Bearer ' . $this->supabaseKey,
        ])->get("{$this->supabaseUrl}/rest/v1/b2b_response_mail", [
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
            'id' => 'required|string',
            'created_at' => 'required|string',
            'threadId' => 'required|string',
            'snippet' => 'required|string',
            'payload' => 'required|string',
            'sizeEstimate' => 'required|string',
            'historyId' => 'required|string',
            'internalDate' => 'required|string', 
            'label' => 'required|string', 
            'To' => 'required|string', 
            'From' => 'required|string', 
            'Subject' => 'required|string', 
            'status_' => 'required|string', 
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = [$validator->validated()];

        $response = Http::withoutVerifying() // 👈 manque ici !
            ->withoutVerifying() // 👈 manque ici !
            ->withHeaders([
            'apikey' => $this->supabaseKey,
            'Authorization' => 'Bearer ' . $this->supabaseKey,
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ])->post("{$this->supabaseUrl}/rest/v1/b2b_relancemailsgen", $data);

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
            'id' => 'required|string',
            'created_at' => 'required|string',
            'threadId' => 'required|string',
            'snippet' => 'required|string',
            'payload' => 'required|string',
            'sizeEstimate' => 'required|string',
            'historyId' => 'required|string',
            'internalDate' => 'required|string', 
            'label' => 'required|string', 
            'To' => 'required|string', 
            'From' => 'required|string', 
            'Subject' => 'required|string', 
            'status_' => 'required|string', 
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        $response = Http::withoutVerifying() // 👈 manque ici !
            ->withoutVerifying() // 👈 manque ici !
            ->withHeaders([
            'apikey' => $this->supabaseKey,
            'Authorization' => 'Bearer ' . $this->supabaseKey,
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ])->patch("{$this->supabaseUrl}/rest/v1/b2b_relancemailsgen?id=eq.$id", $data);

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
            ->withoutVerifying() // 👈 manque ici !
            ->withHeaders([
            'apikey' => $this->supabaseKey,
            'Authorization' => 'Bearer ' . $this->supabaseKey,
            'Content-Type' => 'application/json',
        ])->delete("{$this->supabaseUrl}/rest/v1/b2b_relancemailsgen?id=eq.$id");

        if ($response->failed()) {
            return response()->json([
                'error' => 'Erreur lors de la suppression',
                'details' => $response->json(),
            ], 400);
        }

        return response()->json(['message' => "Mail généré #$id supprimé avec succès"], 200);
    }
}
