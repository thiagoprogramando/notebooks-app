@extends('app.layout')
@section('content')

    <div class="col-12">
        <div class="kanban-add-new-board">
            <a href="{{ route('create-notebook') }}" class="kanban-add-board-btn" for="kanban-add-board-input">
                <i class="ri-add-line"></i>
                <span class="align-middle">Novo Caderno</span>
            </a>
        </div>
    </div>

    <div class="col-12 col-sm-12 col-md-12 col-lg-7">
        <div class="card mb-3">
            <div class="card-header">
                <div class="d-flex justify-content-between">
                    <h5 class="mb-1">Visão Geral</h5>
                    <div class="dropdown">
                        <button class="btn btn-text-secondary rounded-pill text-muted border-0 p-1 waves-effect waves-light" type="button" id="salesOverview" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="ri-more-2-line ri-20px"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="salesOverview">
                            <button type="button" class="dropdown-item waves-effect" onclick="location.reload(true)">Atualizar</button>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center card-subtitle">
                    <div class="me-2">Os dados são atualizados automáticamente.</div>
                </div>
            </div>
            <div class="card-body d-flex justify-content-between flex-wrap gap-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar">
                        <div class="avatar-initial bg-label-primary rounded">
                            <i class="ri-file-edit-line ri-24px"></i>
                        </div>
                    </div>
                    <div class="card-info">
                        {{-- <h5 class="mb-0">{{ $notebooks->count() }}</h5> --}}
                        <p class="mb-0">Cadernos</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar">
                        <div class="avatar-initial bg-label-warning rounded">
                        <i class="ri-pie-chart-2-line ri-24px"></i>
                        </div>
                    </div>
                    <div class="card-info">
                        {{-- <h5 class="mb-0">{{ $notebooks->topics->count() }}</h5> --}}
                        <p class="mb-0">Tópicos</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar">
                        <div class="avatar-initial bg-label-info rounded">
                        <i class="ri-question-line ri-24px"></i>
                        </div>
                    </div>
                    <div class="card-info">
                        {{-- <h5 class="mb-0">{{ $notebooks->questions->count() }}</h5> --}}
                        <p class="mb-0">Questões</p>
                    </div>
                </div>
            </div>
        </div>      

        <div class="card demo-inline-spacing">
            <div class="list-group p-0 m-0">
                @foreach ($notebooks as $notebook)
                    <div class="list-group-item list-group-item-action d-flex align-items-center cursor-pointer waves-effect waves-light mb-2">
                        <img src="{{ $notebook->cover_image ? asset($notebook->cover_image) : asset('assets/img/avatars/man.png') }}" alt="Conteúdo Imagem" class="rounded-circle me-3" width="40">
                        <div class="w-100">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="user-info">
                                    <h6 class="mb-1 fw-normal">{{ $notebook->title }}</h6>
                                    <div class="d-flex align-items-center">
                                        <div class="user-status me-2 d-flex align-items-center">
                                            <span class="badge badge-dot bg-dark me-1"></span>
                                            <small>Tópicos: {{ $notebook->topics_count }}</small>
                                        </div>
                                        <div class="user-status me-2 d-flex align-items-center">
                                            <span class="badge badge-dot bg-info me-1"></span>
                                            <small>Questões: {{ $notebook->questions->count() }}</small>
                                        </div>
                                    </div>
                                </div>
                                <form action="{{ route('deleted-notebook', ['id' => $notebook->id]) }}" method="POST" class="add-btn delete">
                                    @csrf
                                    <a href="{{ route('answer', ['notebook' => $notebook->id]) }}" title="Responder Questões" class="btn btn-success text-white btn-sm"><i class="ri-questionnaire-line"></i></a>
                                    <a href="{{ route('notebook', ['id' => $notebook->id]) }}" title="Editar Caderno" class="btn btn-warning text-white btn-sm"><i class="ri-menu-search-line"></i></a>
                                    <button type="submit" class="btn btn-danger btn-sm"><i class="ri-delete-bin-line"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>  
                @endforeach

                <div class="mt-2 mb-5 text-center">
                    {{ $notebooks->links() }}
                </div>
            </div>
        </div>
    </div>

@endsection