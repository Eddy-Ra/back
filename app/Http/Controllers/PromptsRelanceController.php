<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PromptRelance;

class PromptsRelanceController extends Controller
{
    public function index(Request $request)
    {
        $page = max(1, (int) $request->get('page', 1));
        $perPage = min(100, max(1, (int) $request->get('per_page', 15)));
        $result = PromptRelance::list($page, $perPage);
        return response()->json($result);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'type_reponse' => ['required', 'string', 'max:255'],
            'subject_template' => ['required', 'string'],
            'message_template' => ['required', 'string'],
            'language' => ['sometimes', 'string', 'max:10'],
            'tags' => ['sometimes', 'array'],
            'active' => ['sometimes', 'boolean'],
        ]);

        
        $type = strtolower($validated['type_reponse']);
        $type = str_replace(['é', 'è', 'ê', 'ë'], 'e', $type);
        $type = str_replace(['à', 'â', 'ä'], 'a', $type);
        $type = str_replace(['ù', 'û', 'ü'], 'u', $type);
        $type = str_replace(['ô', 'ö'], 'o', $type);
        $type = str_replace(['î', 'ï'], 'i', $type);
        $type = str_replace(['ç'], 'c', $type);
        $type = str_replace(' ', '_', $type);
        $validated['type_reponse'] = $type;
        
       
        $validated['language'] = $validated['language'] ?? 'fr';
        $validated['tags'] = $validated['tags'] ?? [];
        $validated['active'] = $validated['active'] ?? true;
        $validated['use_count'] = 0;
        $validated['last_used_at'] = null;
        $validated['created_by_email'] = null;

        $created = PromptRelance::create($validated);
        return response()->json($created, 201);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'label' => ['sometimes', 'string', 'max:255'],
            'type_reponse' => ['sometimes', 'string', 'max:255'],
            'subject_template' => ['sometimes', 'string'],
            'message_template' => ['sometimes', 'string'],
            'language' => ['sometimes', 'string', 'max:10'],
            'tags' => ['sometimes', 'array'],
            'active' => ['sometimes', 'boolean'],
            'use_count' => ['sometimes', 'integer'],
        ]);
        $updated = PromptRelance::updateById($id, $validated);
        return response()->json($updated);
    }

    public function destroy(int $id)
    {
        PromptRelance::deleteById($id);
        return response()->noContent();
    }
}


