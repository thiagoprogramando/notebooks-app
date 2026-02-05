@extends('app.layout')
@section('content')

<div class="col-12 col-sm-12 col-md-12 col-lg-12">
    <div class="card mb-3">

        <div class="card-header">
            <div class="d-flex justify-content-center">
                <h5 class="mb-5">Simulado: {{ $simulated->title }}</h5>
            </div>

            <div class="card-subtitle">
                <div class="d-flex justify-content-center">
                    @if ($simulated->image)
                        <img class="img-fluid w-25" width="" src="{{ asset('storage/'.$simulated->image) }}" alt="{{ $simulated->title }}">
                    @endif
                </div>
                {!! $simulated->description !!}
            </div>
        </div>

        <div class="card-body d-flex justify-content-center">
            <div class="btn-group">
                <a href="{{ route('review-simulated', ['uuid' => $simulated->uuid]) }}" class="btn btn-lg btn-outline-success">INICIAR PROVA</a>
            </div>
        </div>
    </div>
</div>
@endsection
