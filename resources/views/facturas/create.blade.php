@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4 crm-page-title">Crear Factura</h1>

        <div class="principal col-md-7">
        {{-- Recuadro de errores (igual que Clientes/Ventas) --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Hay errores en el formulario:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('facturas.store') }}" method="POST" novalidate>
            @csrf

            <div class="mb-3 text-center">
                <label>Cliente</label>
                <select name="cliente_id" class="form-control @error('cliente_id') is-invalid @enderror">
                    <option value="">Seleccione un cliente</option>
                    @foreach ($clientes as $cliente)
                        <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                            {{ $cliente->nombre }}
                        </option>
                    @endforeach
                </select>
                @error('cliente_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3 text-center">
                <label>Venta</label>
                <select name="venta_id" class="form-control @error('venta_id') is-invalid @enderror">
                    <option value="">Seleccione una venta</option>
                    @foreach ($ventas as $venta)
                        <option value="{{ $venta->id }}" {{ old('venta_id') == $venta->id ? 'selected' : '' }}>
                            Venta #{{ $venta->id }} — Cliente: {{ $venta->cliente->nombre ?? '—' }} —
                            ${{ number_format($venta->monto, 0, ',', '.') }} — {{ $venta->fecha }}
                        </option>
                    @endforeach
                </select>
                @error('venta_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3 text-center">
                <label>Número de Factura </label>
                <input type="text" name="numero_factura"
                    class="form-control @error('numero_factura') is-invalid @enderror" value="{{ old('numero_factura') }}"
                    placeholder="Ingresar numero de factura">
                @error('numero_factura')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3 text-center">
                <label>Fecha de Emisión</label>
                <input type="date" name="fecha_emision" class="form-control @error('fecha_emision') is-invalid @enderror"
                    value="{{ old('fecha_emision') }}">
                @error('fecha_emision')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3 text-center">
                <label>Estado</label>
                <select name="estado" class="form-control @error('estado') is-invalid @enderror">
                    <option value="">Seleccione un estado</option>
                    <option value="emitida" {{ old('estado') == 'emitida' ? 'selected' : '' }}>Emitida</option>
                    <option value="pagada" {{ old('estado') == 'pagada' ? 'selected' : '' }}>Pagada</option>
                    <option value="anulada" {{ old('estado') == 'anulada' ? 'selected' : '' }}>Anulada</option>
                </select>
                @error('estado')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex align-items-end flex-grow-1 gap-2 mt-5 mb-3">
                <a href="{{ route('facturas.index') }}" class="btn btn-secondary me-2 w-100">Volver</a>
                <button class="btn btn-crm w-100">Guardar Factura</button>
            </div>
            
        </form>
        </div>
        <br>
        <br>
    </div>
@endsection
