@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex gap-3 justify-content-center align-items-center">
        <h1 class="crm-page-title">Clientes</h1>
        <a href="{{ route('clientes.create') }}" class="btn btn-crm">Agregar</a>
    </div>
    

    <div class="principal">


    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="mb-4 p-3 border rounded crm-filter">
        <div class="row">
            <div class="col-md-6 text-center">
                <label>Buscar Cliente</label>
                <div class="input-group">
                    <input 
                        type="text" 
                        id="searchInput" 
                        class="form-control" 
                        placeholder="Mostrando Todos..."
                    >
                </div>
            </div>

            <div class="col-md-3 text-center">
                <label>Buscar en</label>
                <select id="filterType" class="form-control">
                    <option value="all" selected>Todos</option>
                    <option value="nombre">Nombre</option>
                    <option value="rut">RUT</option>
                    <option value="correo">Correo</option>
                    <option value="telefono">Teléfono</option>
                    <option value="rubro">Rubro</option>
                </select>
            </div>
            <div class="col-md-3">
                <label></label>
                <button class="block btn btn-secondary w-100" id="clearSearch">Limpiar Filtros</button>
            </div>
        </div>
        
    </div>
    <div class="mt-2" style="min-height: 30px;">
        <small id="resultsCount" class="text-muted"></small>
    </div>
    <table class="table table-bordered crm-cardvar table-fixed">
        <thead>
            <tr>
                <th>
                    Nombre
                    <button type="button" id="sortNombre" style="border: none; background: none; padding: 0; cursor: pointer;">
                        🔽
                    </button>
                </th>
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
                    <td class="text-center d-flex justify-content-around">
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
    <br>
    <br>

</div>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("searchInput");
    const filterSelect = document.getElementById("filterType");
    const clearBtn = document.getElementById("clearSearch");
    const resultsCount = document.getElementById("resultsCount");

    const rows = document.querySelectorAll("table tbody tr");

    function highlight(text, search) {
        if (!search) return text;
        const regex = new RegExp(`(${search})`, "gi");
        return text.replace(regex, "<mark>$1</mark>");
    }

    function filtrar() {
        const texto = searchInput.value.toLowerCase().trim();
        const filtro = filterSelect.value;

        let visibles = 0;

        rows.forEach(row => {
            const columnas = row.querySelectorAll("td");

            const data = {
                nombre: columnas[0].innerText.toLowerCase(),
                rut: columnas[1].innerText.toLowerCase(),
                correo: columnas[2].innerText.toLowerCase(),
                telefono: columnas[3].innerText.toLowerCase(),
                rubro: columnas[4].innerText.toLowerCase(),
            };

            const originalHTML = {
                nombre: columnas[0].innerText,
                rut: columnas[1].innerText,
                correo: columnas[2].innerText,
                telefono: columnas[3].innerText,
                rubro: columnas[4].innerText,
            };

            const coincideEnColumnas = Object.values(data).some(valor =>
                valor.includes(texto)
            );

            const coincideEspecifico = filtro !== "all" && data[filtro].includes(texto);

            let mostrar;
            if (texto === "") {
                mostrar = true;
            } else if (filtro === "all" && coincideEnColumnas) {
                mostrar = true;
            } else if (filtro !== "all" && coincideEspecifico) {
                mostrar = true;
            } else {
                mostrar = false;
            }

            // Mostrar u ocultar fila
            row.style.display = mostrar ? "" : "none";

            // Si la fila queda visible, resaltar coincidencias
            if (mostrar && texto !== "") {
                columnas[0].innerHTML = highlight(originalHTML.nombre, texto);
                columnas[1].innerHTML = highlight(originalHTML.rut, texto);
                columnas[2].innerHTML = highlight(originalHTML.correo, texto);
                columnas[3].innerHTML = highlight(originalHTML.telefono, texto);
                columnas[4].innerHTML = highlight(originalHTML.rubro, texto);
                visibles++;
            } else {
                columnas[0].innerHTML = originalHTML.nombre;
                columnas[1].innerHTML = originalHTML.rut;
                columnas[2].innerHTML = originalHTML.correo;
                columnas[3].innerHTML = originalHTML.telefono;
                columnas[4].innerHTML = originalHTML.rubro;
            }
        });

        // Actualizar contador
        if (texto === "") {
            resultsCount.innerText = "";
        } else {
            resultsCount.innerText = `Resultados: ${visibles}`;
        }
    }

    // Eventos
    searchInput.addEventListener("keyup", filtrar);
    filterSelect.addEventListener("change", filtrar);

    // Botón borrar búsqueda
    clearBtn.addEventListener("click", () => {
        searchInput.value = "";
        filterSelect.value = "all";
        filtrar();
        searchInput.focus();
    });
});
document.addEventListener('DOMContentLoaded', () => {
    const sortBtn = document.getElementById('sortNombre');
    const tbody = document.querySelector('table tbody');
    let asc = true; // control del orden

    sortBtn.addEventListener('click', () => {
        const rows = Array.from(tbody.querySelectorAll('tr'));

        rows.sort((a, b) => {
            const nameA = a.querySelector('td').textContent.trim().toLowerCase();
            const nameB = b.querySelector('td').textContent.trim().toLowerCase();
            return asc ? nameA.localeCompare(nameB) : nameB.localeCompare(nameA);
        });

        tbody.innerHTML = '';
        rows.forEach(row => tbody.appendChild(row));

        // Alterna la dirección y cambia la flecha
        asc = !asc;
        sortBtn.textContent = asc ? '🔽' : '🔼';
    });
});
</script>
@endsection
