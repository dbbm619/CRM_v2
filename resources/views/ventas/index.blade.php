@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4 crm-page-title">Ventas</h1>

    <div class="text-center">
    <a href="{{ route('ventas.create') }}" class="btn btn-crm mb-3">Crear Venta</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered crm-cardvar">
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Monto</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ventas as $venta)
                <tr>
                    <td>{{ $venta->cliente->nombre }}</td>
                    <td>${{ number_format($venta->monto, 0, ',', '.') }}</td>
                    <td>{{ $venta->fecha }}</td>
                    <td>{{ ucfirst($venta->estado) }}</td>
                    <td class="text-center">
                        <a href="{{ route('ventas.edit', $venta->id) }}" class="btn btn-secondary btn-sm">Editar</a>

                        @if(auth()->user()->role === 'admin')
                        <form action="{{ route('ventas.destroy', $venta->id) }}" method="POST" 
                              style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm"
                                onclick="return confirm('¿Seguro que deseas eliminar esta venta?')">
                                Eliminar
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
