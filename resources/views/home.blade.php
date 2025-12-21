@extends('layouts.app')

@section('content')
    <div class="container">

        <div class="text-center">
            <img src="{{ asset('img/logo-sinbg.png') }}" alt="Logo" style="height: 200px;">

        </div>

        <div class="principal">
            {{-- ======================= --}}
            {{--  FORMULARIO DE FILTRO   --}}
            {{-- ======================= --}}
            <form method="GET" action="{{ route('home') }}" class="mb-4 p-3 border rounded crm-filter">

                <div class="d-flex flex-row gap-3">

                    <div class="flex-grow-1 text-center">
                        <label>Cliente</label>
                        <select name="cliente_id" class="form-control">
                            <option value="">Todos</option>
                            @foreach ($listaClientes as $id => $nombre)
                                <option value="{{ $id }}" {{ request('cliente_id') == $id ? 'selected' : '' }}>
                                    {{ $nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex-grow-1 text-center">
                        <label>Tipo Cliente</label>
                        <select name="tipo" class="form-control">
                            <option value="">Todos</option>
                            <option value="recurrente" {{ request('tipo') == 'recurrente' ? 'selected' : '' }}>Recurrente
                            </option>
                            <option value="oneshot" {{ request('tipo') == 'oneshot' ? 'selected' : '' }}>One-Shot</option>
                        </select>
                    </div>

                    <div class="flex-grow-1 text-center">
                        <label>Desde</label>
                        <input type="date" name="desde" id="desde" class="form-control"
                            value="{{ request('desde') }}">
                    </div>

                    <div class="flex-grow-1 text-center">
                        <label>Hasta</label>
                        <input type="date" name="hasta" id="hasta" class="form-control"
                            value="{{ request('hasta') }}">
                    </div>
                    <div class="flex-grow-1 text-center">
                        <label>Periodo</label>
                        <select name="periodo" class="form-control" id="periodo">
                            <option value="">Todos</option>
                            <option value="year">Año actual</option>
                            <option value="semester">Semestre actual</option>
                            <option value="four_months">Cuatrimestre actual</option>
                            <option value="quarter">Trimestre actual</option>

                        </select>
                    </div>

                    <div class="d-flex align-items-end flex-grow-1 gap-2">
                        <button class="btn btn-crm w-100">Filtrar</button>
                        <a href="{{ route('home') }}" class="btn btn-secondary w-100">Limpiar</a>
                    </div>

                </div>

            </form>

            {{-- ======================= --}}
            {{--  TARJETAS ESTADÍSTICAS  --}}
            {{-- ======================= --}}
            <div class="row mb-4 mt-5">
                <div class="col-md-4">
                    <div class="card text-white crm-card mb-3">
                        <div class="card-body text-center">
                            <h5 class="card-title text-center">Clientes</h5>
                            <h2 class="text-center">{{ $clientesActivosCount }}</h2>
                            <a href="{{ route('clientes.index') }}" class="btn btn-crm">Ver Clientes</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card text-white crm-card mb-3">
                        <div class="card-body text-center">
                            <h5 class="card-title text-center">Ventas Activas</h5>
                            <h2 class="text-center">{{ $totalVentas }}</h2>
                            <a href="{{ route('ventas.index') }}" class="btn btn-crm">Ver Ventas</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card text-white crm-card mb-3">
                        <div class="card-body text-center">
                            <h5 class="card-title text-center">Facturas Activas</h5>
                            <h2 class="text-center">{{ $totalFacturas }}</h2>
                            <a href="{{ route('facturas.index') }}" class="btn btn-crm">Ver Facturas</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card text-white bg-secondary crm-cardvar mb-3">
                        <div class="card-body text-center">
                            <h5 class="card-title text-center">Ventas Pendientes por Cobrar</h5>
                            <h2 class="text-center">${{ number_format($cuentaPorCobrar, 0, ',', '.') }}</h2>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card text-white bg-danger crm-cardvar mb-3">
                        <div class="card-body text-center">
                            <h5 class="card-title text-center">Ventas Anuladas</h5>
                            <h2 class="text-center">${{ number_format($perdidas, 0, ',', '.') }}</h2>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ======================= --}}
            {{--        GRÁFICOS         --}}
            {{-- ======================= --}}
            <div class="row justify-content-around mt-5">
                <div class="col-md-3 crm-cardest align-content-around">
                    <h4 class="text-center mt-3">Clientes con más Ventas</h4>
                    <canvas class="mb-3" id="ventasClienteChart"></canvas>
                </div>

                <div class="col-md-3 crm-cardest align-content-around">
                    <h4 class="text-center mt-3">Estado de Ventas</h4>
                    <canvas class="mb-3" id="ventasEstadoChart"></canvas>
                </div>
                <div class="col-md-3 crm-cardest align-content-around">
                    <h4 class="text-center mt-3">Estado de Facturas</h4>
                    <canvas class="mb-3" id="facturasEstadoChart"></canvas>
                </div>


            </div>
            <div class="row justify-content-around">
                <div class="col-md-11 mt-5 crm-cardest align-content-around">
                    <h4 class="text-center mt-3">Ventas por Mes</h4>
                    <canvas class="mb-3" id="ventasMesChart" width="200" height="70"></canvas>
                </div>
            </div>

            <div class="row justify-content-around">
                <div class="col-md-11 mt-5 crm-cardest align-content-around">
                    <h4 class="text-center mt-3">Ingresos Totales por Mes</h4>
                    <canvas class="mb-3" id="montosMesChart" width="200" height="70"></canvas>
                    <a href="{{ route('export.ingresos', request()->query()) }}" class="btn btn-success btn-sm mt-2">
                        Exportar a Excel
                    </a>
                </div>
            </div>

            <div class="row justify-content-around">
                <div class="col-md-11 mt-5 crm-cardest align-content-around">
                    <h4 class="text-center mt-3">Flujo Real de Caja Mensual</h4>
                    <canvas class="mb-3" id="flujoCajaChart" width="200" height="70"></canvas>
                    <a href="{{ route('export.flujo', request()->query()) }}" class="btn btn-success btn-sm mt-2">
                        Exportar a Excel
                    </a>

                </div>
            </div>

            <div class="row justify-content-around mt-5">

                <div class="col-md-5 crm-cardest align-content-around">
                    <h5 class="text-center mt-3">Embudo de Ventas</h5>
                    <canvas class="mb-3" id="embudoChart" width="200" height="70"></canvas>
                </div>

                <div class="col-md-5 crm-cardest align-content-around">
                    <h5 class="text-center mt-3">Tasa de Conversión</h5>
                    <canvas class="mb-3" id="tasaChart"
                        style="max-width: 200px; max-height: 200px; margin: 0 auto; display: flex; justify-content: center; align-items: center;"></canvas>
                </div>

            </div>

        </div>



    </div>
    <br>
    <br>

    {{-- Cargar Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Gráfico Ventas por Mes

        const ventasMesCtx = document.getElementById('ventasMesChart').getContext('2d');
        new Chart(ventasMesCtx, {
            type: 'bar',
            data: {
                labels: @json($labelsVentasMes),
                datasets: [{
                    data: @json($ventasPorMes->values()),
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            precision: 0
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y; // solo el número
                            }
                        }
                    }
                },

            },
        });

        // Gráfico Estado Facturas
        const facturasEstadoCtx = document.getElementById('facturasEstadoChart').getContext('2d');
        new Chart(facturasEstadoCtx, {
            type: 'pie',
            data: {
                labels: @json($facturasEstadoLabels),
                datasets: [{
                    label: 'Cantidad',
                    data: @json($facturasEstadoData),
                    backgroundColor: [
                        'rgba(255, 205, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(255, 99, 132, 0.8)',
                    ]
                }]
            }
        });

        // Gráfico Estado Ventas
        const ventasEstadoCtx = document.getElementById('ventasEstadoChart').getContext('2d');
        new Chart(ventasEstadoCtx, {
            type: 'pie',
            data: {
                labels: @json($ventasEstadoLabels),
                datasets: [{
                    data: @json($ventasEstadoData),
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.7)', // pagada
                        'rgba(255, 205, 86, 0.7)', // pendiente
                        'rgba(255, 99, 132, 0.7)' // cancelada
                    ]
                }]
            }
        });

        // Gráfico Montos por Mes
        const montosMesCtx = document.getElementById('montosMesChart').getContext('2d');
        new Chart(montosMesCtx, {
            type: 'line',
            data: {
                labels: @json($labelsVentasMes),
                datasets: [{
                    label: 'Ingresos Mensuales ($)',
                    data: @json($montosPorMes->values()),
                    borderWidth: 2,
                    fill: true,
                }]
            },
            options: {
                scales: {
                    y: {
                        title: {
                            display: true,
                            text: '$ CLP',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        },
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            precision: 0
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y; // solo el número
                            }
                        }
                    }
                }
            }
        });

        // Gráfico Flujo Real De Caja
        const flujoCajaCtx = document.getElementById('flujoCajaChart').getContext('2d');
        new Chart(flujoCajaCtx, {
            type: 'line',
            data: {
                labels: @json($labelsVentasMes),

                datasets: [{
                    label: 'Ingreso Real ($)',
                    data: @json($flujoCaja->values()),
                    borderWidth: 2,
                    fill: true,
                }]
            },
            options: {
                scales: {
                    y: {
                        title: {
                            display: true,
                            text: '$ CLP',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        },
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            precision: 0
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y; // solo el número
                            }
                        }
                    }
                }
            }

        });

        // Gráfico Ventas por Cliente
        const ventasClienteCtx = document.getElementById('ventasClienteChart').getContext('2d');
        new Chart(ventasClienteCtx, {
            type: 'bar',
            data: {
                labels: @json($nombresClientes->values()),
                datasets: [{
                    label: 'Cantidad de Ventas',
                    data: @json($ventasPorCliente->values()),
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            precision: 0
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y; // solo el número
                            }
                        }
                    }
                }
            }
        });

        //Embudo de ventas
        const embudoCtx = document.getElementById('embudoChart').getContext('2d');
        new Chart(embudoCtx, {
            type: 'bar',
            data: {
                labels: ['Oportunidades', 'Clientes Activos', 'Clientes One-Shot', 'Clientes Recurrentes',
                    'Ventas'
                ],
                datasets: [{
                    label: 'Cantidad',
                    data: [
                        {{ $totalOportunidades }},
                        {{ $clientesActivosCount }},
                        {{ $clientesOneShotCount }},
                        {{ $clientesRecurrentesCount }},
                        {{ $totalVentas }}
                    ],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(255, 205, 86, 0.7)',
                        'rgba(255, 159, 64, 0.7)',
                        'rgba(255, 99, 132, 0.7)'
                    ]
                }]
            },
            options: {
                indexAxis: 'y', // bar horizontal para simular embudo
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.x; // número absoluto
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Medidor de tasa de conversión
        const tasaCtx = document.getElementById('tasaChart').getContext('2d');
        new Chart(tasaCtx, {
            type: 'doughnut',
            data: {
                labels: ['Convertidos', 'No Convertidos'],
                datasets: [{
                    data: [{{ $tasaConversion }}, {{ 100 - $tasaConversion }}],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(220, 220, 220, 0.3)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // importante para controlar tamaño
                plugins: {
                    legend: {
                        display: false
                    },

                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + '%';
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: '{{ $tasaConversion }}%',
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    }
                }
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            const periodo = document.getElementById('periodo');
            const desde = document.getElementById('desde');
            const hasta = document.getElementById('hasta');

            function actualizarFechasPorPeriodo() {
                const hoy = new Date();
                let start, end;
                switch (periodo.value) {
                    case 'year':
                        start = new Date(hoy.getFullYear(), 0, 1); // 1 de enero
                        break;
                    case 'semester':
                        if (hoy.getMonth() < 6) {
                            start = new Date(hoy.getFullYear(), 0, 1); // 1er semestre
                        } else {
                            start = new Date(hoy.getFullYear(), 6, 1); // 2do semestre
                        }
                        break;
                    case 'quarter':
                        const trimestre = Math.floor(hoy.getMonth() / 3);
                        start = new Date(hoy.getFullYear(), trimestre * 3, 1);
                        break;
                    case 'four_months':
                        const cuatrimestre = Math.floor(hoy.getMonth() / 4);
                        start = new Date(hoy.getFullYear(), cuatrimestre * 4, 1);
                        break;
                    default:
                        start = '';
                        end = '';
                }
                end = new Date(hoy);
                // Asignar fechas al input en formato yyyy-mm-dd
                if (start && end) {
                    desde.value = start.toISOString().split('T')[0];
                    hasta.value = end.toISOString().split('T')[0];
                } else {
                    desde.value = '';
                    hasta.value = '';
                }

                // Deshabilitar o habilitar fechas
                desde.disabled = periodo.value !== '';
                hasta.disabled = periodo.value !== '';
            }

            // Cambiar periodo
            periodo.addEventListener('change', actualizarFechasPorPeriodo);

            // Cambiar fechas manualmente
            [desde, hasta].forEach(input => {
                input.addEventListener('input', () => {
                    if (desde.value || hasta.value) {
                        periodo.value = '';
                        desde.disabled = false;
                        hasta.disabled = false;
                    }
                });
            });

            // Inicializar
            actualizarFechasPorPeriodo();
        });
    </script>
@endsection
