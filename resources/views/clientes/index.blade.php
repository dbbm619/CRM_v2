@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4 crm-page-title">Clientes</h1>

    <div class="text-center">
        <a href="{{ route('clientes.create') }}" class="btn btn-crm mb-3">Agregar Cliente</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered crm-cardvar">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>RUT</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Rubro</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($clientes as $cliente)
                <tr>
                    <td>{{ $cliente->nombre }}</td>
                    <td>{{ $cliente->rut }}</td>
                    <td>{{ $cliente->correo }}</td>
                    <td>{{ $cliente->telefono }}</td>
                    <td>{{ $cliente->rubro }}</td>
                    <td class="text-center">
                        <a href="{{ route('clientes.edit', $cliente->id) }}" class="btn btn-sm btn-secondary">Editar</a>
                        @if(auth()->user()->role === 'admin')
                        <form action="{{ route('clientes.destroy', $cliente->id) }}" method="POST" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que deseas eliminar este cliente?')">Eliminar</button>
                        </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
