@extends('layouts.app')

@section('title', 'Tickets asignados')
@section('header', 'Tickets disponibles')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Listado traído desde la API externa</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped m-0">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Asunto</th>
                        <th>Cliente</th>
                        <th>Estado</th>
                        <th>Actualizado</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($tickets as $ticket)
                        <tr>
                            <td>{{ data_get($ticket, 'ticket_code', '#'.data_get($ticket, 'id')) }}</td>
                            <td>{{ data_get($ticket, 'subject', 'Sin asunto') }}</td>
                            <td>{{ data_get($ticket, 'requester.name', 'N/D') }}</td>
                            <td>
                                <span class="badge badge-{{ data_get($ticket, 'status') === 'open' ? 'success' : 'secondary' }}">
                                    {{ \Illuminate\Support\Str::title(data_get($ticket, 'status', 'pendiente')) }}
                                </span>
                            </td>
                            <td>
                                @if (data_get($ticket, 'updated_at'))
                                    {{ \Carbon\Carbon::parse(data_get($ticket, 'updated_at'))->diffForHumans() }}
                                @else
                                    N/D
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('tickets.show', data_get($ticket, 'ticket_code', data_get($ticket, 'id'))) }}" class="btn btn-primary btn-sm">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center p-4 text-muted">
                                No hay tickets para mostrar.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
