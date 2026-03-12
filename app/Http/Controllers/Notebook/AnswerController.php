<?php

namespace App\Http\Controllers\Notebook;

use App\Http\Controllers\Controller;
use App\Models\Notebook;
use App\Models\NotebookQuestion;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnswerController extends Controller {
    
    public function index(Request $request, $notebookId, $questionId = null) {

        $notebook = Notebook::find($notebookId);
        if (!$notebook) {
            return redirect()->route('notebooks')->with('infor', 'Caderno não encontrado!');
        }

        $page = $request->has('page') ? (int) $request->page : 1;
        if ($questionId) {
            $notebookQuestions = NotebookQuestion::where('notebook_id', $notebookId)->where('question_id', $questionId)->paginate(1, ['*'], 'page', $page);
        } else {
            $notebookQuestions = NotebookQuestion::where('notebook_id', $notebookId)->where('answer_result', 0)->orderBy('question_position')->paginate(1, ['*'], 'page', $page);
        }

        session(['answer' => true]);

        if ($notebookQuestions->isEmpty()) {
            return redirect()->route('notebooks')->with('infor', 'Você já respondeu todas as questões deste caderno!');
        }

        return view('app.Notebook.answer', [
            'notebook'      => $notebook,
            'questions'     => $notebookQuestions,
            'currentPage'   => $page
        ]);
    }

    public function update(Request $request) {

        $notebookQuestion = NotebookQuestion::find($request->notebook_question_id);
        if (!$notebookQuestion) {
            return response()->json([
                'success' => false,
                'message' => 'Questão não encontrada!'
            ], 404);
        }

        $question = Question::find($notebookQuestion->question_id);
        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'Questão não encontrada!'
            ], 404);
        }

        if ($request->answer_result == 3) {

            $notebookQuestion->answer_result = 3;
            $notebookQuestion->answer_id = null;
            $notebookQuestion->resolved_at = now();

            if ($notebookQuestion->save()) {
                return response()->json([
                    'success' => true,
                    'redirect' => route('answer', [
                        'notebook' => $notebookQuestion->notebook_id
                    ])
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Erro ao pular questão.'
            ]);
        }


        $answer_id = $request->input('answer_id');
        if (!$answer_id) {
            return response()->json([
                'success' => false,
                'message' => 'Você precisa selecionar uma alternativa.'
            ]);
        }

        $isCorrect = $question->alternatives()
            ->where('id', $answer_id)
            ->where('is_correct', true)
            ->exists();

        $notebookQuestion->answer_id = $answer_id;
        $notebookQuestion->answer_result = $isCorrect ? 1 : 2;
        $notebookQuestion->resolved_at = now();

        if ($notebookQuestion->save()) {
            return response()->json([
                'success' => true,
                'redirect' => route('review-question', [
                    'question' => $notebookQuestion->id
                ])
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Erro ao salvar a resposta.'
        ]);
    }

    public function destroy($id) {
        
        $notebookQuestion = NotebookQuestion::find($id);
        if (!$notebookQuestion) {
            return redirect()->back()->with('infor', 'Questão não encontrada!');
        }

        $notebookId         = $notebookQuestion->notebook_id;
        $deletedPosition    = $notebookQuestion->question_position;

        if ($notebookQuestion->delete()) {
            NotebookQuestion::where('notebook_id', $notebookId)->where('question_position', '>', $deletedPosition)->orderBy('question_position')->decrement('question_position');
            return redirect()->back()->with('success', 'Questão deletada com sucesso!');
        }

        return redirect()->back()->with('infor', 'Erro ao deletar a questão. Tente novamente!');
    }

    public function review($questionId, $charts = null) {

        $question = NotebookQuestion::with(['notebook', 'question.alternatives'])->find($questionId);
        if (!$question) {
            return redirect()->back()->with('infor', 'Revisão indisponível para a Questão!');
        }

        $chosenId           = $question->answer_id;
        $chosenAlternative  = $chosenId
            ? $question->question->alternatives->firstWhere('id', $chosenId)
            : null;
        $answeredCorrect    = $chosenAlternative ? (bool) $chosenAlternative->is_correct : false;

        if (is_null($chosenId)) {
            $feedback = ['message' => 'Você não respondeu esta questão.', 'type' => 'secondary'];
        } elseif ($answeredCorrect) {
            $feedback = ['message' => '🎉 Você acertou, parabéns!', 'type' => 'success'];
        } else {
            $feedback = ['message' => '❌ Você errou! Mas isso é normal, siga focado nos estudos.!', 'type' => 'warning'];
        }

       if ($charts) {
            $userId = Auth::id();

            $totalSuccess = NotebookQuestion::where('question_id', $question->question->id)->where('answer_result', 1)->count();
            $totalError   = NotebookQuestion::where('question_id', $question->question->id)->where('answer_result', 2)->count();
            $total        = $totalSuccess + $totalError;

            $general = [
                'success' => $totalSuccess,
                'error'   => $totalError,
                'percent_success' => $total > 0 ? round(($totalSuccess / $total) * 100, 2) : 0,
                'percent_error'   => $total > 0 ? round(($totalError / $total) * 100, 2) : 0,
            ];

            $userSuccess = NotebookQuestion::where('question_id', $question->question->id)->where('user_id', $userId)->where('answer_result', 1)->count();
            $userError   = NotebookQuestion::where('question_id', $question->question->id)->where('user_id', $userId)->where('answer_result', 2)->count();
            $userTotal   = $userSuccess + $userError;

            $personal = [
                'success' => $userSuccess,
                'error'   => $userError,
                'percent_success' => $userTotal > 0 ? round(($userSuccess / $userTotal) * 100, 2) : 0,
                'percent_error'   => $userTotal > 0 ? round(($userError / $userTotal) * 100, 2) : 0,
            ];

            $charts = [
                'general'  => $general,
                'personal' => $personal,
            ];
       }

        return view('app.Notebook.review-question', [
            'notebook'        => $question->notebook,
            'question'        => $question,
            'chosenId'        => $chosenId,
            'answeredCorrect' => $answeredCorrect,
            'feedback'        => $feedback,
            'charts'          => $charts
        ]);
    }
}
