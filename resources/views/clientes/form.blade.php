<div class="mb-3 text-center">
    <label>Nombre:</label>
    <input type="text" name="nombre" 
           class="form-control @error('nombre') is-invalid @enderror"
           value="{{ old('nombre', $cliente->nombre ?? '') }}" required>

    @error('nombre')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3 text-center">
    <label>RUT:</label>
    <input type="text" name="rut" id="rutInput"
           class="form-control @error('rut') is-invalid @enderror"
           value="{{ old('rut', $cliente->rut ?? '') }}" required>

    @error('rut')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3 text-center">
    <label>Correo:</label>
    <input type="email" name="correo"
           class="form-control @error('correo') is-invalid @enderror"
           value="{{ old('correo', $cliente->correo ?? '') }}">

    @error('correo')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3 text-center">
    <label>Teléfono:</label>
    <input type="text" name="telefono"
           class="form-control @error('telefono') is-invalid @enderror"
           value="{{ old('telefono', $cliente->telefono ?? '') }}">

    @error('telefono')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3 text-center">
    <label>Rubro:</label>
    <input type="text" name="rubro"
           class="form-control @error('rubro') is-invalid @enderror"
           value="{{ old('rubro', $cliente->rubro ?? '') }}">

    @error('rubro')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
<script>
document.getElementById("rutInput").addEventListener("input", function () {
    let v = this.value.replace(/[^\dkK]/g, "").toUpperCase();

    if (v.length > 1) {
        let cuerpo = v.slice(0, -1);
        let dv = v.slice(-1);

        // Formatear cada 3 dígitos
        cuerpo = cuerpo.replace(/\B(?=(\d{3})+(?!\d))/g, ".");

        this.value = cuerpo + "-" + dv;
    } else {
        this.value = v;
    }
});
</script>
