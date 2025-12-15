@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="crm-page-title">Papelera</h1>

    

    <div class="principal">
        @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        <ul class="nav nav-tabs nav-fill mb-3" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#clientes">
                    Clientes
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#ventas">
                    Ventas
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#facturas">
                    Facturas
                </button>
            </li>
        </ul>

    <div class="tab-content">

    <div class="tab-pane fade show active" id="clientes">
        <table class="table table-bordered">
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
                @forelse($clientes as $cliente)
                    <tr>
                        <td>{{ $cliente->nombre }}</td>
                        <td>{{ $cliente->rut }}</td>
                        <td>{{ $cliente->correo }}</td>
                        <td>{{ $cliente->telefono }}</td>
                        <td>{{ $cliente->rubro }}</td>
                        <td class="text-center">
                            <form method="POST" action="{{ route('clientes.restore', $cliente->id) }}">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-warning btn-sm">Restaurar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr class="text-center"><td colspan="6">No hay clientes eliminados</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="tab-pane fade" id="ventas">
        <table class="table table-bordered">
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
                @forelse($ventas as $venta)
                    <tr>
                        <td>
                        @if($venta->cliente)
                            {{ $venta->cliente->nombre }}
                            @if($venta->cliente->trashed())
                                <span class="badge bg-danger text-white">Eliminado</span>
                            @endif
                        @else
                            <span class="text-danger">Cliente eliminado</span>
                        @endif
                        </td>
                        <td>${{ number_format($venta->monto, 0, ',', '.') }}</td>
                        <td>{{ $venta->fecha }}</td>
                        <td>{{ ucfirst($venta->estado) }}</td>
                        <td class="text-center">
                            <form method="POST" action="{{ route('ventas.restore', $venta->id) }}">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-warning btn-sm">Restaurar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr class="text-center"><td colspan="5">No hay ventas eliminadas</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="tab-pane fade" id="facturas">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>N° Factura</th>
                    <th>Cliente</th>
                    <th>Venta</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($facturas as $factura)
                    <tr>
                        <td>{{ $factura->id }}</td>
                        <td>{{ $factura->numero_factura }}</td>
                        <td>
                        @if($factura->cliente)
                            {{ $factura->cliente->nombre }}
                            @if($factura->cliente->trashed())
                                <span class="badge bg-danger text-white">Eliminado</span>
                            @endif
                        @else
                            <span class="text-danger">Cliente eliminado</span>
                        @endif
                        </td>
                        <td>
                            @if($factura->venta)
                                Venta #{{ $factura->venta->id }}
                                — ${{ number_format($factura->venta->monto, 0, ',', '.') }}

                                @if($factura->venta->trashed())
                                    <span class="badge bg-danger text-white">Eliminada</span>
                                @endif
                            @else
                                <span class="text-muted">Venta no disponible</span>
                            @endif
                        </td>
                        <td>{{ $factura->fecha_emision }}</td>
                        <td>{{ ucfirst($factura->estado) }}</td>
                        <td class="text-center">
                            <form method="POST" action="{{ route('facturas.restore', $factura->id) }}">
                                @csrf
                                @method('PATCH')
                                
                                <button class="btn btn-warning btn-sm">Restaurar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr class="text-center"><td colspan="7">No hay facturas eliminadas</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    </div>


</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const hash = window.location.hash;

    if (hash) {
        const triggerEl = document.querySelector(
            `.nav-link[data-bs-target="${hash}"]`
        );

        if (triggerEl) {
            const tab = new bootstrap.Tab(triggerEl);
            tab.show();
        }
    }
});
</script>
@endsection
