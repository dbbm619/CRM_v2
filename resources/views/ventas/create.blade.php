@extends('layouts.app')

@section('content')
    <div class="container">
        <h1 class="mb-4 crm-page-title">Nueva Venta</h1>

        <div class="principal col-md-7">
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

        <form action="{{ route('ventas.store') }}" method="POST" novalidate>
            @csrf

            @include('ventas.form')

            <div class="d-flex align-items-end flex-grow-1 gap-2 mt-5 mb-3">
                <a href="{{ route('ventas.index') }}" class="btn btn-secondary me-2 w-100">Volver</a>
                <button class="btn btn-crm w-100">Guardar Venta</button>
            </div>
            
        </form>
        </div>
        <br>
        <br>
    </div>
@endsection
