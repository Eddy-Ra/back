<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AiEmailResponse;

class AiEmailResponsesController extends Controller
{
    public function index(Request $request)
    {
        $page = max(1, (int) $request->get('page', 1));
        $perPage = min(100, max(1, (int) $request->get('per_page', 15)));
        $result = AiEmailResponse::list($page, $perPage);
        return response()->json($result);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
        ]);

     
        $created = AiEmailResponse::create($validated);
        return response()->json($created, 201);
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'email' => ['sometimes', 'email'],
            'subject' => ['sometimes', 'string'],
            'message' => ['sometimes', 'string'],
            'prospect_status' => ['sometimes', 'string'],
            'validated_by_admin' => ['sometimes', 'boolean'],
            'validated_at' => ['sometimes', 'date'],
            'sent_at' => ['sometimes', 'date'],
            'email_dispatched' => ['sometimes', 'boolean'],
        ]);

        $updated = AiEmailResponse::updateById($id, $validated);
        return response()->json($updated);
    }
}



