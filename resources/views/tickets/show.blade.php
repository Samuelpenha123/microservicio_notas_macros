@extends('layouts.app')

@section('title', 'Ticket '.$ticketCode)
@section('header', 'Ticket '.$ticketCode)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('tickets.index') }}">Tickets</a></li>
    <li class="breadcrumb-item active">Detalle</li>
@endsection

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
                <div class="card-header">
                    <h3 class="card-title">Notas internas</h3>
                </div>
                <div class="card-body" style="max-height: 240px; overflow-y: auto;">
                    @forelse($notes as $note)
                        <div class="callout callout-info p-3">
                            <small class="text-muted d-block mb-1">{{ $note->created_at->diffForHumans() }}</small>
                            <p class="mb-2">{{ $note->content }}</p>
                            <form action="{{ route('internal-notes.destroy', $note) }}" method="POST" class="text-right">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" type="submit">Eliminar</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-muted">Aún no agregas notas privadas.</p>
                    @endforelse
                </div>
                <div class="card-footer">
                    <form action="{{ route('internal-notes.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="ticket_code" value="{{ $ticketCode }}">
                        <div class="form-group">
                            <textarea name="content" class="form-control" rows="3" placeholder="Nueva nota privada" required></textarea>
                        </div>
                        <button class="btn btn-warning btn-block" type="submit">
                            <i class="fas fa-lock mr-1"></i>Guardar nota
                        </button>
                    </form>
                </div>
            </div>

            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title">Macros</h3>
                </div>
                <div class="card-body" style="max-height: 200px; overflow-y:auto;">
                    @forelse($macros as $macro)
                        <div class="d-flex align-items-center justify-content-between border-bottom py-2">
                            <button type="button" class="btn btn-sm btn-outline-primary mr-2 apply-macro"
                                    data-content="{{ e($macro->content) }}">
                                {{ $macro->name }}
                            </button>
                            @if($macro->created_by === \App\Support\ExternalAuth::id())
                                <form action="{{ route('macros.destroy', $macro) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-link text-danger" type="submit">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted mb-0">Sin macros guardadas.</p>
                    @endforelse
                </div>
                <div class="card-footer">
                    <form action="{{ route('macros.store') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="macro-name">Nombre</label>
                            <input type="text" id="macro-name" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="macro-content">Contenido</label>
                            <textarea name="content" id="macro-content" class="form-control" rows="3" required></textarea>
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
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.apply-macro').forEach(button => {
                button.addEventListener('click', () => {
                    const textArea = document.getElementById('response-content');
                    if (!textArea) return;
                    const content = button.getAttribute('data-content');
                    textArea.value = content;
                    textArea.focus();
                });
            });
        });
    </script>
@endpush
