<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PostController extends Controller
{
    #[OA\Get(
        path: "/api/posts",
        summary: "List published posts (paginated)",
        tags: ["Posts"],
        responses: [
            new OA\Response(response: 200, description: "Paginated list of posts"),
        ]
    )]
    public function index()
    {
        return Post::where('published', true)
            ->with(['user', 'category', 'tags'])
            ->latest()
            ->paginate(10);
    }

    #[OA\Post(
        path: "/api/posts",
        summary: "Create a new post",
        tags: ["Posts"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["title", "body"],
                properties: [
                    new OA\Property(property: "title", type: "string", example: "Getting Started with Laravel"),
                    new OA\Property(property: "body", type: "string", example: "Laravel is a PHP framework..."),
                    new OA\Property(property: "published", type: "boolean", example: true),
                    new OA\Property(property: "category_id", type: "integer", example: 1),
                    new OA\Property(property: "tags", type: "array", items: new OA\Items(type: "integer"), example: [1, 2]),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Post created"),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 401, description: "Unauthenticated"),
        ]
    )]
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

    #[OA\Get(
        path: "/api/posts/{id}",
        summary: "Show a single post with author, category, tags, and comments",
        tags: ["Posts"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(response: 200, description: "Post found"),
            new OA\Response(response: 404, description: "Post not found"),
        ]
    )]
    public function show(string $id)
    {
        $post = Post::with(['user', 'category', 'tags', 'comments.user'])->find($id);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        return $post;
    }

    #[OA\Put(
        path: "/api/posts/{id}",
        summary: "Update your own post",
        tags: ["Posts"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer")),
        ],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "title", type: "string", example: "Updated Title"),
                    new OA\Property(property: "body", type: "string", example: "Updated content"),
                    new OA\Property(property: "published", type: "boolean", example: true),
                    new OA\Property(property: "category_id", type: "integer", example: 1),
                    new OA\Property(property: "tags", type: "array", items: new OA\Items(type: "integer"), example: [1, 2]),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Post updated"),
            new OA\Response(response: 403, description: "Not the post owner"),
            new OA\Response(response: 404, description: "Post not found"),
            new OA\Response(response: 401, description: "Unauthenticated"),
        ]
    )]
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

    #[OA\Delete(
        path: "/api/posts/{id}",
        summary: "Delete your own post",
        tags: ["Posts"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(response: 204, description: "Post deleted"),
            new OA\Response(response: 403, description: "Not the post owner"),
            new OA\Response(response: 404, description: "Post not found"),
            new OA\Response(response: 401, description: "Unauthenticated"),
        ]
    )]
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
