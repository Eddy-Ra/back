<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SocieteController extends Controller{

    public function __construct()
    {
        $this->supabaseUrl = env('SUPABASE_URL_SOCIETE');
        $this->supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY8SOCIETE');
    }
    public function index()
    {
    $response = Http::withoutVerifying() // 👈 manque ici !
            ->withoutVerifying()
        ->timeout(60)
        ->withHeaders([
            'apikey'        => $this->supabaseKey,
            'Authorization' => 'Bearer ' . $this->supabaseKey,
        ])->get("{$this->supabaseUrl}/rest/v1/societe", [
            'select' => '*',
            'limit'  => 1000,
            'Pays'   => 'eq.Suisse', // 👈 Filtre Supabase
        ]);

    return response()->json($response->json(), $response->status());
    }
    
}