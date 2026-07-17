<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CommentController extends Controller
{
    #[OA\Post(
        path: "/api/posts/{postId}/comments",
        summary: "Add a comment to a post",
        tags: ["Comments"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "postId", in: "path", required: true, schema: new OA\Schema(type: "integer")),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["body"],
                properties: [
                    new OA\Property(property: "body", type: "string", example: "Great post!"),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: "Comment created"),
            new OA\Response(response: 404, description: "Post not found"),
            new OA\Response(response: 422, description: "Validation error"),
            new OA\Response(response: 401, description: "Unauthenticated"),
        ]
    )]
    public function store(Request $request, string $postId)
    {
        $post = Post::find($postId);

        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $validated = $request->validate([
            'body' => 'required|string|max:1000',
        ]);

        $comment = $post->comments()->create([
            'body' => $validated['body'],
            'user_id' => $request->user()->id,
        ]);

        return response()->json($comment->load('user'), 201);
    }

    #[OA\Delete(
        path: "/api/comments/{id}",
        summary: "Delete your own comment",
        tags: ["Comments"],
        security: [["bearerAuth" => []]],
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, schema: new OA\Schema(type: "integer")),
        ],
        responses: [
            new OA\Response(response: 204, description: "Comment deleted"),
            new OA\Response(response: 403, description: "Not the comment owner"),
            new OA\Response(response: 404, description: "Comment not found"),
            new OA\Response(response: 401, description: "Unauthenticated"),
        ]
    )]
    public function destroy(Request $request, string $id)
    {
        $comment = Comment::find($id);

        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        if ($comment->user_id !== $request->user()->id) {
            return response()->json(['message' => 'You can only delete your own comments'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted'], 204);
    }
}
