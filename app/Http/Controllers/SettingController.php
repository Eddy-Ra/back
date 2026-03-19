<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::first() ?? Setting::create([
            'emailDefaut' => 'no-reply@omega-connect.tech',
            'nomExpediteur' => 'OmegaBrain',
            'frequenceEnvoi' => 50,
            'delaiEntreLots' => 5,
            'autoValidation' => false,
            'notificationsEmail' => true,
            'sauvegardeAuto' => true,
            'apiKeyN8n' => 'n8n_key_example_123...',
        ]);
        return response()->json($settings);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'emailDefaut' => 'required|email',
            'nomExpediteur' => 'required|string|max:255',
            'frequenceEnvoi' => 'required|integer|min:1',
            'delaiEntreLots' => 'required|integer|min:1',
            'autoValidation' => 'boolean',
            'notificationsEmail' => 'boolean',
            'sauvegardeAuto' => 'boolean',
            'apiKeyN8n' => 'required|string|max:255'
        ]);

        $settings = Setting::updateOrCreate(['id' => 1], $validated);

        // Mise à jour config mail dynamique
        config([
            'mail.from.address' => $settings->emailDefaut,
            'mail.from.name' => $settings->nomExpediteur
        ]);

        return response()->json($settings);
    }
}