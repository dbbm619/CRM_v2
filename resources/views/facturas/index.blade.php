@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex gap-3 justify-content-center align-items-center">
        <h1 class="crm-page-title">Facturas</h1>
        <a href="{{ route('facturas.create') }}" class="btn btn-crm">Agregar</a>
    </div>
    

    <div class="principal">


    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="mb-4 p-3 border rounded crm-filter">
        <div class="row">

            <!-- Búsqueda General -->
            <div class="col-md-2 text-center">
                <label>Buscar Factura</label>
                <input type="text" id="searchInput" class="form-control" placeholder="Mostrando Todas">
            </div>

            <!-- Filtro por columna -->
            <div class="col-md-2 text-center">
                <label>Buscar en</label>
                <select id="filterType" class="form-control">
                    <option value="all" selected>Todos</option>
                    <option value="id">ID</option>
                    <option value="numero">Número</option>
                    <option value="cliente">Cliente</option>
                    <option value="venta">Venta</option>
                    <option value="fecha">Fecha</option>
                    <option value="estado">Estado</option>
                </select>
            </div>
            <!-- Filtro por estado -->
            <div class="col-md-2 text-center">
                <label>Estado</label>
                <select id="estadoFilter" class="form-control">
                    <option value="">Todos</option>
                    <option value="emitida">Emitida</option>
                    <option value="pagada">Pagada</option>
                    <option value="anulada">Anulada</option>
                </select>
            </div>

            <!-- Rango de Fechas -->
            <div class="col-md-2 text-center">
                <label>Desde</label>
                <input type="date" id="dateFrom" class="form-control">
            </div>

            <div class="col-md-2 text-center">
                <label>Hasta</label>
                <input type="date" id="dateTo" class="form-control">
            </div>

        
            <!-- Limpiar -->
            <div class="col-md-2 d-flex align-items-end">
                <button id="clearFilters" class="btn btn-secondary w-100">Limpiar Filtros</button>
            </div>
        </div>
    </div>

    <!-- Contador -->
    <div class="mt-3" style="min-height: 30px;">
        <small id="resultsCount" class="text-muted"></small>
    </div>
    <table class="table table-bordered crm-cardvar table-fixed">
        <thead>
            <tr>
                <th>ID</th>
                <th>N° Factura
                    <button type="button" id="sortNumero" style="border: none; background: none; padding: 0; cursor: pointer;">
                        🔽
                    </button>
                </th>
                <th>Cliente
                    <button type="button" id="sortCliente" style="border: none; background: none; padding: 0; cursor: pointer;">
                        🔽
                    </button>
                </th>
                <th>Venta</th>
                <th>Fecha
                    <button type="button" id="sortFecha" style="border: none; background: none; padding: 0; cursor: pointer;">
                        🔽
                    </button>
                </th>
                <th>Estado</th>
                <th class="text-center">Acciones</th>
            </tr>
        </thead>

        <tbody>
            @foreach($facturas as $factura)
                <tr>
                    <td>{{ $factura->id }}</td>
                    <td>{{ $factura->numero_factura }}</td>
                    <td>{{ $factura->cliente->nombre ?? '—' }}
                        @if($factura->cliente->trashed())
                            <span class="badge bg-danger text-white">Eliminado</span>
                        @endif
                    </td>
                    <td>
                        Venta #{{ $factura->venta->id ?? '—' }}
                        @if(isset($factura->venta) && $factura->venta)
                            — ${{ number_format($factura->venta->monto, 0, ',', '.') }}
                        @endif
                        @if($factura->venta->trashed())
                            <span class="badge bg-danger text-white">Eliminada</span>
                        @endif
                    </td>
                    <td>{{ $factura->fecha_emision }}</td>
                    <td>
                        {{ ucfirst($factura->estado) }}
                        @php
                        $fechaFactura = \Carbon\Carbon::parse($factura->fecha_emision);
                        $haceUnMes = \Carbon\Carbon::now()->subMonth();
                    @endphp
                    @if($fechaFactura <= $haceUnMes && $factura->estado == 'emitida')
                        <span class="badge bg-warning text-white">Atrasada</span>
                    @endif
                    </td>
                    <td class="text-center">
                        <a href="{{ route('facturas.edit', $factura->id) }}" class="btn btn-secondary btn-sm">Editar</a>
                        @if(auth()->user()->role === 'admin')
                        <form action="{{ route('facturas.destroy', $factura->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar factura?')">Eliminar</button>
                        </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>

    </table>
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

    const clearBtn = document.getElementById("clearFilters");
    const resultsCount = document.getElementById("resultsCount");

    const rows = Array.from(document.querySelectorAll("table tbody tr"));
    const estadoFilter = document.getElementById("estadoFilter");


    function escapeRegExp(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    function highlight(text, search) {
        if (!search) return text;
        const escaped = escapeRegExp(search);
        const regex = new RegExp(`(${escaped})`, "gi");
        return text.replace(regex, "<mark>$1</mark>");
    }

    // Cache original HTML
    const cache = rows.map(row => {
        const cols = row.querySelectorAll("td");
        return {
            row,
            original: {
                id: cols[0].innerHTML,
                numero: cols[1].innerHTML,
                cliente: cols[2].innerHTML,
                venta: cols[3].innerHTML,
                fecha: cols[4].innerHTML,
                estado: cols[5].innerHTML,
            }
        };
    });

    function filtrar() {
        const texto = searchInput.value.toLowerCase().trim();
        const filtro = filterType.value;

        const fDesde = dateFrom.value ? new Date(dateFrom.value) : null;
        const fHasta = dateTo.value ? new Date(dateTo.value) : null;
        
        const estadoSeleccionado = estadoFilter.value;





        let visibles = 0;

        cache.forEach(item => {
            const row = item.row;
            const cols = row.querySelectorAll("td");

            const idText = cols[0].textContent.trim();
            const numeroText = cols[1].textContent.trim();
            const clienteText = cols[2].textContent.toLowerCase().trim();
            
            const ventaText = cols[3].textContent.trim();
            const montoVenta = parseInt(ventaText.replace(/\D/g, ""), 10) || 0;

            const fechaText = cols[4].textContent.trim();
            const estadoText = cols[5].textContent.toLowerCase();

            let mostrar = true;

            // FILTRO DE TEXTO
            if (texto !== "") {
                if (filtro === "all") {
                    const hay =
                        idText.includes(texto) ||
                        numeroText.toLowerCase().includes(texto) ||
                        clienteText.includes(texto) ||
                        ventaText.toLowerCase().includes(texto) ||
                        fechaText.toLowerCase().includes(texto) ||
                        estadoText.includes(texto);

                    if (!hay) mostrar = false;
                }
                else if (filtro === "id" && !idText.includes(texto)) mostrar = false;
                else if (filtro === "numero" && !numeroText.toLowerCase().includes(texto)) mostrar = false;
                else if (filtro === "cliente" && !clienteText.includes(texto)) mostrar = false;
                else if (filtro === "venta" && !ventaText.toLowerCase().includes(texto)) mostrar = false;
                else if (filtro === "fecha" && !fechaText.toLowerCase().includes(texto)) mostrar = false;
                else if (filtro === "estado" && !estadoText.includes(texto)) mostrar = false;
            }

            // FILTRO DE FECHA
            if (mostrar && (fDesde || fHasta)) {
                const fecha = new Date(fechaText);
                if (fDesde && fecha < fDesde) mostrar = false;
                if (fHasta && fecha > fHasta) mostrar = false;
            }

            // FILTRO POR ESTADO (select)
            if (mostrar && estadoSeleccionado !== "") {
                if (estadoText !== estadoSeleccionado) {
                    mostrar = false;
                }
            }

            row.style.display = mostrar ? "" : "none";

            // Highlight
            if (mostrar) {
                visibles++;

                if (texto !== "") {
                    cols[0].innerHTML = highlight(item.original.id, texto);
                    cols[1].innerHTML = highlight(item.original.numero, texto);
                    cols[2].innerHTML = highlight(item.original.cliente, texto);
                    cols[3].innerHTML = highlight(item.original.venta, texto);
                    cols[4].innerHTML = highlight(item.original.fecha, texto);
                    cols[5].innerHTML = highlight(item.original.estado, texto);
                } else {
                    Object.keys(item.original).forEach((key, index) => {
                        cols[index].innerHTML = item.original[key];
                    });
                }
            } else {
                Object.keys(item.original).forEach((key, index) => {
                    cols[index].innerHTML = item.original[key];
                });
            }
        });

        resultsCount.innerText =
            (texto || dateFrom.value || dateTo.value)
                ? `Resultados: ${visibles}`
                : "";
    }

    searchInput.addEventListener("input", filtrar);
    filterType.addEventListener("change", filtrar);
    dateFrom.addEventListener("change", filtrar);
    dateTo.addEventListener("change", filtrar);
    estadoFilter.addEventListener("change", filtrar);



    clearBtn.addEventListener("click", () => {
        searchInput.value = "";
        filterType.value = "all";
        dateFrom.value = "";
        dateTo.value = "";
        estadoFilter.value = "";


        filtrar();
        searchInput.focus();
    });

    filtrar();
});

