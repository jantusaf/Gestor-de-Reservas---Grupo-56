<?php

namespace App\Controllers;

use App\Models\ClienteModel;
use App\Models\PersonaModel;
use App\Entities\Persona;

class ClienteController extends BaseController
{
    public function __construct()
    {
        helper(['form', 'url']);
    }

    public function formularioAlta()
    {
        return view('plantillas/head', ['title' => 'Alta de Cliente'])
            . view('contenido/crud_cliente/alta_cliente')
            . view('plantillas/footer');
    }

    public function guardarCliente()
    {
        $clienteModel = new ClienteModel();

        $persona = new Persona([
            'dni'              => $this->request->getPost('dni')              ?? '',
            'nombre'           => $this->request->getPost('nombre')           ?? '',
            'apellido'         => $this->request->getPost('apellido')         ?? '',
            'fecha_nacimiento' => $this->request->getPost('fecha_nacimiento') ?? '',
            'telefono'         => $this->request->getPost('telefono')         ?? '',
            'calle'            => $this->request->getPost('calle')            ?? '',
            'altura'           => $this->request->getPost('altura')           ?? '',
        ]);

        $resultado = $clienteModel->registrarCliente(
            $persona,
            $this->request->getPost('email') ?? '',
        );

        if (!$resultado['ok']) {
            return redirect()->back()->withInput()->with('errors', $resultado['errores']);
        }

        return redirect()->to('/cliente/listar')->with('success', 'Cliente registrado correctamente.');
    }

    public function listarClientes()
    {
        $clienteModel = new ClienteModel();
        $dni          = trim($this->request->getGet('dni') ?? '');

        return view('plantillas/head', ['title' => 'Listado de Clientes'])
            . view('contenido/crud_cliente/listar_clientes', [
                'clientes'     => $clienteModel->listarClientes($dni),
                'dni_busqueda' => $dni,
            ])
            . view('plantillas/footer');
    }

    public function formularioEditar($id)
    {
        $clienteModel = new ClienteModel();
        $cliente      = $clienteModel->find((int) $id);

        if (!$cliente) {
            return redirect()->to('/cliente/listar')->with('error', 'Cliente no encontrado.');
        }

        return view('plantillas/head', ['title' => 'Editar Cliente'])
            . view('contenido/crud_cliente/editar_cliente', [
                'cliente' => $cliente,
                'persona' => (new PersonaModel())->find($cliente['id_persona']),
            ])
            . view('plantillas/footer');
    }

    public function actualizarCliente($id)
    {
        $clienteModel = new ClienteModel();

        $persona = new Persona([
            'dni'              => $this->request->getPost('dni')              ?? '',
            'nombre'           => $this->request->getPost('nombre')           ?? '',
            'apellido'         => $this->request->getPost('apellido')         ?? '',
            'fecha_nacimiento' => $this->request->getPost('fecha_nacimiento') ?? '',
            'telefono'         => $this->request->getPost('telefono')         ?? '',
            'calle'            => $this->request->getPost('calle')            ?? '',
            'altura'           => $this->request->getPost('altura')           ?? '',
        ]);

        $resultado = $clienteModel->modificarCliente(
            (int) $id,
            $persona,
            $this->request->getPost('email')          ?? '',
            $this->request->getPost('estado_cliente') ?? '',
        );

        if (!$resultado['ok']) {
            return redirect()->to('/cliente/editar/' . $id)->with('errors', $resultado['errores']);
        }

        return redirect()->to('/cliente/listar')->with('success', 'Cliente actualizado correctamente.');
    }

    public function deshabilitarCliente($id)
    {
        $clienteModel = new ClienteModel();

        if (!$clienteModel->deshabilitar((int) $id)) {
            return redirect()->to('/cliente/listar')->with('error', 'Cliente no encontrado.');
        }

        return redirect()->to('/cliente/listar');
    }

    public function habilitarCliente($id)
    {
        $clienteModel = new ClienteModel();

        if (!$clienteModel->habilitar((int) $id)) {
            return redirect()->to('/cliente/listar')->with('error', 'Cliente no encontrado.');
        }

        return redirect()->to('/cliente/listar');
    }
}
