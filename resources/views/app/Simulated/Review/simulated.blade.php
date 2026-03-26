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
            <div class="row">
                <div class="col-12 col-sm-12 col-md-12 col-lg-12 row">
                    @foreach($questions as $question)
                        <div class="divider">
                            <div class="divider-text">QUESTÃO</div>
                        </div>

                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 bg-light p-3 rounded mt-1 mb-1">
                            <p>Questão {{ $question->question_position }} de {{ $questions->count() }}</p>
                            <h5> {!! $question->question->title !!} </h5>
                        </div>

                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 bg-light p-3 rounded mt-2 mb-2">
                            @foreach ($question->question->alternatives as $alternative)
                                <div class="form-check mt-4 alternative-item" data-alternative-id="{{ $alternative->id }}">
                                    <input class="form-check-input" type="radio" name="answer_id_{{ $question->id }}" value="{{ $alternative->id }}" id="answer_id{{ $alternative->id }}" @checked($question->answer_id == $alternative->id) disabled>
                                    <div class="alt-content">
                                        <span class="non-break">
                                            <label class="alt-short" for="answer_id{{ $alternative->id }}">{{ $alternative->label }})</label>
                                        </span>
                                        <label class="alt-long" for="answer_id{{ $alternative->id }}">{{ $alternative->text }}</label>
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
    </div>
</div>
@endsection
