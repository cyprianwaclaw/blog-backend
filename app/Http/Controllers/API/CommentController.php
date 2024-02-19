<?php

namespace App\Http\Controllers\API;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use App\Http\Requests\CommentRequest;
use App\Http\Requests\UpdateCommentRequest;

class CommentController extends Controller
{
    // private function formatDate($publishedAt, $dateDifference)
    // {
    //     if ($dateDifference === 0) {
    //         // Jeśli post opublikowany dzisiaj, wyświetl "dziś o {godzina}"
    //         return 'dziś o ' . $publishedAt->format('H:i');
    //     } elseif ($dateDifference === 1) {
    //         // Jeśli post opublikowany wczoraj, wyświetl "wczoraj o {godzina}"
    //         return 'wczoraj o ' . $publishedAt->format('H:i');
    //     } elseif ($dateDifference <= 14) {
    //         // Jeśli post opublikowany w ciągu ostatniego tygodnia, wyświetl "X dni temu"
    //         return $dateDifference . ' dni temu';
    //     } else {
    //         // W przeciwnym razie, wyświetl pełną datę
    //         return $publishedAt->format('d.m.Y');
    //     }
    // }

    public function createNewComment(CommentRequest $request)
    {
        $formData = $request->all();
        $newComment = Comment::create($formData);
        $publishedAt = Carbon::parse($newComment->created_at);
        $dateDifference = $publishedAt->diffInDays(now());

        return response()->json([
            'message' => 'Dodano nowy komentarz',
            'newCommentId' => $newComment->id,

        ]);
    }

    public function updateComment(UpdateCommentRequest $request)
    {
        $formData = $request->all();
        $comment = Comment::find($formData['comment_id']);

        if (isset($formData['relaction'])) {
            $comment->relaction = $formData['relaction'];

        }
        $comment->update($formData);

        return response()->json([
            'message' => 'Dodano nowy komentarz',
            "comment"=>$comment

        ]);
    }

    public function deleteComment(Request $request)
    {
        $commentId = $request->input('comment_id');

        $comment = Comment::find($commentId);
        if (!$comment) {
            return response()->json([
                'message' => 'Komentarz nie został znaleziony.'
            ], 404);
        }
        $comment->delete();
        return response()->json([
            'message' => 'Komentarz został pomyślnie usunięty.'
        ], 200);
    }
}