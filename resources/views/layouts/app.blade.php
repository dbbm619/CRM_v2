<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Paleta del CRM */
            :root {
                --crm-primary: #000000ff;      /* Azul oscuro */
                --crm-secondary: #00CFFF;    /* Celeste corporativo */
                --crm-background: #53a2d4;   /* Fondo claro */
                --crm-card: #0B1D5F;         /* Tarjetas */
                --crm-text: #f8f2f2ff;         /* Texto principal */
                --crm-danger: #E63946;       /* Rojo personalizado */
                --crm-warning: #FFC300;      /* Amarillo */
                --crm-success: #06D6A0;      /* Verde */
                --crm-hover:  #585f77ff;
            }
            body {
                background-color: var(--crm-background) !important;
                min-height: 100vh;
            }
            .crm-page-title {
                color: #000000 !important;      /* negro */
                font-size: 3.2rem !important;   /* tamaño grande */
                font-weight: 700 !important;    /* negrita */
                text-align: center !important;  /* centrado */
                margin-bottom: 25px !important; /* separación */
            }

            /* Tarjetas personalizadas */
            .crm-card {
                background-color: var(--crm-card) !important;
                color: var(--crm-text) !important;
                
                box-shadow: 0 3px 10px rgba(0,0,0,0.15);
                border: 2px solid var(--crm-primary) !important;
                border-radius: 8px !important;
            }

            .crm-cardvar {
                border: 2px solid var(--crm-primary) !important;
                border-radius: 8px !important;
            }
            .crm-cardest {
                background-color: #ffffff !important;              
                box-shadow: 0 3px 10px rgba(0,0,0,0.15);
                border: 2px solid var(--crm-primary) !important;
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
                        border: 2px solid var(--crm-primary) !important;
                        border-radius: 8px !important;
                        padding: 20px !important;
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

            .navbar-custom .nav-link:hover {
                color: #304aaaff; /* cambio de color al pasar el mouse */
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

                <ul class="navbar-nav ms-auto">
                
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" 
                         href="{{ route('home') }}">
                            Inicio
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('clientes*') ? 'active' : '' }}"
                            href="{{ route('clientes.index') }}">
                            Clientes
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('ventas*') ? 'active' : '' }}"
                            href="{{ route('ventas.index') }}">
                            Ventas
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('facturas*') ? 'active' : '' }}"
                            href="{{ route('facturas.index') }}">
                            Facturas
                        </a>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="nav-link btn btn-link p-0 m-0 align-middle text-decoration-none" style="height: 100%;">
                                {{ __('Cerrar Sesión') }}
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

        @yield('content')
 

    

</body>

</html>

