<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestionCommentController extends Controller {
    
    public function store(Request $request) {

        $comment                = new Comment();
        $comment->user_id       = Auth::user()->id;
        $comment->question_id   = $request->question_id;
        $comment->comment       = $request->comment;
        if ($comment->save()) {
            return redirect()->back()->with('success', 'Comentário feito com sucesso!');
        }

        return redirect()->back()->with('success', 'Falha ao comentar a questão, tente novamente!');
    }

    public function update(Request $request) {

        $comment = Comment::find($request->id);
        if (!$comment) {
            return redirect()->back()->with('infor', 'Falha ao comentar a questão, tente novamente!');
        }

        $comment->user_answer_id    = Auth::user()->id;
        $comment->comment_answer    = $request->comment_answer;
        if ($comment->save()) {
            return redirect()->back()->with('success', 'Comentário feito com sucesso!');
        }

        return redirect()->back()->with('infor', 'Falha ao comentar a questão, tente novamente!');
    }
}
