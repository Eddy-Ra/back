<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    protected $supabaseUrl;
    protected $supabaseKey;

    public function __construct()
    {
        $this->supabaseUrl = env('SUPABASE_URL');
        $this->supabaseKey = env('SUPABASE_SERVICE_ROLE_KEY');
    }

    public function alluser()
    {
        $response = Http::withoutVerifying()
            ->withHeaders([
                'apikey'        => $this->supabaseKey,
                'Authorization' => 'Bearer ' . $this->supabaseKey,
            ])->get("{$this->supabaseUrl}/rest/v1/users", [
                'select' => '*',
                'order'  => 'created_at.desc',
            ]);

        return response()->json($response->json(), $response->status());
    }

    public function index()
    {
        Log::info('Récupération de tous les utilisateurs');
        return User::all();
    }

    public function show(User $user)
    {
        return $user;
    }

    public function store(Request $request)
    {
        Log::info('Données reçues:', $request->all());

        try {
            // 1. Validation
            $validated = $request->validate([
                'name'                  => 'required|string|max:255',
                'email'                 => 'required|string|email|max:255|unique:users',
                'password'              => 'nullable|string|min:4',
                'password_confirmation' => 'nullable|string|min:4',
                'role'                  => 'required|string|in:Rédacteur,Admin',
            ]);

            // 2. Vérifier confirmation password
            if (
                !empty($validated['password']) &&
                $validated['password'] !== ($validated['password_confirmation'] ?? null)
            ) {
                throw ValidationException::withMessages([
                    'password' => ['Les mots de passe ne correspondent pas.'],
                ]);
            }

            // 3. Hasher le password
            if (empty($validated['password'])) {
                $randomPassword = Str::random(12);
                $hashedPassword = Hash::make($randomPassword);
                Log::info('Password généré pour: ' . $validated['email']);
            } else {
                $hashedPassword = Hash::make($validated['password']);
            }

            // 4. Préparer les données pour Supabase (sans password_confirmation)
            $userData = [
                'name'              => $validated['name'],
                'email'             => $validated['email'],
                'password'          => $hashedPassword,
                'role'              => $validated['role'],
                'is_active'         => false,
                'email_verified_at' => now()->toISOString(),
                'last_login_at'     => now()->toISOString(),
                'created_at'        => now()->toISOString(),
                'updated_at'        => now()->toISOString(),
            ];

            // 5. Insérer dans Supabase
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'apikey'        => $this->supabaseKey,
                    'Authorization' => 'Bearer ' . $this->supabaseKey,
                    'Content-Type'  => 'application/json',
                    'Prefer'        => 'return=representation',
                ])->post("{$this->supabaseUrl}/rest/v1/users", $userData);

            if ($response->failed()) {
                Log::error('Erreur Supabase:', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return response()->json([
                    'error'  => 'Erreur lors de la création du compte',
                    'detail' => $response->json(),
                ], $response->status());
            }

            $createdUser = $response->json();
            Log::info('Utilisateur créé avec succès:', $createdUser);

            // Retourner le premier item si Supabase renvoie un tableau
            return response()->json(
                is_array($createdUser) && isset($createdUser[0]) ? $createdUser[0] : $createdUser,
                201
            );
        } catch (ValidationException $e) {
            Log::error('Erreurs de validation:', $e->errors());
            return response()->json([
                'message' => 'Validation échouée',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création:', [
                'error' => $e->getMessage(),
                'line'  => $e->getLine(),
                'file'  => $e->getFile(),
            ]);
            return response()->json([
                'error'  => 'Erreur interne du serveur',
                'detail' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, User $user)
    {
        Log::info('Mise à jour de l\'utilisateur:', ['id' => $user->id, 'data' => $request->all()]);

        $validated = $request->validate([
            'name'                  => 'sometimes|string|max:255',
            'email'                 => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password'              => 'sometimes|string|min:8',
            'password_confirmation' => 'sometimes|string|min:8',
            'role'                  => 'sometimes|string|in:Rédacteur,Admin',
            'is_active'             => 'sometimes|boolean',
        ]);

        if (
            !empty($validated['password']) &&
            $validated['password'] !== ($validated['password_confirmation'] ?? null)
        ) {
            return response()->json([
                'message' => 'Les mots de passe ne correspondent pas.',
            ], 422);
        }

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        unset($validated['password_confirmation']);
        $validated['updated_at'] = now()->toISOString();

        $user->update($validated);
        Log::info('Utilisateur mis à jour avec succès:', $user->toArray());

        return response()->json($user);
    }

    public function destroy(User $user)
    {
        Log::info('Suppression de l\'utilisateur:', ['id' => $user->id]);

        if ($user->role === 'Admin') {
            Log::warning('Tentative de suppression d\'un administrateur:', ['id' => $user->id]);
            return response()->json(['error' => 'Cannot delete admin user'], 403);
        }

        $user->delete();
        Log::info('Utilisateur supprimé avec succès:', ['id' => $user->id]);

        return response()->json(['message' => 'User deleted successfully'], 204);
    }

    public function verifyPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $verified = Hash::check($request->password, $user->password);

        return response()->json(['verified' => $verified]);
    }
    // app/Http/Controllers/UserController.php
    public function updateActiveStatus($id, Request $request)
    {
        try {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'apikey'        => $this->supabaseKey,
                    'Authorization' => 'Bearer ' . $this->supabaseKey,
                    'Content-Type'  => 'application/json',
                    'Prefer'        => 'return=representation',
                ])->patch("{$this->supabaseUrl}/rest/v1/users?id=eq.{$id}", [
                    'is_active' => $request->is_active,
                ]);

            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur'], 500);
        }
    }
    // AuthController.php
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
       
        $hashedPassword = Hash::make($validated['password']);
        return response()->json([
            
            'token' => $hashedPassword,
        ]);
    }
}
