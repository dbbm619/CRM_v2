<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use App\Rules\ValidRut;
use Illuminate\Validation\Rule;


class ClienteController extends Controller
{
    // 🟢 Mostrar todos los clientes
    public function index()
    {
        $clientes = Cliente::all();
        return view('clientes.index', compact('clientes'));
    }

    // 🟢 Mostrar formulario para crear nuevo cliente
    public function create()
    {
        return view('clientes.create');
    }

    // 🟢 Guardar un nuevo cliente en la base de datos
    public function store(Request $request)
    {   
        $clienteEliminado = Cliente::withTrashed()
            ->where('rut', $request->rut)
            ->first();

        if ($clienteEliminado && $clienteEliminado->trashed()) {
            return redirect()->back()->withErrors([
                'rut' => 'Este RUT pertenece a un cliente eliminado. Puede restaurarlo.'
            ]);
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'rut' => [
                'required',
                'string',
                'max:12',
                'unique:clientes,rut,NULL,id,deleted_at,NULL',
                new ValidRut()
            ],
            'correo' => [
                'nullable',
                'email',
                'regex:/^[^@\s]+@[^@\s]+\.[^@\s]+$/'
            ],
            'telefono' => [
                'nullable',
                'regex:/^\+?[1-9]\d{6,14}$/'
            ],
            'rubro' => 'nullable|string|max:100',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'rut.required' => 'El RUT es obligatorio.',
            'rut.unique' => 'Este RUT ya está registrado.',
            'correo.email' => 'El correo debe ser válido.',
            'correo.regex' => 'El correo debe ser válido.',
            'telefono.regex' => 'Ingrese un teléfono válido, incluyendo código de país si corresponde.',
        ]);

        Cliente::create($request->all());

        return redirect()->route('clientes.index')->with('success', 'Cliente creado correctamente.');
    }

    // 🟢 Mostrar formulario de edición
    public function edit(Cliente $cliente)
    {
        return view('clientes.edit', compact('cliente'));
    }

    // 🟢 Actualizar datos de un cliente
    public function update(Request $request, Cliente $cliente)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'rut' => [
                'required',
                'string',
                'max:12',
                new ValidRut(),
                Rule::unique('clientes', 'rut')
                    ->ignore($cliente->id)
                    ->whereNull('deleted_at'),
            ]   ,
            'correo' => 'nullable|email',
            'telefono' => 'nullable|string|max:15',
            'rubro' => 'nullable|string|max:100',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'rut.required' => 'El RUT es obligatorio.',
            'rut.unique' => 'Este RUT ya está registrado.',
            'correo.email' => 'El correo debe ser válido.',
        ]);


        $cliente->update($request->all());

        return redirect()->route('clientes.index')->with('success', 'Cliente actualizado correctamente.');
    }

    // 🟢 Eliminar un cliente
    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return redirect()->route('clientes.index')->with('success', 'Cliente eliminado correctamente.');
    }
    public function restore($id)
    {
        $cliente = Cliente::onlyTrashed()->findOrFail($id);
        $cliente->restore();

        return redirect()->back()->with('success', 'Cliente restaurado correctamente.');
    }

    public function eliminados()
    {
        $clientes = Cliente::onlyTrashed()->get();
        return view('clientes.eliminados', compact('clientes'));
    }
}
