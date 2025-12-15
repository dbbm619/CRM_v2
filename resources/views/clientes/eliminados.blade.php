@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4 crm-page-title">Clientes Eliminados</h1>

    <div class="principal">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>RUT</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Rubro</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clientes as $cliente)
                <tr>
                    <td>{{ $cliente->nombre }}</td>
                    <td>{{ $cliente->rut }}</td>
                    <td>{{ $cliente->correo }}</td>
                    <td>{{ $cliente->telefono }}</td>
                    <td>{{ $cliente->rubro }}</td>
                    <td>
                        <form action="{{ route('clientes.restore', $cliente->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-warning btn-sm">
                                Restaurar
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    
</div>
@endsection
