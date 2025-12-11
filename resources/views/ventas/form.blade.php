<div class="mb-3 text-center">
    <label>Cliente</label>
    <select name="cliente_id" 
            class="form-control @error('cliente_id') is-invalid @enderror">
        <option value="">Seleccione un cliente</option>

        @foreach ($clientes as $cliente)
            <option value="{{ $cliente->id }}"
                {{ old('cliente_id', $venta->cliente_id ?? '') == $cliente->id ? 'selected' : '' }}>
                {{ $cliente->nombre }}
            </option>
        @endforeach
    </select>

    @error('cliente_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3 text-center">
    <label>Monto</label>

    <input type="text" name="monto" 
           class="form-control @error('monto') is-invalid @enderror"
           value="{{ old('monto', isset($venta->monto) ? number_format($venta->monto, 0, ',', '.') : '') }}"
           placeholder="Ingrese Monto de Venta"
           oninput="this.value = formatMiles(this.value)">

    @error('monto')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<script>
function formatMiles(value) {
    // Quita todo lo que no sea número
    value = value.replace(/\D/g, "");

    // Si está vacío, lo retorna vacío
    if (value === "") return "";

    // Aplica puntos cada 3 dígitos
    return value.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}
</script>


<div class="mb-3 text-center">
    <label>Fecha</label>
    <input type="date" name="fecha" 
           class="form-control @error('fecha') is-invalid @enderror"
           value="{{ old('fecha', $venta->fecha ?? '') }}">

    @error('fecha')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3 text-center">
    <label>Estado</label>
    <select name="estado" 
            class="form-control @error('estado') is-invalid @enderror">
        <option value="pendiente" {{ old('estado', $venta->estado ?? '') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
        <option value="pagada" {{ old('estado', $venta->estado ?? '') == 'pagada' ? 'selected' : '' }}>Pagada</option>
        <option value="cancelada" {{ old('estado', $venta->estado ?? '') == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
    </select>

    @error('estado')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
