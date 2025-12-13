<div class="mb-3 text-center">
    <label>Nombre:</label>
    <input type="text" name="nombre" 
           class="form-control @error('nombre') is-invalid @enderror"
           value="{{ old('nombre', $cliente->nombre ?? '') }}" 
           placeholder="Nombre" required>

    @error('nombre')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3 text-center">
    <label>RUT</label>
    <input type="text" name="rut" id="rut" class="form-control" maxlength="12" 
           value="{{ old('rut', $cliente->rut ?? '') }}"
           placeholder="Rut">
</div>

<script>
function formatRut(rut) {
    rut = rut.replace(/[^\dkK]/g, '').replace(/^0+/, '');
    if (rut.length <= 1) return rut;
    let body = rut.slice(0, -1);
    let dv = rut.slice(-1);
    body = body.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    return body + '-' + dv;
}

document.getElementById('rut').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\./g, '').replace(/-/g, '');
    e.target.value = formatRut(value);
});
</script>

<div class="mb-3 text-center">
    <label>Correo :</label>
    <input type="email" name="correo"
           class="form-control @error('correo') is-invalid @enderror"
           value="{{ old('correo', $cliente->correo ?? '') }}"
           placeholder="Correo Electrónico">

    @error('correo')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3 text-center">
    <label>Teléfono</label>
    <input type="text" name="telefono" id="telefono" class="form-control"
           value="{{ old('telefono', $cliente->telefono ?? '') }}"
           placeholder="+569 XXXXXXXX" maxlength="13">
</div>

<script>
const telefonoInput = document.getElementById('telefono');

telefonoInput.addEventListener('input', function(e) {
    let value = e.target.value;

    // Forzar que siempre comience con '+569 '
    if (!value.startsWith('+569 ')) {
        value = '+569 ' + value.replace(/^\+569\s?/, '');
    }

    // Mantener solo números después del espacio y máximo 8 dígitos
    let numeros = value.slice(5).replace(/\D/g, '').slice(0, 8);

    // Actualizar el input
    e.target.value = '+569 ' + numeros;
});
</script>



<div class="mb-3 text-center">
    <label>Rubro</label>
    <select name="rubro" class="form-control">
        <option value="">Seleccione un rubro</option>
        <option value="Comercio" {{ old('rubro', $cliente->rubro ?? '') == 'Comercio' ? 'selected' : '' }}>Comercio</option>
        <option value="Servicios" {{ old('rubro', $cliente->rubro ?? '') == 'Servicios' ? 'selected' : '' }}>Servicios</option>
        <option value="Industria" {{ old('rubro', $cliente->rubro ?? '') == 'Industria' ? 'selected' : '' }}>Industria</option>
        <option value="Agricultura" {{ old('rubro', $cliente->rubro ?? '') == 'Agricultura' ? 'selected' : '' }}>Agricultura</option>
        <option value="Construcción" {{ old('rubro', $cliente->rubro ?? '') == 'Construcción' ? 'selected' : '' }}>Construcción</option>
        <!-- Agrega más rubros según lo que necesites -->
    </select>
</div>
