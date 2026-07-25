<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class B2BDatasyncController extends Controller
{
    private $supabaseUrl;
    private $supabaseKey;

    public function __construct()
    {
        $this->supabaseUrl = env('SUPABASE_URL');
        $this->supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY');
    }

    /**
     * 🔹 Récupérer toutes les données
     */

    public function index()
    {
        $allData = [];
        $limit = 800000;
        $offset = 0;

        do {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'apikey'        => $this->supabaseKey,
                    'Authorization' => 'Bearer ' . $this->supabaseKey,
                ])->get("{$this->supabaseUrl}/rest/v1/b2b_datasynch", [
                    'select' => '*',
                    'order'  => 'id.desc',
                    'limit'  => $limit,
                    'offset' => $offset,
                ]);

            if ($response->failed()) {
                return response()->json(['error' => $response->body()], 400);
            }

            $data = $response->json();
            $allData = array_merge($allData, $data);
            $offset += $limit;

        } while (count($data) === $limit);

        return response()->json($allData, 200);
    }

    /**
     * 🔹 Ajouter un enregistrement
     */
    public function store(Request $request)
    {
        $data = [
            'full_name' => $request->input('full_name'),
            'email' => $request->input('email'),
            'company' => $request->input('company'),
            'source' => $request->input('source', 'Manuel'),
            'generateMessage' => $request->input('generateMessage', false),
            'category_id' => $request->input('category_id'),
            'created_at' => now(),
        ];

        $response = Http::withoutVerifying() // 👈 manque ici !
            ->withoutVerifying()->withHeaders([
            'apikey' => $this->supabaseKey,
            'Authorization' => 'Bearer ' . $this->supabaseKey,
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ])->post("{$this->supabaseUrl}/rest/v1/b2b_datasynch", $data);

        if ($response->failed()) {
            return response()->json(['error' => $response->body()], 500);
        }

        return response()->json(['message' => 'Contact ajouté avec succès', 'data' => $response->json()], 201);
    }

    /**
     * 🔹 Mettre à jour un enregistrement
     */
    public function update(Request $request, $id)
    {
        $data = [
            'full_name' => $request->input('full_name'),
            'email' => $request->input('email'),
            'company' => $request->input('company'),
            'source' => $request->input('source', 'Manuel'),
            'generateMessage' => $request->input('generateMessage', false),
            'category_id' => $request->input('category_id'),
            'updated_at' => now(),
        ];

        $response = Http::withoutVerifying() // 👈 manque ici !
            ->withoutVerifying()->withHeaders([
            'apikey' => $this->supabaseKey,
            'Authorization' => 'Bearer ' . $this->supabaseKey,
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ])->patch("{$this->supabaseUrl}/rest/v1/b2b_datasynch?id=eq.$id", $data);

        if ($response->failed()) {
            return response()->json(['error' => $response->body()], 500);
        }

        return response()->json(['message' => 'Contact mis à jour avec succès', 'data' => $response->json()]);
    }

    /**
     * 🔹 Supprimer un enregistrement
     */
    public function destroy($id)
    {
        $response = Http::withoutVerifying() // 👈 manque ici !
            ->withoutVerifying()->withHeaders([
            'apikey' => $this->supabaseKey,
            'Authorization' => 'Bearer ' . $this->supabaseKey,
        ])->delete("{$this->supabaseUrl}/rest/v1/b2b_datasynch?id=eq.$id");

        if ($response->failed()) {
            return response()->json(['error' => $response->body()], 500);
        }

        return response()->json(['message' => 'Enregistrement supprimé avec succès']);
    }
}
