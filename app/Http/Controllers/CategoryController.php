<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * List all categories — public, no auth required.
     */
    public function index()
    {
        return Category::all();
    }

    /**
     * Create a new category — requires auth.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = Category::create($validated);

        return response()->json($category, 201);
    }

    /**
     * Show a single category with its posts — public.
     */
    public function show(string $id)
    {
        $category = Category::with('posts')->find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        return $category;
    }

    /**
     * Update a category — requires auth.
     */
    public function update(Request $request, string $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
        ]);

        $category->update($validated);

        return $category;
    }

    /**
     * Delete a category — requires auth.
     */
    public function destroy(string $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted'], 204);
    }
}
