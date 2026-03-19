<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Console\View\Components\Alert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use function Laravel\Prompts\alert;

class CategoryController extends Controller
{
    private $supabaseUrl;
    private $supabaseKey;

    public function __construct()
    {
        // 🛑 Utiliser la clé de service pour les opérations d'écriture sécurisées si elle est configurée comme telle
        $this->supabaseUrl = env('SUPABASE_URL');
        $this->supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY'); 
    }
    public function show($id)
    {

    $response = Http::withoutVerifying() // 👈 manque ici !
            ->withoutVerifying()->withHeaders([
            'apikey' => $this->supabaseKey,
            'Authorization' => 'Bearer ' . $this->supabaseKey,
        ])->get("{$this->supabaseUrl}/rest/v1/categories", [
            'id' => "eq.$id",
            'select' => '*',
            'order' => 'created_at.desc'
           
        ]);

        return response()->json($response->json(), $response->status());
    }

    // 🔹 Créer une catégorie
    public function store(Request $request)
    {
        $data = [
            'name' => $request->input('name'),
            'color' => $request->input('color'),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $response = Http::withoutVerifying() // 👈 manque ici !
            ->withoutVerifying() // 👈 manque ici !
            ->withHeaders([
            'apikey' => $this->supabaseKey,
            'Authorization' => 'Bearer ' . $this->supabaseKey,
            'Content-Type' => 'application/json',
            // Clé pour le temps réel : demande à Supabase de renvoyer l'objet créé
            'Prefer' => 'return=representation', 
        ])->post("{$this->supabaseUrl}/rest/v1/categories", $data);

        if ($response->failed()) {
            return response()->json(['error' => $response->body()], 500);
        }

        // Renvoie l'objet créé (Supabase renvoie un tableau, on prend le premier élément)
        return response()->json($response->json()[0] ?? [], 201);
    }

    // 🔹 Lire toutes les catégories
    public function index()
    {
        $response = Http::withoutVerifying() // 👈 manque ici !
            ->withoutVerifying()->withHeaders([
            'apikey' => $this->supabaseKey,
            'Authorization' => 'Bearer ' . $this->supabaseKey,
        ])->get("{$this->supabaseUrl}/rest/v1/categories", [
            'select' => '*',
            'order' => 'created_at.desc'
           
        ]);

        return response()->json($response->json(), $response->status());
    }

    // 🔹 Mettre à jour une catégorie
    public function update(Request $request, $id)
    {
        $data = [
            'name' => $request->input('name'),
            'color' => $request->input('color'),
            'updated_at' => now(),
        ];

        $response = Http::withoutVerifying() // 👈 manque ici !
            ->withoutVerifying() // 👈 manque ici !
            ->withHeaders([
            'apikey' => $this->supabaseKey,
            'Authorization' => 'Bearer ' . $this->supabaseKey,
            'Content-Type' => 'application/json',
            // Clé pour le temps réel : demande à Supabase de renvoyer l'objet mis à jour
            'Prefer' => 'return=representation',
        ])->patch("{$this->supabaseUrl}/rest/v1/categories?id=eq.$id", $data);

        if ($response->failed()) {
            return response()->json(['error' => $response->body()], 500);
        }

        // Renvoie l'objet mis à jour (Supabase renvoie un tableau, on prend le premier élément)
        return response()->json($response->json()[0] ?? []);
    }

    // 🔹 Supprimer une catégorie
    public function destroy($id)
    {
        $response = Http::withoutVerifying() // 👈 manque ici !
            ->withoutVerifying() // 👈 manque ici !
            ->withHeaders([
                'apikey' => $this->supabaseKey,
                'Authorization' => 'Bearer ' . $this->supabaseKey,
            ])->delete("{$this->supabaseUrl}/rest/v1/categories?id=eq.$id");

        if ($response->failed()) {
            return response()->json(['error' => $response->body()], 500);
        }

      

        return response()->noContent(); 
    }
}