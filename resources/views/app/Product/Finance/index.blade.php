@extends('app.layout')
@section('content')

    <div class="col-12">
        
        <div class="kanban-add-new-board">
            <label class="kanban-add-board-btn" for="kanban-add-board-input" data-bs-toggle="modal" data-bs-target="#filterModal">
                <i class="ri-filter-line"></i>
                <span class="align-middle">Filtrar</span>
            </label>
        </div>

        <div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
            <form action="{{ route('finances') }}" method="GET">
                @csrf
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title" id="exampleModalLabel1">Filtro de Pesquisa</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-6 col-sm-12 col-md-6 col-lg-6 mb-2">
                                    <div class="form-floating form-floating-outline">
                                        <div class="select2-primary">
                                            <select name="product_id" id="product_id" class="select2 form-select">
                                                <option value="  " selected>Todos</option>
                                                @foreach ($products as $product)
                                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <label for="product_id">Produto</label>
                                    </div>
                                </div>
                                <div class="col-6 col-sm-12 col-md-6 col-lg-6 mb-2">
                                    <div class="form-floating form-floating-outline">
                                        <div class="select2-primary">
                                            <select name="simulated_id" id="simulated_id" class="select2 form-select">
                                                <option value="  " selected>Todos</option>
                                                @foreach ($simulateds as $simulated)
                                                    <option value="{{ $simulated->id }}">{{ $simulated->title }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <label for="simulated_id">Simulado</label>
                                    </div>
                                </div>
                                <div class="col-4 col-sm-12 col-md-6 col-lg-6 mb-2">
                                    <div class="form-floating form-floating-outline">
                                        <div class="select2-primary">
                                            <select name="payment_status" id="payment_status" class="select2 form-select">
                                                <option value="  ">Todos</option>
                                                <option value="0">Pendente</option>
                                                <option value="1">Aprovado</option>
                                                <option value="2">Cancelado</option>
                                            </select>
                                        </div>
                                        <label for="payment_status">Status de Pagamento</label>
                                    </div>
                                </div>
                                <div class="col-4 col-sm-12 col-md-6 col-lg-6 mb-2">
                                    <div class="form-floating form-floating-outline">
                                        <div class="select2-primary">
                                            <select name="filter" id="filter" class="select2 form-select">
                                                <option value="  ">Todos</option>
                                                <option value="actives">Ativos</option>
                                                <option value="inactives">Inativos</option>
                                                <option value="canceleds">Cancelados</option>
                                            </select>
                                        </div>
                                        <label for="filter">Situação</label>
                                    </div>
                                </div>
                                <div class="col-4 col-sm-12 col-md-6 col-lg-6 mb-2">
                                    <div class="form-floating form-floating-outline">
                                        <input type="date" name="date_start" id="date_start" class="form-control money" placeholder="Data Inicial"/>
                                        <label for="date_start">Data Inicial</label>
                                    </div>
                                </div>
                                <div class="col-4 col-sm-12 col-md-6 col-lg-6 mb-2">
                                    <div class="form-floating form-floating-outline">
                                        <input type="date" name="date_end" id="date_end" class="form-control money" placeholder="Data Final"/>
                                        <label for="date_end">Data Final</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer btn-group">
                            <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal"> Fechar </button>
                            <button type="submit" class="btn btn-success">Pesquisar</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="col-12 col-sm-12 col-md-10 col-lg-10">
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
                        <div class="avatar-initial bg-label-dark rounded">
                            <i class="ri-list-check ri-24px"></i>
                        </div>
                    </div>
                    <div class="card-info">
                        <h5 class="mb-0">{{ $invoices->count() }}</h5>
                        <p class="mb-0">Faturas (N°)</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar">
                        <div class="avatar-initial bg-label-dark rounded">
                        <i class="ri-exchange-dollar-line ri-24px"></i>
                        </div>
                    </div>
                    <div class="card-info">
                        <h5 class="mb-0">{{ number_format($invoices->sum('value'), 2, ',', '.') }}</h5>
                        <p class="mb-0">Faturas (R$)</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar">
                        <div class="avatar-initial bg-label-success rounded">
                        <i class="ri-user-follow-line ri-24px"></i>
                        </div>
                    </div>
                    <div class="card-info">
                        <h5 class="mb-0">{{ $stats['actives'] }}</h5>
                        <p class="mb-0">Ativas</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar">
                        <div class="avatar-initial bg-label-info rounded">
                        <i class="ri-user-minus-line ri-24px"></i>
                        </div>
                    </div>
                    <div class="card-info">
                        <h5 class="mb-0">{{ $stats['inactives'] }}</h5>
                        <p class="mb-0">Congeladas</p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar">
                        <div class="avatar-initial bg-label-danger rounded">
                        <i class="ri-user-unfollow-line ri-24px"></i>
                        </div>
                    </div>
                    <div class="card-info">
                        <h5 class="mb-0">{{ $stats['canceleds'] }}</h5>
                        <p class="mb-0">Vencidas</p>
                    </div>
                </div>
            </div>
        </div>      

        <div class="card demo-inline-spacing">
            <div class="list-group p-0 m-0">
                @foreach ($invoices as $invoice)
                    <div class="list-group-item list-group-item-action d-flex align-items-center cursor-pointer waves-effect waves-light">
                        <div class="w-100">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="user-info">
                                    <h6 class="mb-1 fw-normal">{{ $invoice->product->name ?? $invoice->simulated->title }}</h6>
                                    <div class="d-flex align-items-center">
                                        <div class="user-status me-2 d-flex align-items-center">
                                            {!! $invoice->statusLabel() !!}
                                        </div>
                                        <div class="user-status me-2 d-flex align-items-center">
                                            <span class="badge badge-dot bg-info me-1"></span>
                                            <small><a href="{{ route('user', ['uuid' => $invoice->user->uuid]) }}" target="_blank">{{ $invoice->user->name.' | '.$invoice->user->maskCpfCnpj() }}</a></small>
                                        </div>
                                        <small class="text-muted ms-1">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y').' | R$'.number_format($invoice->value, 2, ',', '.') }}</small>
                                    </div>
                                </div>
                                <form action="{{ route('deleted-finance', ['uuid' => $invoice->uuid]) }}" method="POST" class="add-btn delete">
                                    @csrf
                                    <button type="button" class="btn btn-success text-white btn-sm" title="Editar Fatura" data-bs-toggle="modal" data-bs-target="#updatedModal"><i class="ri-menu-search-line"></i></button>
                                    <button type="submit" class="btn btn-danger btn-sm" title="Excluir Conteúdo"><i class="ri-delete-bin-line"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="updatedModal" tabindex="-1" aria-hidden="true">
                        <form action="{{ route('updated-finance', ['uuid' => $invoice->uuid]) }}" method="POST">
                            @csrf
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title" id="exampleModalLabel1">Dados da Fatura</h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-6 col-sm-12 col-md-6 col-lg-6 mb-2">
                                                <div class="form-floating form-floating-outline">
                                                    <div class="select2-primary">
                                                        <select name="product_id" id="product_id" class="select2 form-select">
                                                            <option value="  " selected>Nenhum Produto</option>
                                                            @foreach ($products as $product)
                                                                <option value="{{ $product->id }}" @selected($product->id == $invoice->product_id)>{{ $product->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <label for="product_id">Produto</label>
                                                </div>
                                            </div>
                                            <div class="col-6 col-sm-12 col-md-6 col-lg-6 mb-2">
                                                <div class="form-floating form-floating-outline">
                                                    <div class="select2-primary">
                                                        <select name="simulated_id" id="simulated_id" class="select2 form-select">
                                                            <option value="  " selected>Nenhum Simulado</option>
                                                            @foreach ($simulateds as $simulated)
                                                                <option value="{{ $simulated->id }}" @selected($simulated->id == $invoice->simulated_id)>{{ $simulated->title }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <label for="simulated_id">Simulado</label>
                                                </div>
                                            </div>
                                            <div class="col-4 col-sm-12 col-md-6 col-lg-6 mb-2">
                                                <div class="form-floating form-floating-outline">
                                                    <input type="text" name="value" id="value" class="form-control money" placeholder="Valor" value="{{ $invoice->value }}" oninput="maskValue(this)"/>
                                                    <label for="value">Valor</label>
                                                </div>
                                            </div>
                                            <div class="col-4 col-sm-12 col-md-6 col-lg-6 mb-2">
                                                <div class="form-floating form-floating-outline">
                                                    <div class="select2-primary">
                                                        <select name="payment_status" id="payment_status" class="select2 form-select">
                                                            <option value="00" @selected($invoice->payment_status == 0)>Pendente</option>
                                                            <option value="1" @selected($invoice->payment_status == 1)>Aprovado</option>
                                                            <option value="2" @selected($invoice->payment_status == 2)>Cancelado</option>
                                                        </select>
                                                    </div>
                                                    <label for="payment_status">Status de Pagamento</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer btn-group">
                                        <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal"> Fechar </button>
                                        <button type="submit" class="btn btn-success">Enviar</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                @endforeach
            </div>

            <div class="card-footer d-flex justify-content-center">
                {{ $invoices->links() }}
            </div>
        </div>
    </div>

@endsection