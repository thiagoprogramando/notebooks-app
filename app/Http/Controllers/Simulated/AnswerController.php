<?php

namespace App\Http\Controllers\Simulated;

use App\Http\Controllers\Controller;

use App\Models\Question;
use App\Models\Simulated;
use App\Models\SimulatedQuestion;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnswerController extends Controller {

    public function show(Request $request, $uuid) {

        $simulated = Simulated::where('uuid', $uuid)->first();
        if (!$simulated) {
            return redirect()->back()->with('infor', 'Simulado não encontrado!');
        }

        $allQuestions = SimulatedQuestion::where('user_id', Auth::id())
            ->where('simulated_id', $simulated->id)
            ->orderBy('question_position')
            ->get();

        if ($allQuestions->isEmpty()) {
            return redirect()->back()->with(
                'infor',
                'Simulado não disponível! contate o suporte ou aguarde liberação.'
            );
        }

        if (
            ($allQuestions->firstWhere('answer_result', 0) === null &&
            $allQuestions->firstWhere('answer_result', 3) === null)
            || $simulated->date_end < now()
        ) {
            return redirect()->route('review-simulated', ['uuid' => $simulated->uuid]);
        }

        /*
        |--------------------------------------------------------------------------
        | DEFINIR PÁGINA
        |--------------------------------------------------------------------------
        */

        if ($request->has('page')) {
            $page = max(1, (int) $request->page);
        } else {

            $currentQuestionId  = $request->input('simulated_question_id');
            $currentPage        = max(1, (int) $request->input('current_page', 1));
            $nextPending        = $allQuestions
                                    ->whereIn('answer_result', [0, 3])
                                    ->where('id', '!=', $currentQuestionId)
                                    ->first();

            if (!$nextPending) {
                $nextPending = $allQuestions->where('id', '!=', $currentQuestionId)->firstWhere('answer_result', 3);
            }

            if ($nextPending) {
                $index = $allQuestions->search(fn($q) => $q->id === $nextPending->id);
                $page = $index + 1;
            } else {
                $page = 1;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | PAGINAÇÃO
        |--------------------------------------------------------------------------
        */

        $questions = SimulatedQuestion::where('user_id', Auth::id())
            ->where('simulated_id', $simulated->id)
            ->orderBy('question_position')
            ->paginate(1, ['*'], 'page', $page);

        session(['answer' => true]);

        return view('app.Simulated.Resolution.answer', [
            'simulated' => $simulated,
            'questions' => $questions,
            'currentPage' => $page,
        ]);
    }

    public function update(Request $request) {

        $simulatedQuestion = SimulatedQuestion::find($request->simulated_question_id);
        if (!$simulatedQuestion) {
            return response()->json([
                'success' => false,
                'message' => 'Questão não encontrada/indisponível!'
            ]);
        }

        $question = Question::find($simulatedQuestion->question_id);
        if (!$question) {
            return response()->json([
                'success' => false,
                'message' => 'Questão não encontrada/indisponível!'
            ]);
        }

        if ($request->answer_result == 3) {
            $simulatedQuestion->answer_result   = 3;
            $simulatedQuestion->resolved_at     = now();
            $simulatedQuestion->answer_id       = null;
            if ($simulatedQuestion->save()) {
                return response()->json([
                    'success' => true,
                    'redirect' => route('answer-simulated', [
                        'uuid' => $simulatedQuestion->simulated->uuid,
                        'simulated_question_id' => $request->simulated_question_id
                    ])
                ]);
            }

            return response()->json([
                    'success' => false,
                    'message' => 'Erro ao salvar a resposta.'
                ]);
        }

        $answer_id = $request->input('answer_id');
        if (!$answer_id) {
            return response()->json([
                'success' => false,
                'message' => 'Você precisa selecionar uma alternativa.'
            ]);
        }
        
        $isCorrect                          = $question->alternatives()->where('id', $answer_id)->where('is_correct', true)->exists();
        $simulatedQuestion->answer_id       = $answer_id;
        $simulatedQuestion->answer_result   = $isCorrect ? 1 : 2;
        $simulatedQuestion->resolved_at     = now();
        if ($simulatedQuestion->save()) {
            return response()->json([
                'success' => true,
                'redirect' => route('answer-simulated', [
                    'uuid' => $simulatedQuestion->simulated->uuid
                ])
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Erro ao salvar a resposta.'
        ]);
    }
}
