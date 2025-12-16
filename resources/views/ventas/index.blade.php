@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex gap-3 justify-content-center align-items-center">
        <h1 class="crm-page-title">Ventas</h1>
        <a href="{{ route('ventas.create') }}" class="btn btn-crm">Agregar</a>
    </div>

    <div class="principal">

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="mb-4 p-3 border rounded crm-filter">
        <div class="row">

            <!-- Búsqueda General -->
            <div class="col-md-3 text-center">
                <label>Buscar Venta</label>
                <input type="text" id="searchInput" class="form-control" placeholder="Mostrando Todas">
            </div>

            <!-- Filtro por columna -->
            <div class="col-md-3 text-center">
                <label>Buscar en</label>
                <select id="filterType" class="form-control">
                    <option value="all" selected>Todos</option>
                    <option value="cliente">Cliente</option>
                    <option value="monto">Monto</option>
                    <option value="fecha">Fecha</option>
                </select>
            </div>

            <!-- Rango de Fechas -->
            <div class="col-md-3 text-center">
                <label>Desde</label>
                <input type="date" id="dateFrom" class="form-control">
            </div>
            <div class="col-md-3 text-center">
                <label>Hasta</label>
                <input type="date" id="dateTo" class="form-control">
            </div>

        </div>

        <div class="row mt-3">

            <!-- Rango de montos -->
            <div class="col-md-3 text-center">
                <label>Monto mínimo</label>
                <input type="number" id="minAmount" class="form-control" placeholder="0">
            </div>

            <div class="col-md-3 text-center">
                <label>Monto máximo</label>
                <input type="number" id="maxAmount" class="form-control" placeholder="99999999">
            </div>
            <!-- Filtro por estado -->
            <div class="col-md-3 text-center">
                <label>Estado</label>
                <select id="estadoFilter" class="form-control">
                    <option value="">Todos</option>
                    <option value="pagada">Pagada</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="anulada">Anulada</option>
                </select>
            </div>
            
            <!-- Limpiar -->
            <div class="col-md-3 d-flex align-items-end">
                <button id="clearFilters" class="btn btn-secondary w-100">Limpiar Filtros</button>
            </div>
        </div>
    </div>

    <!-- Contador fijo -->
    <div class="mt-3" style="min-height: 30px;">
        <small id="resultsCount" class="text-muted"></small>
    </div>

    <div>
         <table class="table table-bordered crm-cardvar table-fixed">
        <thead>
            <tr>
                <th>
                    Cliente
                    <button type="button" id="sortCliente" style="border: none; background: none; padding: 0; cursor: pointer;">
                        🔽
                    </button>
                </th>
                <th>
                    Monto
                    <button type="button" id="sortMonto" style="border: none; background: none; padding: 0; cursor: pointer;">
                        🔽
                    </button>
                </th>
                <th>
                    Fecha
                    <button type="button" id="sortFecha" style="border: none; background: none; padding: 0; cursor: pointer;">
                        🔽
                    </button>
                </th>
                <th>Estado</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ventas as $venta)
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
                    <td>{{ ucfirst($venta->estado_formateado) }}</td>
                    <td class="text-center d-flex justify-content-around">
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
    </div>
    <br>
    <br>