document.addEventListener('DOMContentLoaded', () => {
    const tbody = document.querySelector('table tbody');

    // Variables de orden
    let numeroAsc = true;
    let clienteAsc = true;
    let fechaAsc = true;

  

    const sortNumero = document.getElementById('sortNumero');
    const sortCliente = document.getElementById('sortCliente');
    const sortFecha = document.getElementById('sortFecha');

    // Función para ordenar número de factura
    sortNumero.addEventListener('click', () => {
        const rows = Array.from(tbody.querySelectorAll('tr'));
        rows.sort((a, b) => {
            const numA = parseInt(a.children[1].textContent.trim());
            const numB = parseInt(b.children[1].textContent.trim());
            return numeroAsc ? numA - numB : numB - numA;
        });
        tbody.innerHTML = '';
        rows.forEach(row => tbody.appendChild(row));
        numeroAsc = !numeroAsc;
        sortNumero.textContent = numeroAsc ? '🔽' : '🔼';
    });

    // Función para ordenar por nombre de cliente
    sortCliente.addEventListener('click', () => {
        const rows = Array.from(tbody.querySelectorAll('tr'));
        rows.sort((a, b) => {
            const clienteA = a.children[2].textContent.trim().toLowerCase();
            const clienteB = b.children[2].textContent.trim().toLowerCase();
            if (clienteA < clienteB) return clienteAsc ? -1 : 1;
            if (clienteA > clienteB) return clienteAsc ? 1 : -1;
            return 0;
        });
        tbody.innerHTML = '';
        rows.forEach(row => tbody.appendChild(row));
        clienteAsc = !clienteAsc;
        sortCliente.textContent = clienteAsc ? '🔽' : '🔼';
    });

    // Función para ordenar por fecha
    sortFecha.addEventListener('click', () => {
        const rows = Array.from(tbody.querySelectorAll('tr'));
        rows.sort((a, b) => {
            const fechaA = new Date(a.children[4].textContent.trim());
            const fechaB = new Date(b.children[4].textContent.trim());
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
