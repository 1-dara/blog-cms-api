<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * List published posts, paginated, with author/category/tags loaded.
     */
    public function index()
    {
        return Post::where('published', true)
            ->with(['user', 'category', 'tags'])
            ->latest()
            ->paginate(10);
    }

    /**
     * Create a new post — requires auth. Automatically sets the author.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'published' => 'boolean',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
        ]);

        $post = $request->user()->posts()->create([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'published' => $validated['published'] ?? false,
            'category_id' => $validated['category_id'] ?? null,
        ]);

        if (!empty($validated['tags'])) {
            $post->tags()->attach($validated['tags']);
        }

        return response()->json($post->load(['user', 'category', 'tags']), 201);
    }

    /**
     * Show a single post with all relationships and comments.
     */
    public function show(string $id)
    {
        $post = Post::with(['user', 'category', 'tags', 'comments.user'])->find($id);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        return $post;
    }

    /**
     * Update a post — only the author can update their own post.
     */
    public function update(Request $request, string $id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        if ($post->user_id !== $request->user()->id) {
            return response()->json(['message' => 'You can only edit your own posts'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'body' => 'sometimes|required|string',
            'published' => 'sometimes|boolean',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
        ]);

        $post->update($validated);

        if (isset($validated['tags'])) {
            $post->tags()->sync($validated['tags']);
        }

        return $post->load(['user', 'category', 'tags']);
    }

    /**
     * Delete a post — only the author can delete their own post.
     */
    public function destroy(Request $request, string $id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        if ($post->user_id !== $request->user()->id) {
            return response()->json(['message' => 'You can only delete your own posts'], 403);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted'], 204);
    }
}
