@extends('app.layout')
@section('content')

    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
        <div class="card mb-3">
            <div class="card-header">
                <div class="d-flex justify-content-between">
                    <h5 class="mb-1">Caderno: {{ $notebook->title }}</h5>
                    <div class="dropdown">
                        <button class="btn btn-text-secondary rounded-pill text-muted border-0 p-1 waves-effect waves-light" type="button" id="salesOverview" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="ri-more-2-line ri-20px"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="salesOverview">
                            <button type="button" class="dropdown-item waves-effect" onclick="location.reload(true)">Atualizar</button>
                        </div>
                    </div>
                </div>
                <div class="card-subtitle">
                    @foreach($questions as $question)
                        <div class="me-2">Questão {{ $question->question_position.' de '.$notebook->questions->count() }}</div>
                        <small>
                            <b>Conteúdo/Tópico:</b> {{ $question->question->topic->content->title }} | {{ $question->question->topic->title }}<br>
                            <b>Banca:</b> {{ $question->question->board->code.' '.$question->question->board->name.' - '.$question->question->board->state .'/'.$question->question->board->city }} <br>
                        </small>
                    @endforeach
                    <small><b>{{ $notebook->countQuestionsByStatus(1) }}</b> Resolvidas</small> <small class="text-success"><b>{{ $notebook->countQuestionsByStatus(1, 1) }}</b> Acertos</small> <small class="text-danger"><b>{{ $notebook->countQuestionsByStatus(1, 2) }}</b> Erros</small>
                </div>
            </div>
            <div class="card-body">

                <div class="row">
                    <div class="col-12 d-flex justify-content-center flex-wrap gap-4">
                        <div class="btn-toolbar demo-inline-spacing gap-2">
                            <div class="btn-group" role="group" aria-label="First group">
                                <button type="button" class="btn btn-outline-secondary" title="Estátisticas"> <i class="tf-icons ri-pie-chart-line"></i></button>
                                <a href="{{ route('notebook', ['id' => $notebook->id]) }}" title="Editar Caderno" class="btn btn-outline-secondary"> <i class="tf-icons ri-filter-3-line"></i> </a>
                                <button type="button" class="btn btn-outline-secondary" title="Alertar Problema" data-bs-toggle="modal" data-bs-target="#createdTicketModal"> <i class="tf-icons ri-alarm-warning-line"></i></button>
                                <button type="button" class="btn btn-outline-secondary" title="Comentário do Professor" class="btn btn-dark" data-bs-toggle="popover" data-bs-placement="top" data-bs-custom-class="popover-dark" data-bs-content="{{ $questions->first()->question->resolution }}">
                                    <i class="tf-icons ri-chat-quote-line"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" title="Comentários da Questão" data-bs-toggle="collapse" href="#collapseComments" role="button" aria-expanded="false" aria-controls="collapseComments">
                                    <i class="tf-icons ri-chat-1-line"></i>
                                </button>
                                <a href="{{ route('favorited-question', ['id' => $questions->first()->question->id]) }}" class="btn btn-outline-secondary" title="Favoritar"> <i class="tf-icons {{ $questions->first()->question->isFavorited() ? 'ri-heart-fill text-danger' : 'ri-heart-line' }}"></i> </a>
                            </div>
                        </div>
                    </div>

                     <div class="collapse" id="collapseComments">
                        <div class="row p-4 border">
                            <form action="{{ route('created-comment') }}" method="POST" class="col-12 col-sm-12 col-md-4 col-lg-4">
                                @csrf
                                <input type="hidden" name="question_id" value="{{ $questions->first()->question->id }}">
                                <div class="form-floating form-floating-outline mb-2">
                                    <textarea class="form-control h-px-100" name="comment" id="comment" placeholder="Deixe seu comentário:" required></textarea>
                                    <label for="comment">Deixe seu comentário:</label>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-outline-dark">Comentar</button>
                                </div>
                            </form>

                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                <div class="divider text-start-center">
                                    <div class="divider-text">
                                        {{ $questions->first()->question->comments->count() > 0 ? 'Últimos comentários' : 'Seja o primeiro a comentar algo!' }}
                                    </div>
                                </div>
                                
                                <div class="card-body">
                                    @foreach ($questions->first()->question->comments as $comment)
                                        <small class="text-light fw-medium">{{ $comment->user->name }} <cite title="Comentário feito em {{ $comment->created_at->format('d/m/Y') }}">{{ $comment->created_at->format('d/m/Y') }}</cite></small>
                                        <figure class="mt-2">
                                            <blockquote class="blockquote">
                                                <p class="mb-0">{{ $comment->comment }}</p>
                                            </blockquote>
                                        </figure>
                                    @endforeach
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="modal fade" id="createdTicketModal" tabindex="-1" aria-hidden="true">
                        <form action="{{ route('created-ticket') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title" id="exampleModalLabel1">Dados do Ticket</h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-12 mb-2">
                                                <div class="form-floating form-floating-outline mb-2">
                                                    <textarea class="form-control h-px-100" name="description" id="description" placeholder="Notas" required></textarea>
                                                    <label for="description">Descrição</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-4">
                                            <label for="assets" class="form-label">Arquivos (Imagens, PDFs e etc)</label>
                                            <input type="file" class="form-control" name="assets[]" id="assets" multiple>
                                        </div>
                                        <input type="hidden" name="question_id" value="{{ $questions->first()->question->id }}">
                                    </div>
                                    <div class="modal-footer btn-group">
                                        <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal"> Fechar </button>
                                        <button type="submit" class="btn btn-success">Enviar</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="divider">
                            <div class="divider-text">Questão</div>
                        </div>
                    </div>
                    <form action="" method="POST" class="col-12 col-sm-12 col-md-12 col-lg-12 row">
                        @csrf
                        @foreach($questions as $question)
                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 bg-light p-3 rounded mt-1 mb-1">
                                <h5>
                                    #{{ $question->id }} - {{ $question->question->title }}
                                </h5>
                            </div>

                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 bg-light p-3 rounded mt-2 mb-2">
                                @foreach ($question->question->alternatives as $alternative)
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="radio" name="answer_id" value="{{ $alternative->id }}" id="answer_id{{ $alternative->id }}">
                                        <label class="form-check-label" for="answer_id{{ $alternative->id }}"> {{ $alternative->label.') '.$alternative->text }}  </label>
                                    </div>
                                @endforeach
                            </div>

                            <input type="hidden" name="notebook_question_id" value="{{ $question->id }}">
                        @endforeach

                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 bg-light p-3 rounded mt-1 mb-1 text-center">
                            {{ $questions->links() }}
                        </div>
                    </form>

                    <form id="deleteForm" method="POST" style="display: none;">
                        @csrf
                    </form>
                </div>
            
            </div>
        </div>      
    </div>

    <script>
        function submitAnswer() {
            const form = document.querySelector('form');
            form.action = "{{ route('answer-question') }}";
            form.submit();
        }

        function submitDelete() {
            const questionId = document.querySelector('[name="notebook_question_id"]')?.value;
            if (!questionId) {
                return;
            }

            const deleteForm = document.getElementById('deleteForm');
            deleteForm.action = `/answer-question/${questionId}`;
            deleteForm.submit();
        }
    </script>
@endsection