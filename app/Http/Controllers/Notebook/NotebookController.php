<?php

namespace App\Http\Controllers\Notebook;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Notebook;
use App\Models\NotebookQuestion;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotebookController extends Controller {
    
    public function index(Request $request) {

        $notebooks = Notebook::where('created_by', Auth::user()->id)->orderBy('created_at', 'desc')->paginate(10);
        return view('app.Notebook.list-notebooks', [
            'notebooks' => $notebooks
        ]);
    }

    public function show($id) {

        $notebook = Notebook::find($id);
        if (!$notebook) {
            return redirect()->back()->with('infor', 'Caderno não encontrado, verique os dados e tente novamente!');
        }

        $userId     = Auth::id();
        $contents   = Content::with([
            'topics' => function ($query) use ($userId) {
                $query->withCount([

                    // TOTAL DE QUESTÕES LIVRES
                    'questions' => function ($q) {
                        $q->whereNull('simulated_id');
                    },

                    // ACERTOS
                    'questions as resolved_count' => function ($q) use ($userId) {
                        $q->whereNull('simulated_id')
                        ->whereHas('notebookQuestions', function ($nq) use ($userId) {
                            $nq->where('user_id', $userId)
                                ->where('answer_result', 1);
                        });
                    },

                    // ERROS
                    'questions as failer_count' => function ($q) use ($userId) {
                        $q->whereNull('simulated_id')
                        ->whereHas('notebookQuestions', function ($nq) use ($userId) {
                            $nq->where('user_id', $userId)
                                ->where('answer_result', 2);
                        });
                    },

                    // ELIMINADAS (respondidas de qualquer forma)
                    'questions as eliminated_count' => function ($q) use ($userId) {
                        $q->whereNull('simulated_id')
                        ->whereHas('notebookQuestions', function ($nq) use ($userId) {
                            $nq->where('user_id', $userId)
                                ->whereIn('answer_result', [1,2]);
                        });
                    },

                    // FAVORITADAS
                    'questions as favorited_count' => function ($q) use ($userId) {
                        $q->whereNull('simulated_id')
                        ->whereHas('favorites', function ($fav) use ($userId) {
                            $fav->where('user_id', $userId);
                        })
                        ->whereNull('questions.deleted_at');
                    },
                ])

                // GARANTE QUE O TÓPICO SÓ APAREÇA SE TIVER QUESTÕES LIVRES
                ->whereHas('questions', function ($q) {
                    $q->whereNull('simulated_id');
                });
            }
        ])->where('status', 'active')->orderBy('order', 'asc')->get();

        $filters = is_array($notebook->filters) ? $notebook->filters : json_decode($notebook->filters, true);

        return view('app.Notebook.view-notebook', [
            'notebook'  => $notebook,
            'contents'  => $contents,
            'filters'   => $filters,
        ]);
    }

    public function create() {

        $userId     = Auth::id();
        $contents   = Content::with([
            'topics' => function ($query) use ($userId) {
                $query->leftJoin('topic_groups as g', 'topics.group_id', '=', 'g.id')
                    ->select('topics.*', 'g.order as group_order')
                    ->withCount([
                        'questions' => function ($q) {
                            $q->whereNull('simulated_id');
                        },
                        'questions as resolved_count' => function ($q) use ($userId) {
                            $q->whereNull('simulated_id')
                            ->whereHas('notebookQuestions', function ($nq) use ($userId) {
                                $nq->where('user_id', $userId)
                                    ->where('answer_result', 1);
                            });
                        },
                        'questions as failer_count' => function ($q) use ($userId) {
                            $q->whereNull('simulated_id')
                            ->whereHas('notebookQuestions', function ($nq) use ($userId) {
                                $nq->where('user_id', $userId)
                                    ->where('answer_result', 2);
                            });
                        },
                        'questions as eliminated_count' => function ($q) use ($userId) {
                            $q->whereNull('simulated_id')
                            ->whereHas('notebookQuestions', function ($nq) use ($userId) {
                                $nq->where('user_id', $userId)
                                    ->whereIn('answer_result', [1,2]);
                            });
                        },
                        'questions as favorited_count' => function ($q) use ($userId) {
                            $q->whereNull('simulated_id')
                            ->whereHas('favorites', function ($fav) use ($userId) {
                                $fav->where('user_id', $userId);
                            })->whereNull('questions.deleted_at');
                        },
                    ])
                    ->whereHas('questions', function ($q) {
                        $q->whereNull('simulated_id');
                    })
                    ->orderByRaw('COALESCE(g.order, 0) asc')
                    ->orderBy('topics.order', 'asc');
            }
        ])->where('status', 'active')->orderBy('order', 'asc')->get();

        return view('app.Notebook.create-notebook', compact('contents'));
    }

    public function review(Request $request, $id) {
        
        $notebook = Notebook::find($id);
        if (!$notebook) {
            return redirect()->back()->with('infor', 'Caderno não encontrado, verique os dados e tente novamente!');
        }

        $successCount = NotebookQuestion::where('notebook_id', $notebook->id)
            ->where('answer_result', 1)
            ->count();

        $errorCount = NotebookQuestion::where('notebook_id', $notebook->id)
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

        return view('app.Notebook.review-notebook', [
            'notebook'  => $notebook,
            'charts'    => $charts
        ]);
    }

    public function store(Request $request) {

        $request->validate([
            'title'             => ['required', 'string', 'max:255'],
            'topics'            => ['required', 'string'],
            'quanty_questions'  => ['required', 'integer', 'min:1'],
        ], [
            'title.required'            => 'O título é obrigatório.',
            'title.string'              => 'O título deve ser um texto.',
            'title.max'                 => 'O título não pode ter mais que 255 caracteres.',
            'topics.required'           => 'Selecione pelo menos um tópico.',
            'topics.string'             => 'Os tópicos devem ser enviados como texto.',
            'quanty_questions.required' => 'Informe a quantidade de questões.',
            'quanty_questions.integer'  => 'A quantidade de questões deve ser um número inteiro.',
            'quanty_questions.min'      => 'A quantidade mínima de questões é 1.',
        ]);

        $topicIds        = json_decode($request->input('topics'), true);
        $totalQuestions  = (int) $request->input('quanty_questions');
        $user_id         = Auth::id();

        if (empty($topicIds)) {
            return redirect()->back()->with('infor', 'Nenhum tópico selecionado. Por favor, selecione pelo menos um tópico!');
        }

        $topicsCount        = count($topicIds);
        $questionsPerTopic  = floor($totalQuestions / $topicsCount);
        $remaining          = $totalQuestions % $topicsCount;
        $filters            = $request->except(['title']);

        DB::beginTransaction();

        try {

            $notebook = Notebook::create([
                'title'         => $request->title,
                'created_by'    => $user_id,
                'filters'       => json_encode($filters),
                'status'        => 'draft',
            ]);

            $allSelectedQuestions = [];
            $position             = 1;
            $remainingQuestions   = $totalQuestions;

            // 🔥 Função reutilizável de filtros
            $applyFilters = function ($query) use ($request, $user_id) {

                if ($request->filter == 'filter_success') {
                    // remove questões acertadas
                    $query->whereDoesntHave('notebookQuestions', function($q) use ($user_id) {
                        $q->where('user_id', $user_id)
                        ->where('answer_result', 1);
                    });
                }

                if ($request->filter == 'filter_failer') {
                    // remove questões já respondidas (acerto ou erro)
                    $query->whereDoesntHave('notebookQuestions', function($q) use ($user_id) {
                        $q->where('user_id', $user_id)
                        ->whereNotNull('answer_result');
                    });
                }

                if ($request->filter == 'filter_eliminated') {
                    // 🔥 CORREÇÃO: remover TODAS já respondidas
                    $query->whereDoesntHave('notebookQuestions', function($q) use ($user_id) {
                        $q->where('user_id', $user_id)
                        ->whereNotNull('answer_result');
                    });
                }

                if ($request->filter == 'filter_favorited') {
                    $query->whereHas('favorites', function($q) use ($user_id) {
                        $q->where('user_id', $user_id);
                    });
                }

                return $query;
            };

            foreach ($topicIds as $topicId) {

                $limit = min(
                    $questionsPerTopic + ($remaining-- > 0 ? 1 : 0),
                    $remainingQuestions
                );

                $query = Question::where('topic_id', $topicId)
                    ->whereNull('simulated_id');

                $query = $applyFilters($query);

                // evitar repetir questões já selecionadas
                if (!empty($allSelectedQuestions)) {
                    $query->whereNotIn(
                        'id',
                        collect($allSelectedQuestions)->pluck('question_id')
                    );
                }

                $questions = $query->inRandomOrder()->limit($limit)->get();

                foreach ($questions as $question) {
                    $allSelectedQuestions[] = [
                        'notebook_id'       => $notebook->id,
                        'user_id'           => $user_id,
                        'question_id'       => $question->id,
                        'question_position' => $position++,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ];
                }

                $remainingQuestions -= $questions->count();
            }

            // 🔥 Completar com questões extras se faltar
            if ($remainingQuestions > 0) {

                $extraQuestionsQuery = Question::whereIn('topic_id', $topicIds)
                    ->whereNull('simulated_id');

                $extraQuestionsQuery = $applyFilters($extraQuestionsQuery);

                if (!empty($allSelectedQuestions)) {
                    $extraQuestionsQuery->whereNotIn(
                        'id',
                        collect($allSelectedQuestions)->pluck('question_id')
                    );
                }

                $extraQuestions = $extraQuestionsQuery
                    ->inRandomOrder()
                    ->limit($remainingQuestions)
                    ->get();

                foreach ($extraQuestions as $question) {
                    $allSelectedQuestions[] = [
                        'notebook_id'       => $notebook->id,
                        'user_id'           => $user_id,
                        'question_id'       => $question->id,
                        'question_position' => $position++,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ];
                }
            }

            NotebookQuestion::insert($allSelectedQuestions);

            DB::commit();

            return redirect()
                ->route('answer', ['notebook' => $notebook->id])
                ->with('success', count($allSelectedQuestions) . ' questões adicionadas ao caderno com sucesso!');

        } catch (\Exception $e) {

            DB::rollBack();
            report($e);

            return back()->withErrors('Erro ao criar caderno: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id) {

        $request->validate([
            'title'             => 'required|string|max:255',
            'topics'            => 'required|string',
            'quanty_questions'  => 'required|integer|min:1',
        ]);

        $topicIds        = json_decode($request->input('topics'), true);
        $totalQuestions  = (int) $request->input('quanty_questions');
        $user_id         = Auth::id();

        if (empty($topicIds)) {
            return redirect()->back()->with('infor', 'Nenhum tópico selecionado. Por favor, selecione pelo menos um tópico!');
        }

        $topicsCount        = count($topicIds);
        $questionsPerTopic  = floor($totalQuestions / $topicsCount);
        $remaining          = $totalQuestions % $topicsCount;
        $filters            = $request->except(['title']);

        DB::beginTransaction();

        try {
            $notebook = Notebook::findOrFail($id);

            $notebook->update([
                'title'   => $request->title,
                'filters' => json_encode($filters),
                'status'  => 'draft',
            ]);

            NotebookQuestion::where('notebook_id', $notebook->id)->delete();

            $allSelectedQuestions = [];
            $position             = 1;
            $remainingQuestions   = $totalQuestions;

            // 🔥 Função reutilizável de filtros (MESMA do store)
            $applyFilters = function ($query) use ($request, $user_id) {

                if ($request->filter == 'filter_success') {
                    $query->whereDoesntHave('notebookQuestions', function($q) use ($user_id) {
                        $q->where('user_id', $user_id)
                        ->where('answer_result', 1);
                    });
                }

                if ($request->filter == 'filter_failer') {
                    // remove qualquer questão já respondida
                    $query->whereDoesntHave('notebookQuestions', function($q) use ($user_id) {
                        $q->where('user_id', $user_id)
                        ->whereNotNull('answer_result');
                    });
                }

                if ($request->filter == 'filter_eliminated') {
                    // 🔥 corrigido: remover todas respondidas
                    $query->whereDoesntHave('notebookQuestions', function($q) use ($user_id) {
                        $q->where('user_id', $user_id)
                        ->whereNotNull('answer_result');
                    });
                }

                if ($request->filter == 'filter_favorited') {
                    $query->whereHas('favorites', function($q) use ($user_id) {
                        $q->where('user_id', $user_id);
                    });
                }

                return $query;
            };

            foreach ($topicIds as $topicId) {

                $limit = min(
                    $questionsPerTopic + ($remaining-- > 0 ? 1 : 0),
                    $remainingQuestions
                );

                $query = Question::where('topic_id', $topicId)
                    ->whereNull('simulated_id');

                $query = $applyFilters($query);

                if (!empty($allSelectedQuestions)) {
                    $query->whereNotIn(
                        'id',
                        collect($allSelectedQuestions)->pluck('question_id')
                    );
                }

                $questions = $query->inRandomOrder()->limit($limit)->get();

                foreach ($questions as $question) {
                    $allSelectedQuestions[] = [
                        'notebook_id'       => $notebook->id,
                        'user_id'           => $user_id,
                        'question_id'       => $question->id,
                        'question_position' => $position++,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ];
                }

                $remainingQuestions -= $questions->count();
            }

            // 🔥 Completar se faltar questões
            if ($remainingQuestions > 0) {

                $extraQuestionsQuery = Question::whereIn('topic_id', $topicIds)
                    ->whereNull('simulated_id');

                $extraQuestionsQuery = $applyFilters($extraQuestionsQuery);

                if (!empty($allSelectedQuestions)) {
                    $extraQuestionsQuery->whereNotIn(
                        'id',
                        collect($allSelectedQuestions)->pluck('question_id')
                    );
                }

                $extraQuestions = $extraQuestionsQuery
                    ->inRandomOrder()
                    ->limit($remainingQuestions)
                    ->get();

                foreach ($extraQuestions as $question) {
                    $allSelectedQuestions[] = [
                        'notebook_id'       => $notebook->id,
                        'user_id'           => $user_id,
                        'question_id'       => $question->id,
                        'question_position' => $position++,
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ];
                }
            }

            NotebookQuestion::insert($allSelectedQuestions);

            DB::commit();

            return redirect()
                ->route('answer', ['notebook' => $notebook->id])
                ->with('success', count($allSelectedQuestions) . ' questões adicionadas ao caderno com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            report($e);

            return back()->withErrors('Erro ao atualizar caderno: ' . $e->getMessage());
        }
    }

    public function destroy($id) {
        
        $notebook = Notebook::find($id);
        if ($notebook) {
            NotebookQuestion::where('notebook_id', $notebook->id)->where('answer_result', 0)->delete();
            $notebook->delete();
            return redirect()->back()->with('success', 'Caderno excluído com sucesso!');
        }

        return redirect()->back()->with('infor', 'Não foi possível Excluir/ou Encontrar o Caderno, verifique os dados e tente novamente!');
    }
}
