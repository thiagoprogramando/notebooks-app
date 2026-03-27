@extends('app.layout')
@section('content')

<div class="col-12 col-sm-12 col-md-12 col-lg-12">
    <div class="card mb-3">

        <div class="card-header">
            <div class="justify-content-center">
                <h4 class="text-center">{{ $simulated->title }}</h4>
                <p class="text-center text-info">REVISAR SIMULADO RESOLVIDO</p>
            </div>
        </div>

        <div class="card-body">
            @foreach($questions as $question)
                <div class="divider">
                    <div class="divider-text">QUESTÃO</div>
                </div>

                <div class="bg-light p-3 rounded mt-1 mb-1">
                    <p>Questão {{ $question->question_position }} de {{ $questions->count() }}</p>
                    <h5> {!! $question->question->title !!} </h5>
                </div>

                <div class="bg-light p-3 rounded mt-2 mb-2">
                    @foreach ($question->question->alternatives as $alternative)
                        @php
                            $isCorrect = $alternative->is_correct;
                            $isChosen  = $question->answer_id == $alternative->id;
                        @endphp

                        <div class="form-check mt-4 alternative-item @if($isCorrect) border border-success bg-success-subtle rounded @elseif($isChosen && !$isCorrect) border border-warning bg-warning-subtle rounded @else border border-danger bg-danger-subtle rounded @endif" data-alternative-id="{{ $alternative->id }}">

                            <input class="form-check-input" type="radio" name="answer_id_{{ $question->id }}" value="{{ $alternative->id }}" id="answer_id{{ $alternative->id }}" @checked($isChosen) disabled>

                            <div class="alt-content">
                                <span class="non-break">
                                    <label class="alt-short" for="answer_id{{ $alternative->id }}">
                                        {{ $alternative->label }})
                                    </label>
                                </span>

                                <label class="alt-long" for="answer_id{{ $alternative->id }}">
                                    {{ $alternative->text }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                    
                    <div class="divider">
                        <div class="divider-text">RESOLUÇÃO</div>
                    </div>

                    {!! $question->question->resolution !!}
                </div>
            @endforeach

            <div class="btn-group d-flex justify-content-center mt-3 mb-3">
                <a href="{{ route('review-simulated', ['uuid' => $simulated->uuid]) }}" class="btn btn-outline-dark">Estátisticas do Simulado</a>
            </div>
        </div>
    </div>
</div>
@endsection
