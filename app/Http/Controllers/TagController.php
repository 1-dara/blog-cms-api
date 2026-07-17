<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class TagController extends Controller
{
    #[OA\Get(
        path: "/api/tags",
        summary: "List all tags",
        tags: ["Tags"],
        responses: [
            new OA\Response(response: 200, description: "List of tags"),
        ]
    )]
    public function index()
    {
        return Tag::all();
    }

    #[OA\Post(
        path: "/api/tags",
        summary: "Create a new tag",
        tags: ["Tags"],
        security: [["bearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Laravel"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Tag created"),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 401, description: "Unauthenticated"),
        ]
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $tag = Tag::create($validated);

        return response()->json($tag, 201);
    }

    #[OA\Get(
        path: "/api/tags/{id}",
        summary: "Show a single tag with its posts",
        tags: ["Tags"],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(response: 200, description: "Tag found"),
            new OA\Response(response: 404, description: "Tag not found"),
        ]
    )]
    public function show(string $id)
    {
        $tag = Tag::with('posts')->find($id);

        if (!$tag) {
            return response()->json(['message' => 'Tag not found'], 404);
        }

        return $tag;
    }

    #[OA\Put(
        path: "/api/tags/{id}",
        summary: "Update a tag",
        tags: ["Tags"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer")),
        ],
        requestBody: new OA\RequestBody(
            required: false,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Updated Tag"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: "Tag updated"),
            new OA\Response(response: 404, description: "Tag not found"),
            new OA\Response(response: 401, description: "Unauthenticated"),
        ]
    )]
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

    #[OA\Delete(
        path: "/api/tags/{id}",
        summary: "Delete a tag",
        tags: ["Tags"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(response: 204, description: "Tag deleted"),
            new OA\Response(response: 404, description: "Tag not found"),
            new OA\Response(response: 401, description: "Unauthenticated"),
        ]
    )]
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
