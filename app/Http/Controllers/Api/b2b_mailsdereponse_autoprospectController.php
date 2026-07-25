<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class b2b_mailsdereponse_autoprospectController extends Controller
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
        ])->get("{$this->supabaseUrl}/rest/v1/b2b_mailsdereponse_autoprospect", [
            'select' => '*',
             'order' => 'created_at.desc',
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
            'sujet' => 'required|string',
            'contenu' => 'required|string',
            
            'dateReponse' => 'required|string',
            'entreprise' => 'required|string', 
            'email' => 'required|string', 
            'categorie_entreprise' => 'required|string', 
            'cat_envoyer' => 'required|string', 
            'reponse_par_IA' => 'required|string',
            'thread_id' => 'required|string',
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
        ])->post("{$this->supabaseUrl}/rest/v1/b2b_mailsdereponse_autoprospect", $data);

        if ($response->failed()) {
            return response()->json(['error' => $response->body()], 400);
        }

        return response()->json([
            'message' => 'Insertion réussie',
            'data' => $response->json(),
        ], 201);
    }

    /**
     * PATCH — Mettre à jour un mail généré existant/// efa mandeha
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'statut' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'apikey'        => $this->supabaseKey,
                    'Authorization' => 'Bearer ' . $this->supabaseKey,
                    'Content-Type'  => 'application/json',
                    'Prefer'        => 'return=representation',
                ])->patch("{$this->supabaseUrl}/rest/v1/b2b_mailsreponses?id=eq.{$id}",  [
                    'statut' =>$request->statut,
                ]);
    
            return response()->json($response->json(), $response->status());
    
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur'], 500);
        }
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
        ])->delete("{$this->supabaseUrl}/rest/v1/b2b_mailsdereponse_autoprospect?id=eq.$id");

        if ($response->failed()) {
            return response()->json([
                'error' => 'Erreur lors de la suppression',
                'details' => $response->json(),
            ], 400);
        }

        return response()->json(['message' => "Mail généré #$id supprimé avec succès"], 200);
    }
}
