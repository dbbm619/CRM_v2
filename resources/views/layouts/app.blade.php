<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        /* Paleta del CRM */
            :root {
                --crm-primary: #000000ff;      /* Azul oscuro */
                --crm-secondary: #00CFFF;    /* Celeste corporativo */
                --crm-background: #82afd7ff;
                --crm-body: #E9F1F8;   /* Fondo claro */
                --crm-card: #2C74B3;         /* Tarjetas */
                --crm-text: #f8f2f2ff;         /* Texto principal */
                --crm-danger: #E63946;       /* Rojo personalizado */
                --crm-warning: #FFC300;      /* Amarillo */
                --crm-success: #06D6A0;      /* Verde */
                --crm-hover:  #585f77ff;
            }
            body {
                background-color: var(--crm-body) !important;
                min-height: 100vh;
                font-family: 'Inter', sans-serif !important;
            }
            .principal {
                background-color: var(--crm-background);
                border-radius: 16px;             /* Esquinas suaves */
                padding: 2rem 2.5rem;            /* Espaciado interno */
                margin-top: 2rem;                /* Separar del logo */
                box-shadow: 0 6px 20px rgba(0,0,0,0.12);  /* Sombra elegante */
                max-width: 1200px;               /* Limitar el ancho */
                margin-left: auto;
                margin-right: auto;
                border-top: 6px solid #0B1D5F;
                opacity: 0;
                transform: translateY(10px);
                animation: fadeSlideIn 0.6s ease-out forwards;   
            }
            @keyframes fadeSlideIn {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .crm-page-title {
                color: #000000 !important;      /* negro */
                font-size: 3.2rem !important;   /* tamaño grande */
                font-weight: 700 !important;    /* negrita */
                text-align: center !important;  /* centrado */
                margin-bottom: 25px !important;
                margin-top: 25px !important; /* separación */
            }

            /* Tarjetas personalizadas */
            .crm-card {
                background-color: var(--crm-card) !important;
                color: var(--crm-text) !important;
                padding: 10px;
                box-shadow: 0 3px 10px rgba(0,0,0,0.15);
                border: 1px solid var(--crm-primary) !important;
                border-radius: 8px !important;
            }


            .crm-cardvar {
                padding: 10px;
                box-shadow: 0 3px 10px rgba(0,0,0,0.15);
                border: 1px solid var(--crm-primary) !important;
                border-radius: 8px !important;
            }
            .crm-cardest {
                background-color: #ffffff !important;              
                box-shadow: 0 3px 10px rgba(0,0,0,0.15);
                border: 1px solid var(--crm-primary) !important;
                border-radius: 8px !important;
            }

            /* Títulos de tarjetas */
            .crm-card .card-title {
                font-weight: bold;
                color: var(--crm-text);
            }

            /* Botones corporativos */
            .btn-crm {
                background-color: var(--crm-primary) !important;
                color: white !important;
                
                
            }

            .btn-crm:hover {
              
                background-color: var(--crm-hover) !important;
                color: var(--crm-text) !important;
                
            }

            /* Fondo formulario de filtros */
            .crm-filter {
                        background-color: var(--crm-background) !important;
                        border: 1px solid var(--crm-primary) !important;
                        border-radius: 8px !important;
                        padding: 20px !important;
                        font-size: 0.85rem;
                        font-weight: 500;
                        box-shadow: 0 3px 10px rgba(0,0,0,0.15);
            }

            label {
                color: var(--crm-primary) !important;
                font-weight: bold;
            }
            .navbar-custom {
                background-color: #ffffff;
            }

            .navbar-custom .nav-link,
            .navbar-custom .navbar-brand {
                color: #0B1D5F; /* tu color secundario para enlaces y brand */
                
            }
            .navbar-custom .nav-link.active {
                
                font-weight: bold;
                color: #0B1D5F;   
            }

            .nav-tabs {
                border-bottom: 1px solid #0B1D5F;
                font-size: 20px;
            }

            .nav-tabs .nav-link {
                border: none;
                border-radius: 0;
                color: var(--crm-text); /* gris bootstrap */
            }
            
            /* Hover */
            .nav-tabs .nav-link:hover {
                
                border-top-left-radius: 8px;
                border-top-right-radius: 8px;
                background-color: rgba(165, 180, 193, 0.27);
                box-shadow: 0 -2px 6px rgba(0, 0, 0, 0.08);
            }

            /* Tab activo */
            .nav-tabs .nav-link.active {
                border: none;
                
                border-top-left-radius: 8px;
                border-top-right-radius: 8px;
                background-color: #0B1D5F;
                color: white;
                font-weight: 600;
            }

            .navbar-custom .nav-link:hover {
                color: #304aaaff; /* cambio de color al pasar el mouse */
            }
            table {
                border-radius: 12px;
                overflow: hidden;
            }

            table thead {
               
                font-weight: 600;
            }

            table td {
                padding: 14px !important;
            }
            .table-fixed {
                table-layout: fixed;
            }

            .table-fixed th,
            .table-fixed td {
                word-wrap: break-word;
            }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom mb-4">
        <div class="container">

            <a class="navbar-brand" href="{{ url('/home') }}">
                <img src="{{ asset('img/logo-sinbg.png') }}" 
                    alt="Logo" 
                    style="height: 40px;">
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">

               <ul class="navbar-nav me-auto">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" 
            href="{{ route('home') }}">Inicio</a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ request()->is('clientes*') && !request()->is('clientes-eliminados*') ? 'active' : '' }}"
            href="{{ route('clientes.index') }}">Clientes</a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ request()->is('ventas*') ? 'active' : '' }}"
            href="{{ route('ventas.index') }}">Ventas</a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ request()->is('facturas*') ? 'active' : '' }}"
            href="{{ route('facturas.index') }}">Facturas</a>
    </li>
</ul>

{{-- ESTE UL ES EL QUE VA A LA DERECHA --}}
<ul class="navbar-nav ms-auto d-flex align-items-center">

    @if(Auth::check())
        @switch(Auth::user()->role)
            @case('admin')
                <li>
                    <a class="nav-link {{ request()->is('eliminados*') ? 'active' : '' }}" href="{{ route('eliminados.index') }}" class="btn btn-outline-warning btn-sm">
                        Papelera
                    </a>
                </li>
                
                <li class="nav-item me-3">
                    <span class="badge bg-danger">Administrador</span>
                </li>
                @break

            @case('gestor')
                <li class="nav-item me-3">
                    <span class="badge bg-success">Gestor</span>
                </li>
                @break

            @default
                <li class="nav-item me-3">
                    <span class="badge bg-secondary">Usuario</span>
                </li>
        @endswitch
    @endif

    <li class="nav-item">
        <form method="POST" action="{{ route('logout') }}" class="d-inline">
            @csrf
            <button type="submit" class="nav-link btn btn-link p-0 m-0 text-decoration-none">
                {{ __('Cerrar Sesión') }}
            </button>
        </form>
    </li>

</ul>



            </div>
        </div>
    </nav>

        @yield('content')
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


    

</body>

</html>

