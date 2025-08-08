@extends('app.layout')
@section('content')

    <style>
        .dual-listbox {
            display: flex;
            gap: 15px;
        }
        .listbox-panel {
            flex: 1;
        }
    </style>
    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
        <div class="card mb-3">
            <div class="card-header">
                <div class="d-flex justify-content-between">
                    <h5 class="mb-1">Gerar Caderno</h5>
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
                    <div class="me-2">Escolha os Filtros para gerar um Caderno de questões.</div>
                </div>
            </div>
            <div class="card-body">

                <div class="dual-listbox row">
                    <div class="col-12 col-sm-12 col-md-5 col-lg-5 listbox-panel">
                        <h5>Disponíveis</h5>
                        <input type="text" class="form-control mb-2" id="search-available" placeholder="Pesquisar...">
                        <select multiple id="available-topics" class="form-control" size="15">
                            @foreach($contents as $content)
                                <optgroup label="{{ $content->title }}" data-content-id="{{ $content->id }}">
                                    @foreach($content->topics as $topic)
                                        <option value="topic:{{ $topic->id }}" data-content-id="{{ $content->id }}">
                                            {{ $topic->title }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-sm-12 col-md-2 col-lg-2 listbox-controls text-center my-auto">
                        <div class="btn-group d-flex">
                            <button type="button" class="btn btn-sm btn-success m-1" id="add-selected">&gt;&gt;</button>
                            <button type="button" class="btn btn-sm btn-danger m-1" id="remove-selected">&lt;&lt;</button>
                        </div>
                        
                        <button type="button" class="btn btn-sm w-100 btn-secondary m-1" id="clear-all">Limpar tudo</button>
                    </div>

                    @php
                        $selectedTopics = isset($filters['topics']) ? json_decode($filters['topics'], true) : [];
                    @endphp

                    <div class="col-12 col-sm-12 col-md-5 col-lg-5 listbox-panel">
                        <h5>Selecionados</h5>
                        <input type="text" class="form-control mb-2" id="search-selected" placeholder="Pesquisar...">
                        <select multiple id="selected-topics" name="selected_topics[]" class="form-control" size="15">
                            @foreach($contents as $content)
                                @foreach($content->topics as $topic)
                                    @if(in_array($topic->id, $selectedTopics))
                                        <option value="topic:{{ $topic->id }}" data-content-id="{{ $content->id }}">
                                            {{ $topic->title }}
                                        </option>
                                    @endif
                                @endforeach
                            @endforeach
                        </select>
                    </div>
                </div>

                <form method="POST" action="{{ route('updated-notebook', ['id' => $notebook->id]) }}" id="create-notebook-form" class="row">
                    @csrf
                    <div class="col-12 col-sm-12 col-md-5 col-lg-5">
                        <div class="p-6">
                            <small class="text-light fw-medium">+Filtros</small>
                            <div class="form-check mt-4">
                                <input name="filter_resolved" class="form-check-input" type="radio" value="filter_resolved" id="filter_resolved" {{ isset($filters['filter_resolved']) ? 'checked' : '' }}>
                                <label class="form-check-label" for="filter_resolved">Eliminar questões já Resolvidas</label>
                            </div>
                            <div class="form-check">
                                <input name="filter_failer" class="form-check-input" type="radio" value="filter_failer" id="filter_failer" {{ isset($filters['filter_failer']) ? 'checked' : '' }}>
                                <label class="form-check-label" for="filter_failer">Mostrar apenas as que eu já Errei</label>
                            </div>
                            <div class="form-check">
                                <input name="filter_favorites" class="form-check-input" type="radio" value="filter_favorites" id="filter_favorites" {{ isset($filters['filter_favorites']) ? 'checked' : '' }}>
                                <label class="form-check-label" for="filter_favorites">Mostrar apenas as questões Favoritas</label>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-12 col-md-2 col-lg-2"></div>

                    <div class="col-12 col-sm-12 col-md-5 col-lg-5">
                        <div class="p-6">
                            <small class="text-light fw-medium">Foram encotradas: X Questões</small>
                            <div class="form-floating form-floating-outline mt-4">
                                <input type="number" name="quanty_questions" id="quanty_questions" class="form-control" value="{{ old('quanty_questions', $filters['quanty_questions'] ?? '') }}" />
                                <label for="quanty_questions">N° de Questões</label>
                            </div>
                            <div class="form-floating form-floating-outline mt-4">
                                <input type="text" name="title" id="notebook_title" class="form-control" value="{{ old('title', $notebook->title) }}" required />
                                <label for="title">Dê um nome ao Caderno:</label>
                            </div>
                            <input type="hidden" name="topics" id="selected-topics-hidden"/>
                            <button type="submit" class="btn btn-success w-100 mt-2">Atualizar Caderno</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>      
    </div>

    <script>
        const contentTopicsMap = JSON.parse(`{!! json_encode($contents->mapWithKeys(fn($c) => [
            $c->id => [
                'title' => $c->title,
                'topics' => $c->topics->pluck('id')->map(fn($id) => (string) $id)->toArray(),
                'topicsTitles' => $c->topics->mapWithKeys(fn($t) => [$t->id => $t->title])
            ]
        ])) !!}`);

        $(document).ready(function () {

            function renderAvailableTopics() {
                const selectedTopics = new Set();
                $('#selected-topics option').each(function () {
                    const val = $(this).val();
                    if (val.startsWith('topic:')) {
                        selectedTopics.add(val.split(':')[1]);
                    }
                });

                $('#available-topics optgroup').each(function () {
                    const $group = $(this);
                    const contentId = $group.data('content-id');
                    const topicIds = contentTopicsMap[contentId]?.topics ?? [];
                    const title = contentTopicsMap[contentId]?.title ?? 'Conteúdo';

                    let hasVisible = false;
                    $group.find('option[value="content:' + contentId + '"]').remove();

                    $group.find('option').each(function () {
                        const val = $(this).val();
                        if (val.startsWith('topic:')) {
                            const topicId = val.split(':')[1];
                            const shouldShow = !selectedTopics.has(topicId);
                            $(this).toggle(shouldShow);
                            if (shouldShow) hasVisible = true;
                        }
                    });

                    const notSelected = topicIds.some(id => !selectedTopics.has(String(id)));
                    if (notSelected) {
                        const option = $('<option>', {
                            value: 'content:' + contentId,
                            'data-content-id': contentId,
                            class: 'content-option',
                            text: '[Todo] ' + title
                        });
                        $group.prepend(option);
                        hasVisible = true;
                    }

                    $group.toggle(hasVisible);
                });
            }

            function renderSelectedTopics() {

                const selectedTopicIds = new Set();
                $('#selected-topics option').each(function () {
                    const val = $(this).val();
                    if (val.startsWith('topic:')) {
                        selectedTopicIds.add(val.split(':')[1]);
                    }
                });

                $('#selected-topics').empty();
                Object.entries(contentTopicsMap).forEach(([contentId, data]) => {
                    const { title, topics } = data;

                    const allSelected = topics.every(id => selectedTopicIds.has(id));

                    if (allSelected) {
                        $('#selected-topics').append(
                            $('<option>', {
                                value: 'content:' + contentId,
                                'data-content-id': contentId,
                                class: 'content-option',
                                text: `[Todo] ${title}`
                            })
                        );
                    }

                    topics.forEach(topicId => {
                        if (selectedTopicIds.has(topicId)) {
                            $('#selected-topics').append(
                                $('<option>', {
                                    value: 'topic:' + topicId,
                                    'data-content-id': contentId,
                                    text: contentTopicsMap[contentId].topicsTitles?.[topicId] || ('Tópico ' + topicId)
                                })
                            );
                        }
                    });
                });
            }

            function renderLists() {
                renderAvailableTopics();
                renderSelectedTopics();
            }

            function filterOptions(inputId, selectId) {
                const search = $(inputId).val().toLowerCase();
                $(selectId + ' option').each(function () {
                    const text = $(this).text().toLowerCase();
                    $(this).toggle(text.includes(search));
                });
            }

            $('#search-available').on('input', function () {
                filterOptions('#search-available', '#available-topics');
            });

            $('#search-selected').on('input', function () {
                filterOptions('#search-selected', '#selected-topics');
            });

            $('#add-selected').on('click', function () {
                $('#available-topics option:selected').each(function () {
                    const val = $(this).val();

                    if ($('#selected-topics option[value="' + val + '"]').length === 0) {
                        $('#selected-topics').append($(this).clone());
                    }

                    if (val.startsWith('content:')) {
                        const contentId = val.split(':')[1];
                        $('#available-topics option[data-content-id="' + contentId + '"]').each(function () {
                            const tVal = $(this).val();
                            if (tVal !== val && $('#selected-topics option[value="' + tVal + '"]').length === 0) {
                                $('#selected-topics').append($(this).clone());
                            }
                        });
                    }
                });

                renderLists();
            });

            $('#remove-selected').on('click', function () {
                const toRemove = [];

                $('#selected-topics option:selected').each(function () {
                    const val = $(this).val();

                    if (val.startsWith('content:')) {
                        const contentId = val.split(':')[1];
                        $('#selected-topics option[data-content-id="' + contentId + '"]').each(function () {
                            toRemove.push($(this).val());
                        });
                    }

                    toRemove.push(val);
                });

                toRemove.forEach(function (val) {
                    $('#selected-topics option[value="' + val + '"]').remove();
                });

                renderLists();
            });

            $('#clear-all').on('click', function () {
                $('#selected-topics').empty();
                renderLists();
            });

            $('#available-topics').on('dblclick', 'option', function () {
                $(this).prop('selected', true);
                $('#add-selected').click();
            });

            $('#create-notebook-form').on('submit', function () {
                let selectedTopics = [];

                $('#selected-topics option').each(function () {
                    const val = $(this).val();
                    if (val.startsWith('topic:')) {
                        selectedTopics.push(val.split(':')[1]);
                    }
                });

                $('#selected-topics-hidden').val(JSON.stringify(selectedTopics));
            });

            renderLists();
        });
    </script>
@endsection