</div>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("searchInput");
    const filterType = document.getElementById("filterType");
    const dateFrom = document.getElementById("dateFrom");
    const dateTo = document.getElementById("dateTo");
    const minAmount = document.getElementById("minAmount");
    const maxAmount = document.getElementById("maxAmount");
    const clearBtn = document.getElementById("clearFilters");
    const resultsCount = document.getElementById("resultsCount");
    const estadoFilter = document.getElementById("estadoFilter");


    const rows = Array.from(document.querySelectorAll("table tbody tr"));

    // Helpers
    function escapeRegExp(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    function highlight(text, search) {
        if (!search) return text;
        const escaped = escapeRegExp(search);
        const regex = new RegExp(`(${escaped})`, "gi");
        return text.replace(regex, "<mark>$1</mark>");
    }

    // Precache original HTML for each row so podemos restaurarlo luego
    const cache = rows.map(row => {
        const cols = row.querySelectorAll("td");
        return {
            row,
            original: {
                cliente: cols[0].innerHTML,
                monto: cols[1].innerHTML,
                fecha: cols[2].innerHTML,
                estado: cols[3].innerHTML
            }
        };
    });

    function filtrar() {
        const texto = searchInput.value.toLowerCase().trim();
        const filtro = filterType.value;

        const fDesde = dateFrom.value ? new Date(dateFrom.value) : null;
        const fHasta = dateTo.value ? new Date(dateTo.value) : null;

        const montoMin = minAmount.value ? parseInt(minAmount.value, 10) : null;
        const montoMax = maxAmount.value ? parseInt(maxAmount.value, 10) : null;

        let visibles = 0;

        cache.forEach(item => {
            const row = item.row;
            const cols = row.querySelectorAll("td");

            // Obtener valores "limpios" para comparar
            const clienteText = cols[0].textContent.toLowerCase();
            const montoText = cols[1].textContent; // ejemplo: $1.000
            const montoNum = parseInt(montoText.replace(/\D/g, ""), 10) || 0;
            const fechaText = cols[2].textContent.trim();
            const estadoText = cols[3].textContent.toLowerCase();
            const estadoSeleccionado = estadoFilter.value;

            let mostrar = true;

            // FILTRO POR TEXTO Y COLUMNA
            if (texto !== "") {
                if (filtro === "all") {
                    // buscar en todos los campos visibles (texto)
                    const hay = clienteText.includes(texto) ||
                                String(montoNum).includes(texto) ||
                                fechaText.toLowerCase().includes(texto) ||
                                estadoText.includes(texto);
                    if (!hay) mostrar = false;
                } else if (filtro === "cliente" && !clienteText.includes(texto)) {
                    mostrar = false;
                } else if (filtro === "monto" && !String(montoNum).includes(texto)) {
                    mostrar = false;
                } else if (filtro === "fecha" && !fechaText.toLowerCase().includes(texto)) {
                    mostrar = false;
                } else if (filtro === "estado" && !estadoText.includes(texto)) {
                    mostrar = false;
                }
            }

            // FILTROS POR FECHA (si la fecha en la tabla viene en formato ISO yyyy-mm-dd esto funciona bien)
            // Si tu fecha está en dd/mm/yyyy, habría que parsearla manualmente.
            if (mostrar && (fDesde || fHasta)) {
                const fechaVenta = new Date(fechaText);
                if (fDesde && fechaVenta < fDesde) mostrar = false;
                if (fHasta && fechaVenta > fHasta) mostrar = false;
            }

            // FILTRO POR MONTOS
            if (mostrar && (montoMin !== null || montoMax !== null)) {
                if (montoMin !== null && montoNum < montoMin) mostrar = false;
                if (montoMax !== null && montoNum > montoMax) mostrar = false;
            }

            // FILTRO POR ESTADO (select)
            if (mostrar && estadoSeleccionado !== "") {
                if (estadoText !== estadoSeleccionado) {
                    mostrar = false;
                }
            }

            // Mostrar u ocultar fila
            row.style.display = mostrar ? "" : "none";

            // Si mostramos y hay texto de búsqueda, aplicamos highlight SOLO a columnas textuales
            if (mostrar) {
                visibles++;
                if (texto !== "") {
                    cols[0].innerHTML = highlight(item.original.cliente, texto);
                    // Para monto y fecha, hacemos highlight sobre su HTML original también
                    cols[1].innerHTML = highlight(item.original.monto, texto);
                    cols[2].innerHTML = highlight(item.original.fecha, texto);
                    cols[3].innerHTML = highlight(item.original.estado, texto);
                } else {
                    // Restaurar HTML original si no hay búsqueda
                    cols[0].innerHTML = item.original.cliente;
                    cols[1].innerHTML = item.original.monto;
                    cols[2].innerHTML = item.original.fecha;
                    cols[3].innerHTML = item.original.estado;
                }
            } else {
                // Si no mostramos, también restauramos HTML para mantener consistencia
                cols[0].innerHTML = item.original.cliente;
                cols[1].innerHTML = item.original.monto;
                cols[2].innerHTML = item.original.fecha;
                cols[3].innerHTML = item.original.estado;
            }
        });

        // Actualizar contador
        resultsCount.innerText = (texto !== "" || dateFrom.value || dateTo.value || minAmount.value || maxAmount.value)
            ? `Resultados: ${visibles}`
            : "";
    }

    // EVENTOS
    searchInput.addEventListener("input", filtrar);
    filterType.addEventListener("change", filtrar);
    dateFrom.addEventListener("change", filtrar);
    dateTo.addEventListener("change", filtrar);
    minAmount.addEventListener("input", filtrar);
    maxAmount.addEventListener("input", filtrar);
    estadoFilter.addEventListener("change", filtrar);


    clearBtn.addEventListener("click", () => {
        searchInput.value = "";
        filterType.value = "all";
        estadoFilter.value = "";
        dateFrom.value = "";
        dateTo.value = "";
        minAmount.value = "";
        maxAmount.value = "";
        filtrar();
        searchInput.focus();
    });

    // filtrar inicialmente (por si hay valores precargados)
    filtrar();
});
document.addEventListener('DOMContentLoaded', () => {
    const sortCliente = document.getElementById('sortCliente');
    const sortMonto = document.getElementById('sortMonto');
    const sortFecha = document.getElementById('sortFecha');
    const tbody = document.querySelector('table tbody');
    
    let clienteAsc = true, montoAsc = true, fechaAsc = true;

    // Ordenar Cliente
    sortCliente.addEventListener('click', () => {
        const rows = Array.from(tbody.querySelectorAll('tr'));
        rows.sort((a, b) => {
            const clienteA = a.querySelector('td').textContent.trim().toLowerCase();
            const clienteB = b.querySelector('td').textContent.trim().toLowerCase();
            return clienteAsc ? clienteA.localeCompare(clienteB) : clienteB.localeCompare(clienteA);
        });
        tbody.innerHTML = '';
        rows.forEach(row => tbody.appendChild(row));
        clienteAsc = !clienteAsc;
        sortCliente.textContent = clienteAsc ? '🔽' : '🔼';
    });

    // Ordenar Monto
    sortMonto.addEventListener('click', () => {
    const rows = Array.from(tbody.querySelectorAll('tr'));
    rows.sort((a, b) => {
        // Tomamos el texto, quitamos $ y puntos, luego parseamos a float
        const montoA = parseFloat(a.querySelectorAll('td')[1].textContent.replace(/\$|\./g, ''));
        const montoB = parseFloat(b.querySelectorAll('td')[1].textContent.replace(/\$|\./g, ''));
        return montoAsc ? montoA - montoB : montoB - montoA;
    });
    tbody.innerHTML = '';
    rows.forEach(row => tbody.appendChild(row));
    montoAsc = !montoAsc;
    sortMonto.textContent = montoAsc ? '🔽' : '🔼';
});

    // Ordenar Fecha
    sortFecha.addEventListener('click', () => {
        const rows = Array.from(tbody.querySelectorAll('tr'));
        rows.sort((a, b) => {
            const fechaA = new Date(a.querySelectorAll('td')[2].textContent);
            const fechaB = new Date(b.querySelectorAll('td')[2].textContent);
            return fechaAsc ? fechaA - fechaB : fechaB - fechaA;
        });
        tbody.innerHTML = '';
        rows.forEach(row => tbody.appendChild(row));
        fechaAsc = !fechaAsc;
        sortFecha.textContent = fechaAsc ? '🔽' : '🔼';
    });
});
</script>
@endsection
