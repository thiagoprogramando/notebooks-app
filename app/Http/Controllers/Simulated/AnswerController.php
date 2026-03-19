<?php

namespace App\Http\Controllers\Simulated;

use App\Http\Controllers\Controller;

use App\Models\Question;
use App\Models\Simulated;
use App\Models\SimulatedQuestion;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AnswerController extends Controller {

    public function show(Request $request, $uuid) {

        $simulated = Simulated::where('uuid', $uuid)->first();
        if (!$simulated) {
            return redirect()->back()->with('infor', 'Simulado não encontrado/disponível!');
        }

        $allQuestions = SimulatedQuestion::where('user_id', Auth::id())->where('simulated_id', $simulated->id)->orderBy('question_position')->get();
        if ($allQuestions->isEmpty()) {
            return redirect()->back()->with('infor', 'Questões não disponíveis ainda!');
        }

        if (Carbon::parse($simulated->date_end)->endOfDay() < now()) {
            return redirect()->route('review-simulated', ['uuid' => $simulated->uuid]);
        }


        $action     = $request->input('action');
        $questionId = $request->input('question_id');

        if ($action === 'skip' && $questionId) {

            $q = $allQuestions->firstWhere('id', $questionId);
            if ($q && $q->answer_result == 0) {
                $q->update([
                    'answer_result' => 3,
                    'resolved_at'   => now()
                ]);
            }
        }

        if ($questionId) {
            $current = $allQuestions->firstWhere('id', $questionId);
        }
        if (empty($current)) {
            $current = $allQuestions->firstWhere('answer_result', 0);
        }
        if (!$current) {
            $current = $allQuestions->firstWhere('answer_result', 3);
        }
        if (!$current) {
            $current = $allQuestions->first();
        }

        return view('app.Simulated.Resolution.answer', [
            'simulated'     => $simulated,
            'question'      => $current,
            'allQuestions'  => $allQuestions,
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

        if ($request->answer_result == 3 && ($simulatedQuestion->answer_result == 0 || $simulatedQuestion->answer_result == 3)) {
            $simulatedQuestion->update([
                'answer_result' => 3,
                'answer_id'     => null,
                'resolved_at'   => now()
            ]);
        } else {
            $answer_id = $request->input('answer_id');
            if (!$answer_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você precisa selecionar uma alternativa.'
                ]);
            }

            $isCorrect = $question->alternatives()->where('id', $answer_id)->where('is_correct', true)->exists();
            $simulatedQuestion->update([
                'answer_id'     => $answer_id,
                'answer_result' => $isCorrect ? 1 : 2,
                'resolved_at'   => now()
            ]);
        }


        $allQuestions   = SimulatedQuestion::where('user_id', Auth::id())->where('simulated_id', $simulatedQuestion->simulated_id)->orderBy('question_position')->get();
        $next           = $allQuestions->where('question_position', '>', $simulatedQuestion->question_position)->first();
        if (!$next) {
            $next = $allQuestions->firstWhere('answer_result', 0);
        }
        if (!$next) {
            $next = $allQuestions
                ->where('id', '!=', $simulatedQuestion->id)
                ->firstWhere('answer_result', 3);
        }
        if ($next) {
            return response()->json([
                'success' => true,
                'redirect' => route('answer-simulated', [
                    'uuid'        => $simulatedQuestion->simulated->uuid,
                    'question_id' => $next->id
                ])
            ]);
        }

        return response()->json([
            'success'   => true,
            'redirect'  => route('review-simulated', [
                'uuid'  => $simulatedQuestion->simulated->uuid
            ])
        ]);
    }
}
