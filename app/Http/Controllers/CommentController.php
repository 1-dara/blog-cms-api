<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Add a comment to a post — requires auth.
     */
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

    /**
     * Delete a comment — only the comment's author can delete it.
     */
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
