@extends('app.layout')
@section('content')

<style>
    .questions {
        display: inline-flex;
        align-items: center;
        justify-content: center;

        width: 22px;
        height: 22px;

        margin: 4px;

        border-radius: 50%;
        border: 1.5px solid #ccc;

        background-color: #fff;
        color: #333;
        text-decoration: none;
        font-size: 10px;
        font-weight: 500;

        transition: all 0.2s ease;
    }
    .questions:hover {
        border-color: #999;
        transform: scale(1.05);
    }
    .questions.answered {
        background-color: #faffc2;
        border-color: #c9d918;
        color: #ffd000;
    }
    .questions.current {
        border: 2px solid #c27a27;
    }
</style>

<div class="col-12 col-sm-12 col-md-12 col-lg-12">
    <div class="card mb-3">

        <div class="card-header">
            <div class="row">
                <div class="col-sm-12 col-md-5 col-lg-5">
                    <h5 class="mb-1">Simulado: {{ $simulated->title }}</h5>
                    <div class="card-subtitle">
                        <div class="me-2">Questão {{ $question->question_position.' de '.$simulated->questions->count() }}</div>
                        <small>
                            <b>Banca:</b> {{ $question->question->board->code.' '.$question->question->board->name.' - '.$question->question->board->state .'/'.$question->question->board->city }} <br>
                        </small>
                    </div>
                </div>
                
                <div class="col-sm-12 col-md-7 col-lg-7">
                    @foreach($allQuestions as $q)
                        <a  href="?question_id={{ $q->id }}" class="questions {{ $q->answer_result == 1 || $q->answer_result == 2 ? 'answered' : 'current' }}">
                            {{ $loop->iteration }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
       

        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <div class="divider">
                        <div class="divider-text">Questão</div>
                    </div>
                </div>

                <form id="answerForm" method="POST" class="col-12 col-sm-12 col-md-12 col-lg-12 row">
                    @csrf
                    <input type="hidden" name="simulated_question_id" value="{{ $question->id }}">
                   
                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 bg-light p-3 rounded mt-1 mb-1">
                        <h5> {!! $question->question->title !!} </h5>
                    </div>
                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 bg-light p-3 rounded mt-2 mb-2">
                        @foreach ($question->question->alternatives as $alternative)
                            <div class="form-check mt-4 alternative-item" data-alternative-id="{{ $alternative->id }}">
                                <input class="form-check-input" type="radio" name="answer_id" value="{{ $alternative->id }}" id="answer_id{{ $alternative->id }}" @checked($question->answer_id == $alternative->id)>
                                <div class="alt-content">
                                    <span class="non-break">
                                        <i class="ri-scissors-line scissors-icon" title="Eliminar alternativa" role="button" aria-label="Eliminar alternativa"></i>
                                        <label class="alt-short" for="answer_id{{ $alternative->id }}">{{ $alternative->label }})</label>
                                    </span>
                                    <label class="alt-long" for="answer_id{{ $alternative->id }}">{{ $alternative->text }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 bg-light p-3 rounded mt-1 mb-1 text-center">
                        <button type="button" onclick="submitAnswer()" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium bg-success text-white border-success">
                            Responder
                        </button>
                        @if ($isFinished)
                            <button type="button" onclick="submitEnd()" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium bg-success text-white border-success">
                                Finalizar
                            </button>
                        @endif
                        @php
                            $currentIndex = $allQuestions->search(fn($q) => $q->id === $question->id);
                            $nextQuestion = $allQuestions[$currentIndex + 1] ?? null;
                        @endphp

                        @if (in_array($question->answer_result, [1, 2]) && $nextQuestion)
                            <button type="button" onclick="window.location.href='?question_id={{ $nextQuestion->id }}'" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium bg-dark text-white border-dark">
                                Próxima
                            </button>
                        @else
                            <button type="button" onclick="submitEmpty()" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium bg-dark text-white border-dark">
                                Pular
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function submitAnswer() {

        const form = document.getElementById('answerForm');
        const formData = new FormData(form);

        fetch("{{ route('answer-simulated-question') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {

            if (data.success) {
                window.location.href = data.redirect;
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    text: data.message
                });
            }

        })
        .catch(() => {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Falha na comunicação com o servidor.'
            });
        });
    }

    function submitEnd() {

        Swal.fire({
            title: 'Finalizar simulado?',
            text: "Você deverá aguardar até a finalização do período de respostas para acessar os resultados!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim, finalizar!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ route('review-simulated', ['uuid' => $simulated->uuid, 'reports' => true]) }}";
            }
        });
    }

    function submitEmpty() {

        fetch("{{ route('answer-simulated-question') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                answer_result: 3,
                simulated_question_id: {{ $question->id }}
            })
        })
        .then(res => res.json())
        .then(data => {

            if (data.success) {
                window.location.href = data.redirect;
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: data.message
                });
            }

        })
        .catch(() => {
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: 'Falha na comunicação com o servidor.'
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.scissors-icon').forEach(icon => {
            icon.addEventListener('click', function () {
                const item = this.closest('.alternative-item');
                const short = item.querySelector('.alt-short');
                const long = item.querySelector('.alt-long');
                const input = item.querySelector('.form-check-input');

                short.classList.toggle('eliminado');
                long.classList.toggle('eliminado');
                input.disabled = !input.disabled;
            });
        });

        document.querySelectorAll('.alt-long').forEach(el => {
            el.addEventListener('dblclick', function () {
                const item = this.closest('.alternative-item');
                const short = item.querySelector('.alt-short');
                const long = item.querySelector('.alt-long');
                const input = item.querySelector('.form-check-input');

                short.classList.toggle('eliminado');
                long.classList.toggle('eliminado');
                input.disabled = !input.disabled;
            });
        });
    });
</script>

@endsection
