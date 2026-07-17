<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index()
    {
        return Tag::all();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $tag = Tag::create($validated);

        return response()->json($tag, 201);
    }

    public function show(string $id)
    {
        $tag = Tag::with('posts')->find($id);

        if (!$tag) {
            return response()->json(['message' => 'Tag not found'], 404);
        }

        return $tag;
    }

    public function update(Request $request, string $id)
    {
        $tag = Tag::find($id);

        if (!$tag) {
            return response()->json(['message' => 'Tag not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
        ]);

        $tag->update($validated);

        return $tag;
    }

    public function destroy(string $id)
    {
        $tag = Tag::find($id);

        if (!$tag) {
            return response()->json(['message' => 'Tag not found'], 404);
        }

        $tag->delete();

        return response()->json(['message' => 'Tag deleted'], 204);
    }
}
