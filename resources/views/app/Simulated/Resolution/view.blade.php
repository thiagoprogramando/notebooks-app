@extends('app.layout')
@section('content')

<div class="col-12 col-sm-12 col-md-12 col-lg-12">
    <div class="card mb-3">
        <div class="card-body">
            @if (!empty(asset('storage/'.$simulated->image)))
                <div class="text-center mb-6 pt-2 rounded-3">
                    <img class="img-fluid w-50" src="{{ asset('storage/'.$simulated->image) }}" alt="{{ $simulated->title }}">
                </div>
            @endif
            @if ($mode == 'roles')
                {!! $simulated->roles !!}
            @else
                {!! $simulated->presentation !!}
            @endif

            <div class="d-flex justify-content-center">
                <div class="btn-group">
                    @if ($mode == 'roles')
                        <a href="{{ route('review-simulated', ['uuid' => $simulated->uuid]) }}" class="btn btn-lg btn-outline-success">INICIAR PROVA</a>
                    @else
                        <a href="{{ route('view-simulated', ['uuid' => $simulated->uuid, 'mode' => 'roles']) }}" class="btn btn-lg btn-outline-warning">VER REGRAS/ORIENTAÇÕES</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
