<?php

namespace App\Http\Controllers\Simulated;

use App\Http\Controllers\Controller;

use App\Models\Simulated;
use App\Models\SimulatedQuestion;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewSimulatedController extends Controller {
    
    public function show ($simulted) {

        $simulated = Simulated::where('uuid', $simulted)->first();
        if (!$simulated) {
            return redirect()->back()->with('infor', 'Simulado não encontrado!');
        }

        $questions = SimulatedQuestion::where('simulated_id', $simulated->id)->where('user_id', Auth::user()->id)->whereIn('answer_result', [1, 2])->orderBy('question_position')->get();
        return view('app.Simulated.Review.simulated', [
            'simulated' => $simulated,
            'questions' => $questions
        ]);
    }
}
