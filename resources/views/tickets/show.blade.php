@extends('layouts.app')

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('title', 'Ticket '.$ticketCode)
@section('header', 'Ticket '.$ticketCode)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tickets.index') }}">Tickets</a></li>
    <li class="breadcrumb-item active">Detalle</li>
@endsection

<div class="modal fade" id="macroPreviewModal" tabindex="-1" role="dialog" aria-labelledby="macroPreviewLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="macroPreviewLabel">Vista previa de macro</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="macro-preview-content">Contenido final</label>
                    <textarea id="macro-preview-content" class="form-control" rows="6"></textarea>
                </div>
                <div class="form-group">
                    <label for="macro-preview-mode">Modo de inserción</label>
                    <select id="macro-preview-mode" class="form-control">
                        <option value="replace">Reemplazar respuesta actual</option>
                        <option value="append">Agregar al final</option>
                    </select>
                </div>
                <small class="text-muted">Puedes personalizar el texto antes de insertarlo.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="insert-macro-btn">
                    <i class="fas fa-level-down-alt mr-1"></i>Insertar en respuesta
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="macroEditModal" tabindex="-1" role="dialog" aria-labelledby="macroEditLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" id="macro-edit-form">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="macroEditLabel">Editar macro</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit-macro-name">Nombre</label>
                        <input type="text" class="form-control" id="edit-macro-name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit-macro-category">Categoría</label>
                        <input type="text" class="form-control" id="edit-macro-category" name="category">
                    </div>
                    <div class="form-group">
                        <label for="edit-macro-content">Contenido</label>
                        <textarea class="form-control" id="edit-macro-content" name="content" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit-macro-scope">Visibilidad</label>
                        <select name="scope" id="edit-macro-scope" class="form-control">
                            <option value="personal">Personal</option>
                            <option value="global">Global</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="noteEditModal" tabindex="-1" role="dialog" aria-labelledby="noteEditLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" id="note-edit-form" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="noteEditLabel">Editar nota interna</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit-note-content">Contenido</label>
                        <textarea name="content" id="edit-note-content" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="edit-note-important" name="is_important" value="1">
                            <label for="edit-note-important" class="form-check-label">Marcar como importante</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit-note-attachments">Adjuntar archivos adicionales</label>
                        <input type="file" name="attachments[]" id="edit-note-attachments" class="form-control-file" multiple>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ data_get($ticket, 'title', data_get($ticket, 'subject', 'Sin asunto')) }}</h3>
                    <span class="badge badge-info ml-2">{{ data_get($ticket, 'priority', 'Normal') }}</span>
                </div>
                <div class="card-body">
                    <p><strong>Código:</strong> {{ $ticketCode }}</p>
                    <p><strong>Cliente:</strong> {{ data_get($ticket, 'created_by_user.name', data_get($ticket, 'requester.name', 'N/D')) }}</p>
                    <p><strong>Estado:</strong> {{ \Illuminate\Support\Str::title(data_get($ticket, 'status', 'pendiente')) }}</p>
                    <p><strong>Creado:</strong> {{ data_get($ticket, 'created_at') ? \Carbon\Carbon::parse(data_get($ticket, 'created_at'))->toDayDateTimeString() : 'N/D' }}</p>
                    <p>{{ data_get($ticket, 'description') }}</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Conversación</h3>
                </div>
                <div class="card-body" style="max-height: 420px; overflow-y: auto;">
                    @forelse($responses as $response)
                        <div class="post clearfix">
                            <div class="user-block">
                                <span class="username">{{ data_get($response, 'author.name', 'Sistema') }}</span>
                                <span class="description">
                                    {{ data_get($response, 'created_at') ? \Carbon\Carbon::parse(data_get($response, 'created_at'))->diffForHumans() : 'hace instantes' }}
                                    @if (data_get($response, 'internal'))
                                        <span class="badge badge-warning ml-1">Interno</span>
                                    @endif
                                </span>
                            </div>
                            <p>{!! nl2br(e(data_get($response, 'content'))) !!}</p>
                        </div>
                        <hr>
                    @empty
                        <p class="text-muted">No hay respuestas aún.</p>
                    @endforelse
                </div>
            </div>

            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Responder al cliente</h3>
                </div>
                <form method="POST" action="{{ route('tickets.responses.store', $ticketCode) }}">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="response-content">Mensaje</label>
                            <textarea name="content" id="response-content" class="form-control" rows="5" required>{{ old('content') }}</textarea>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="internal" value="1" class="form-check-input" id="internal-response">
                            <label class="form-check-label" for="internal-response">Marcar como interna</label>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-paper-plane mr-1"></i>Enviar
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card card-warning">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <h3 class="card-title mb-0">Notas internas</h3>
                    <span class="badge badge-light">{{ $notes->count() }} notas</span>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('tickets.show', $ticketCode) }}" class="mb-3">
                        <input type="hidden" name="macro_search" value="{{ $macroFilters['search'] }}">
                        <input type="hidden" name="macro_category" value="{{ $macroFilters['category'] }}">
                        <input type="hidden" name="macro_author" value="{{ $macroFilters['author'] }}">
                        <input type="hidden" name="macro_only_favorites" value="{{ $macroFilters['favorites'] ? 1 : 0 }}">
                        <div class="form-row">
                            <div class="col-md-6 mb-2">
                                <input type="search" name="note_search" value="{{ $noteFilters['search'] }}" class="form-control" placeholder="Buscar por palabra clave">
                            </div>
                            <div class="col-md-6 mb-2">
                                <select name="note_author" class="form-control">
                                    <option value="">Todos los autores</option>
                                    @foreach($noteAuthors as $author)
                                        <option value="{{ $author->agent_id }}" {{ (int) $noteFilters['author'] === (int) $author->agent_id ? 'selected' : '' }}>
                                            {{ $author->agent_name ?? 'Agente #'.$author->agent_id }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-row align-items-center">
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input type="checkbox" name="note_only_important" value="1" class="form-check-input" id="filter-important" {{ $noteFilters['important'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="filter-important">Solo importantes</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input type="checkbox" name="note_only_with_attachments" value="1" class="form-check-input" id="filter-attachments" {{ $noteFilters['attachments'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="filter-attachments">Con adjuntos</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2 text-md-right">
                                <button class="btn btn-sm btn-outline-dark" type="submit">
                                    <i class="fas fa-filter mr-1"></i>Filtrar
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="notes-wrapper" style="max-height: 260px; overflow-y:auto;">
                        @forelse($notes as $note)
                            <div class="callout {{ $note->is_important ? 'callout-danger' : 'callout-info' }} p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <strong>{{ $note->agent_name ?? 'Agente #'.$note->agent_id }}</strong>
                                        <small class="d-block text-muted">{{ $note->created_at->diffForHumans() }}</small>
                                    </div>
                                    @if($note->is_important)
                                        <span class="badge badge-danger"><i class="fas fa-flag mr-1"></i>Importante</span>
                                    @endif
                                </div>
                                <p class="mb-2">{{ $note->content }}</p>
                                @if($note->mentions)
                                    <div class="mb-2">
                                        @foreach($note->mentions as $mention)
                                            <span class="badge badge-light">{{ $mention }}</span>
                                        @endforeach
                                    </div>
                                @endif
                                @if($note->attachments->isNotEmpty())
                                    <ul class="list-unstyled mb-2">
                                        @foreach($note->attachments as $attachment)
                                            <li>
                                                <a href="{{ Storage::url($attachment->path) }}" target="_blank">
                                                    <i class="fas fa-paperclip mr-1"></i>{{ $attachment->original_name }}
                                                </a>
                                                <small class="text-muted">({{ number_format($attachment->size / 1024, 1) }} KB)</small>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                                @if($note->agent_id === \App\Support\ExternalAuth::id())
                                    <div class="text-right">
                                        <button type="button"
                                                class="btn btn-sm btn-outline-secondary mr-2 edit-note-btn"
                                                data-update-url="{{ route('internal-notes.update', $note) }}"
                                                data-content="{{ e($note->content) }}"
                                                data-important="{{ $note->is_important ? '1' : '0' }}">
                                            <i class="fas fa-edit mr-1"></i>Editar
                                        </button>
                                        <form action="{{ route('internal-notes.destroy', $note) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted">Aún no hay notas registradas para este ticket.</p>
                        @endforelse
                    </div>
                </div>
                <div class="card-footer">
                    <form action="{{ route('internal-notes.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="ticket_code" value="{{ $ticketCode }}">
                        <div class="form-group">
                            <label for="note-content">Contenido</label>
                            <textarea name="content" id="note-content" class="form-control" rows="3" placeholder="Usa @usuario para mencionar" required></textarea>
                        </div>
                        <div class="form-row">
                            <div class="col-md-6">
                                <div class="form-check mb-2">
                                    <input type="checkbox" name="is_important" value="1" class="form-check-input" id="note-important">
                                    <label class="form-check-label" for="note-important">Marcar como importante</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <input type="file" name="attachments[]" class="form-control-file" multiple>
                                <small class="text-muted">Puedes adjuntar múltiples archivos (máx 5MB c/u)</small>
                            </div>
                        </div>
                        <button class="btn btn-warning btn-block" type="submit">
                            <i class="fas fa-lock mr-1"></i>Guardar nota
                        </button>
                    </form>
                </div>
            </div>

            <div class="card card-secondary mt-3">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <h3 class="card-title mb-0">Macros</h3>
                    <span class="badge badge-light">{{ $macros->count() }} disponibles</span>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('tickets.show', $ticketCode) }}" class="mb-3">
                        <input type="hidden" name="note_search" value="{{ $noteFilters['search'] }}">
                        <input type="hidden" name="note_author" value="{{ $noteFilters['author'] }}">
                        <input type="hidden" name="note_only_important" value="{{ $noteFilters['important'] ? 1 : 0 }}">
                        <input type="hidden" name="note_only_with_attachments" value="{{ $noteFilters['attachments'] ? 1 : 0 }}">
                        <div class="form-row">
                            <div class="col-md-6 mb-2">
                                <input type="search" name="macro_search" value="{{ $macroFilters['search'] }}" class="form-control" placeholder="Buscar por nombre o contenido">
                            </div>
                            <div class="col-md-6 mb-2">
                                <select name="macro_category" class="form-control">
                                    <option value="">Todas las categorías</option>
                                    @foreach($macroCategories as $category)
                                        <option value="{{ $category }}" {{ $macroFilters['category'] === $category ? 'selected' : '' }}>
                                            {{ $category }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-row align-items-center">
                            <div class="col-md-6 mb-2">
                                <select name="macro_author" class="form-control">
                                    <option value="">Todos los autores</option>
                                    @foreach($macroAuthors as $author)
                                        <option value="{{ $author->created_by }}" {{ (int) $macroFilters['author'] === (int) $author->created_by ? 'selected' : '' }}>
                                            {{ $author->created_by_name ?? 'Agente #'.$author->created_by }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input type="checkbox" name="macro_only_favorites" value="1" class="form-check-input" id="filter-favorites" {{ $macroFilters['favorites'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="filter-favorites">Solo favoritos</label>
                                </div>
                            </div>
                            <div class="col-md-2 mb-2 text-md-right">
                                <button class="btn btn-sm btn-outline-dark" type="submit">
                                    <i class="fas fa-search mr-1"></i>Filtrar
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="macros-list" style="max-height: 220px; overflow-y:auto;">
                        @forelse($macros as $macro)
                            <div class="border rounded p-2 mb-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $macro->name }}</strong>
                                        <small class="text-muted">({{ $macro->scope === 'global' ? 'Global' : 'Personal' }})</small>
                                        @if($macro->category)
                                            <span class="badge badge-info">{{ $macro->category }}</span>
                                        @endif
                                    </div>
                                    <div class="btn-group" role="group">
                                        <form action="{{ route('macros.favorite', $macro) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ in_array($macro->id, $favoriteMacroIds, true) ? 'btn-warning' : 'btn-outline-secondary' }}" title="Favorito">
                                                <i class="fas fa-star"></i>
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-outline-primary preview-macro-btn"
                                                data-macro-id="{{ $macro->id }}"
                                                data-macro-name="{{ $macro->name }}"
                                                data-preview-url="{{ route('macros.preview', $macro) }}"
                                                data-usage-url="{{ route('macros.usages.store', $macro) }}">
                                            <i class="fas fa-eye mr-1"></i>Preview
                                        </button>
                                        @if($macro->created_by === \App\Support\ExternalAuth::id())
                                            <button type="button" class="btn btn-sm btn-outline-secondary edit-macro-btn"
                                                    data-update-url="{{ route('macros.update', $macro) }}"
                                                    data-name="{{ $macro->name }}"
                                                    data-content="{{ e($macro->content) }}"
                                                    data-scope="{{ $macro->scope }}"
                                                    data-category="{{ $macro->category }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('macros.destroy', $macro) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" type="submit">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                                <p class="mb-0 text-muted small">{{ \Illuminate\Support\Str::limit($macro->content, 160) }}</p>
                            </div>
                        @empty
                            <p class="text-muted mb-0">Sin macros guardadas.</p>
                        @endforelse
                    </div>
                </div>
                <div class="card-footer">
                    <form action="{{ route('macros.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="macro-name">Nombre</label>
                            <input type="text" id="macro-name" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="macro-category">Categoría</label>
                            <input type="text" id="macro-category" name="category" class="form-control" placeholder="Ej. Facturación, Soporte">
                        </div>
                        <div class="form-group">
                            <label for="macro-content">Contenido</label>
                            <textarea name="content" id="macro-content" class="form-control" rows="3" placeholder="Puedes usar variables como &#123;&#123; ticket.code &#125;&#125;" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Visibilidad</label>
                            <select name="scope" class="form-control">
                                <option value="personal">Personal</option>
                                <option value="global">Global</option>
                            </select>
                        </div>
                        <button class="btn btn-secondary btn-block" type="submit">
                            <i class="fas fa-save mr-1"></i>Guardar macro
                        </button>
                    </form>
                    <small class="d-block text-muted mt-2">Variables disponibles:
                        @foreach($macroPlaceholders as $placeholder => $description)
                            <span class="badge badge-light">&#123;&#123; {{ $placeholder }} &#125;&#125;</span>
                        @endforeach
                    </small>
                </div>
            </div>

            <div class="card card-info mt-3">
                <div class="card-header">
                    <h3 class="card-title">Uso inteligente de macros</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Macros más usadas</h5>
                            <ul class="list-unstyled">
                                @forelse($topMacros as $macroStat)
                                    <li class="mb-1">
                                        {{ $macroStat->name }}
                                        <span class="badge badge-secondary">{{ $macroStat->usages_count }} usos</span>
                                    </li>
                                @empty
                                    <li class="text-muted">Sin registros aún.</li>
                                @endforelse
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>Macros más efectivas</h5>
                            <ul class="list-unstyled">
                                @forelse($macroEffectivenessReport as $macroStat)
                                    @php
                                        $ratio = $macroStat->usages_count > 0 ? round(($macroStat->positive_feedback_count / $macroStat->usages_count) * 100) : 0;
                                    @endphp
                                    <li class="mb-1">
                                        {{ $macroStat->name }}
                                        <span class="badge badge-success">{{ $ratio }}% efectividad</span>
                                    </li>
                                @empty
                                    <li class="text-muted">Aún no hay feedback.</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                    <hr>
                    <h5>Historial de uso por agente</h5>
                    <div class="list-group" style="max-height: 200px; overflow-y:auto;">
                        @forelse($macroUsageHistory as $usage)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $usage->macro->name ?? 'Macro eliminada' }}</strong>
                                    <small class="d-block text-muted">Ticket {{ $usage->ticket_code }} · {{ $usage->created_at->diffForHumans() }}</small>
                                    @if($usage->customized)
                                        <span class="badge badge-info">Personalizada</span>
                                    @endif
                                    @if($usage->feedback)
                                        <span class="badge badge-{{ $usage->feedback === 'positive' ? 'success' : 'danger' }}">
                                            {{ $usage->feedback === 'positive' ? '👍 Efectiva' : '👎 Mejorar' }}
                                        </span>
                                    @endif
                                </div>
                                @if(! $usage->feedback)
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-success macro-feedback-btn"
                                                data-usage-id="{{ $usage->id }}"
                                                data-feedback="positive"
                                                data-feedback-url="{{ route('macros.usages.feedback', $usage) }}">
                                            <i class="fas fa-thumbs-up"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger macro-feedback-btn"
                                                data-usage-id="{{ $usage->id }}"
                                                data-feedback="negative"
                                                data-feedback-url="{{ route('macros.usages.feedback', $usage) }}">
                                            <i class="fas fa-thumbs-down"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-muted mb-0">Aún no registras uso de macros.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@php
    $macroTicketContext = [
        'ticket_code' => $ticketCode,
        'ticket_title' => data_get($ticket, 'title', data_get($ticket, 'subject', '')),
        'customer_name' => data_get($ticket, 'created_by_user.name', data_get($ticket, 'requester.name', 'Cliente')),
    ];
@endphp

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const ticketContext = @json($macroTicketContext);

            const previewModal = $('#macroPreviewModal');
            let currentMacroContext = {
                usageUrl: null,
                renderedContent: '',
            };

            $('.preview-macro-btn').on('click', function () {
                const previewUrl = this.dataset.previewUrl;
                currentMacroContext = {
                    usageUrl: this.dataset.usageUrl,
                    renderedContent: '',
                };
                $('#macroPreviewLabel').text('Vista previa · ' + (this.dataset.macroName || ''));

                fetch(previewUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        ticket_code: ticketContext.ticket_code,
                        ticket_title: ticketContext.ticket_title,
                        customer_name: ticketContext.customer_name,
                    }),
                })
                    .then(response => response.json())
                    .then(payload => {
                        const content = payload?.data?.content || '';
                        currentMacroContext.renderedContent = content;
                        $('#macro-preview-content').val(content);
                        $('#macro-preview-mode').val('replace');
                        previewModal.modal('show');
                    })
                    .catch(() => alert('No se pudo generar la vista previa de la macro.'));
            });

            $('#insert-macro-btn').on('click', function () {
                const target = document.getElementById('response-content');
                if (!target) {
                    previewModal.modal('hide');
                    return;
                }

                const content = $('#macro-preview-content').val();
                const mode = $('#macro-preview-mode').val();

                if (mode === 'replace') {
                    target.value = content;
                } else {
                    target.value = target.value ? target.value + '\n\n' + content : content;
                }

                target.focus();

                if (currentMacroContext.usageUrl) {
                    fetch(currentMacroContext.usageUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            ticket_code: ticketContext.ticket_code,
                            content,
                            customized: content !== currentMacroContext.renderedContent,
                        }),
                    }).catch(() => {});
                }

                previewModal.modal('hide');
            });

            const macroEditModal = $('#macroEditModal');
            $('.edit-macro-btn').on('click', function () {
                $('#macro-edit-form').attr('action', this.dataset.updateUrl);
                $('#edit-macro-name').val(this.dataset.name);
                $('#edit-macro-category').val(this.dataset.category || '');
                $('#edit-macro-content').val(this.dataset.content);
                $('#edit-macro-scope').val(this.dataset.scope || 'personal');
                macroEditModal.modal('show');
            });

            const noteEditModal = $('#noteEditModal');
            $('.edit-note-btn').on('click', function () {
                $('#note-edit-form').attr('action', this.dataset.updateUrl);
                $('#edit-note-content').val(this.dataset.content);
                $('#edit-note-important').prop('checked', this.dataset.important === '1');
                noteEditModal.modal('show');
            });

            document.querySelectorAll('.macro-feedback-btn').forEach(button => {
                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    const feedbackUrl = this.dataset.feedbackUrl;

                    fetch(feedbackUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            feedback: this.dataset.feedback,
                        }),
                    })
                        .then(response => response.json())
                        .then(payload => {
                            const feedbackValue = payload?.data?.feedback;
                            const parentItem = this.closest('.list-group-item');
                            const infoBlock = parentItem ? parentItem.querySelector('.list-group-item > div') : null;

                            if (infoBlock && feedbackValue) {
                                const badge = document.createElement('span');
                                badge.className = 'badge badge-' + (feedbackValue === 'positive' ? 'success' : 'danger') + ' ml-2';
                                badge.textContent = feedbackValue === 'positive' ? '👍 Efectiva' : '👎 Mejorar';
                                infoBlock.appendChild(badge);
                            }

                            const group = this.closest('.btn-group');
                            if (group) {
                                group.remove();
                            }
                        })
                        .catch(() => alert('No se pudo registrar el feedback.'));
                });
            });
        });
    </script>
@endpush
