<?php

namespace App\Http\Controllers\Simulated;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Gateway\AssasController;
use App\Models\Invoice;
use App\Models\Simulated;
use App\Models\SimulatedQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SimulatedController extends Controller {
    
    public function index (Request $request) {

        $query = Simulated::query();

        if ($request->has('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        if (Auth::user()->role != 'admin') {
            $query->where('status', 'active');
        }

        return view('app.Simulated.index', [
            'simulateds' => $query->paginate(10),
        ]);
    }

    public function view ($uuid, $mode = null) {
        
        $simulated = Simulated::where('uuid', $uuid)->first();
        if (!$simulated) {
            return redirect()->back()->with('infor', 'Simulado não encontrado!');
        }

        if ($simulated->status == 'completed') {
            return redirect()->route('review-simulated', ['uuid' => $simulated->uuid]);
        }

        if (Carbon::parse($simulated->date_end) < now()) {
            return redirect()->route('review-simulated', ['uuid' => $simulated->uuid]);
        }

        return view('app.Simulated.Resolution.view', [
            'simulated' => $simulated,
            'mode'      => $mode == 'roles' ? 'roles' :'presentation'
        ]);
    }

    public function review ($uuid) {

        $simulated = Simulated::where('uuid', $uuid)->first();
        if (!$simulated) {
            return redirect()->back()->with('infor', 'Simulado não encontrado/disponível!');
        }

        $allQuestions = SimulatedQuestion::where('user_id', Auth::id())->where('simulated_id', $simulated->id)->orderBy('question_position')->get();
        if ($allQuestions->isEmpty()) {
            return redirect()->back()->with('infor', 'Questões não disponíveis ainda!');
        }

        if ($simulated->simulatedAnswers()->where('user_id', Auth::user()->id)->whereIn('answer_result', [0, 3])->exists() && Carbon::parse($simulated->date_end) > now()) {
            return redirect()->route('answer-simulated', ['uuid' => $simulated->uuid]);
        }

        $successCount = SimulatedQuestion::where('simulated_id', $simulated->id)
            ->where('user_id', Auth::user()->id)->where('answer_result', 1)
            ->count();

        $errorCount = SimulatedQuestion::where('simulated_id', $simulated->id)
            ->where('user_id', Auth::user()->id)->where('answer_result', 2)
            ->count();

        $total = $successCount + $errorCount;

        $percentSuccess = $total > 0 ? round(($successCount / $total) * 100, 2) : 0;
        $percentError   = $total > 0 ? round(($errorCount / $total) * 100, 2) : 0;

        $charts = [
            'general' => [
                'success'         => $successCount,
                'error'           => $errorCount,
                'percent_success' => $percentSuccess,
                'percent_error'   => $percentError,
            ],
        ];

        $ranking = SimulatedQuestion::select(
                'user_id',
                DB::raw("SUM(CASE WHEN answer_result = 1 THEN 1 ELSE 0 END) as total_points"),
                DB::raw("SUM(CASE WHEN answer_result <> 0 THEN 1 ELSE 0 END) as total_answered")
                )->where('simulated_id', $simulated->id)->groupBy('user_id')->orderByDesc('total_points')
                ->with(['user:id,name,address_state'])->get()->values()
                ->map(function ($item, $index) {
                    $item->position = $index + 1;
                    return $item;
                });

        return view('app.Simulated.Resolution.review', [
            'simulated' => $simulated,
            'charts'    => $charts,
            'ranking'   => $ranking,
        ]);
    }

    public function show ($uuid) {

        $simulated = Simulated::where('uuid', $uuid)->first();
        if (!$simulated) {
            return redirect()->back()->with('infor', 'Simulado não encontrado!');
        }

        $successCount = SimulatedQuestion::where('simulated_id', $simulated->id)
            ->where('answer_result', 1)
            ->count();

        $errorCount = SimulatedQuestion::where('simulated_id', $simulated->id)
            ->where('answer_result', 2)
            ->count();

        $total = $successCount + $errorCount;

        $percentSuccess = $total > 0 ? round(($successCount / $total) * 100, 2) : 0;
        $percentError   = $total > 0 ? round(($errorCount / $total) * 100, 2) : 0;

        $charts = [
            'general' => [
                'success'         => $successCount,
                'error'           => $errorCount,
                'percent_success' => $percentSuccess,
                'percent_error'   => $percentError,
            ],
        ];

        $ranking = SimulatedQuestion::select(
                'user_id',
                DB::raw("SUM(CASE WHEN answer_result = 1 THEN 1 ELSE 0 END) as total_points"),
                DB::raw("SUM(CASE WHEN answer_result <> 0 THEN 1 ELSE 0 END) as total_answered")
                )->where('simulated_id', $simulated->id)->groupBy('user_id')->orderByDesc('total_points')
                ->with(['user:id,name,address_state'])->get()->values()
                ->map(function ($item, $index) {
                    $item->position = $index + 1;
                    return $item;
                });

        return view('app.Simulated.show', [
            'simulated' => $simulated,
            'ranking'   => $ranking,
            'charts'    => $charts,
        ]);
    }

    public function store (Request $request) {

        $simulated                  = new Simulated();
        $simulated->uuid            = Str::uuid();
        $simulated->title           = $request->title;
        $simulated->value           = $this->formatValue($request->value);
        $simulated->presentation    = $request->presentation;
        $simulated->roles           = $request->roles;
        $simulated->caption         = $request->caption;
        $simulated->date_start      = $request->date_start;
        $simulated->date_end        = $request->date_end;
        $simulated->status          = $request->status;

        if ($request->hasFile('cover_image')) {
            $simulated->image = $request->file('cover_image')->store('simulateds', 'public');
        }

        if ($simulated->save()) {
            return redirect()->back()->with('success', 'Simulado criado com sucesso!');
        }

        return redirect()->back()->with('error', 'Falha ao criar o simulado, verifique os dados e tente novamente!');
    }

    public function update (Request $request, $uuid) {

        $simulated = Simulated::where('uuid', $uuid)->first();
        if (!$simulated) {
            return redirect()->back()->with('error', 'Simulado não encontrado!');
        }

        if ($request->filled('title')) {
            $simulated->title = $request->title;
        }
        if ($request->filled('value')) {
            $simulated->value = $this->formatValue($request->value);
        }
        if ($request->filled('presentation')) {
            $simulated->presentation = $request->presentation;
        }
        if ($request->filled('roles')) {
            $simulated->roles = $request->roles;
        }
        if ($request->filled('caption')) {
            $simulated->caption = $request->caption;
        }
        if ($request->filled('date_start')) {
            $simulated->date_start = $request->date_start;
        }
        if ($request->filled('date_end')) {
            $simulated->date_end = $request->date_end;
        }
        if ($request->filled('status')) {
            $simulated->status = $request->status;
        }
        if ($request->hasFile('cover_image')) {
            $simulated->image = $request->file('cover_image')->store('simulateds', 'public');
        }
        if ($simulated->save()) {
            return redirect()->back()->with('success', 'Simulado atualizado com sucesso!');
        }

        return redirect()->back()->with('error', 'Falha ao atualizar o simulado, verifique os dados e tente novamente!');
    }

    public function buy (Request $request, $uuid) {

        $assasController = new AssasController();

        $simulated = Simulated::where('uuid', $uuid)->first();
        if (!$simulated) {
            return redirect()->back()->with('error', 'Simulado não encontrado!');
        }

        if ($simulated->hasInvoice(Auth::id(), 1)) {
            return redirect()->route('invoices')->with('infor', 'Você já comprou o Simulado!');   
        }

        Invoice::where('user_id', Auth::id())
            ->where('payment_status', '<>', 1)
            ->where('simulated_id', $simulated->id)
            ->update(['payment_status' => 2]);

        if (env('APP_ENV') == 'local') {
            
            $invoice                  = new Invoice();
            $invoice->uuid            = Str::uuid();
            $invoice->user_id         = Auth::user()->id;
            $invoice->simulated_id    = $simulated->id;
            $invoice->payment_status  = 1;
            $invoice->value           = $simulated->value;
            $invoice->due_date        = now()->addDays(3);
            $invoice->payment_token   = Str::uuid();
            $invoice->payment_url     = '#';
            if ($invoice->save()) {

                $assasController->generateSimulatedForUser($invoice->user, $invoice->simulated);

                return redirect()->back()->with('infor', 'Compra aprovada em SANDBOX!');
            }
            
            return redirect()->back()->with('error', 'Falha ao gerar a cobrança, tente novamente!');
        } else {

            $customer = $assasController->createdCustomer(Auth::user()->name, Auth::user()->cpfcnpj, Auth::user()->phone, Auth::user()->email);
            if ($customer === false) {
                return redirect()->back()->with('error', 'Verfique seus dados e tente novamente!');   
            }

            $charge = $assasController->createdCharge($customer, $request->payment_method, $request->payment_installments, $value = $simulated->value, $description = 'Compra do Simulado: ' . $simulated->title, now()->addDays(3), $commissions = null);
            if ($charge === false) {
                return redirect()->back()->with('error', 'Falha ao gerar a cobrança, tente novamente!');   
            }

            $invoice                  = new Invoice();
            $invoice->uuid            = Str::uuid();
            $invoice->user_id         = Auth::user()->id;
            $invoice->simulated_id    = $simulated->id;
            $invoice->value           = $simulated->value;
            $invoice->due_date        = now()->addDays(3);
            $invoice->payment_splits  = $charge['paymentSplits'] ?? null;
            $invoice->payment_token   = $charge['id'];
            $invoice->payment_url     = $charge['invoiceUrl'];
            $invoice->payment_status  = 0;
            if ($invoice->save()) {
                return redirect($charge['invoiceUrl']);
            }

            return redirect()->back()->with('error', 'Falha ao gerar a cobrança, tente novamente!');
        }

        return redirect()->back()->with('error', 'Módulo indisponível!');
    }

    public function generateSimulatedForUser(Request $request) {

        if (Hash::check($request->password, Auth::user()->password)) {

            $simulated = Simulated::where('uuid', $request->uuid)->first();
            if (!$simulated) {
                return redirect()->back()->with('error', 'Simulado não encontrado!');
            }

            $questions = $simulated->questions()->orderBy('simulated_question_position')->get();
            if ($questions->isEmpty()) {
                return redirect()->back()->with('error', 'Não há questões associadas ao Simulado!');
            }

            $users = Invoice::where('simulated_id', $simulated->id)->where('payment_status', '1')->pluck('user_id');
            foreach ($users as $userId) {

                $existingQuestionIds    = SimulatedQuestion::where('user_id', $userId)->where('simulated_id', $simulated->id)->pluck('question_id')->toArray();
                $position               = SimulatedQuestion::where('user_id', $userId)->where('simulated_id', $simulated->id)->max('question_position') ?? 0;

                foreach ($questions as $q) {

                    if (in_array($q->id, $existingQuestionIds)) {
                        continue;
                    }

                    SimulatedQuestion::create([
                        'user_id'           => $userId,
                        'simulated_id'      => $simulated->id,
                        'question_id'       => $q->id,
                        'question_position' => ++$position,
                        'answer_result'     => 0,
                    ]);
                }
            }

            return redirect()->back()->with('success', 'Questões geradas para os usuários compradores!');
        }

        return redirect()->back()->with('error', 'Autenticação falhou, verifique os dados e tente novamente!');
    }

    public function destroy ($uuid) {

        $simulated = Simulated::where('uuid', $uuid)->first();
        if ($simulated && $simulated->delete()) {
            return redirect()->back()->with('success', 'Simulado excluído com sucesso!');
        }

        return redirect()->back()->with('error', 'Falha ao excluir o simulado, tente novamente!');
    }

    private function formatValue ($valor) {
        
        $valor = preg_replace('/[^0-9,]/', '', $valor);
        $valor = str_replace(',', '.', $valor);
        $valorFloat = floatval($valor);
    
        return number_format($valorFloat, 2, '.', '');
    }
}
