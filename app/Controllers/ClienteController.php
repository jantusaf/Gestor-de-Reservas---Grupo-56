<?php

namespace App\Controllers;

use App\Models\ClienteModel;

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

        $resultado = $clienteModel->registrarCliente(
            $this->request->getPost('dni')              ?? '',
            $this->request->getPost('nombre')           ?? '',
            $this->request->getPost('apellido')         ?? '',
            $this->request->getPost('fecha_nacimiento') ?? '',
            $this->request->getPost('telefono')         ?? '',
            $this->request->getPost('calle')            ?? '',
            $this->request->getPost('altura')           ?? '',
            $this->request->getPost('email')            ?? '',
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
        $data         = $clienteModel->datosFormularioEditar((int) $id);

        if (!$data) {
            return redirect()->to('/cliente/listar')->with('error', 'Cliente no encontrado.');
        }

        return view('plantillas/head', ['title' => 'Editar Cliente'])
            . view('contenido/crud_cliente/editar_cliente', $data)
            . view('plantillas/footer');
    }

    public function actualizarCliente($id)
    {
        $clienteModel = new ClienteModel();

        $resultado = $clienteModel->modificarCliente(
            (int) $id,
            $this->request->getPost('dni')              ?? '',
            $this->request->getPost('nombre')           ?? '',
            $this->request->getPost('apellido')         ?? '',
            $this->request->getPost('fecha_nacimiento') ?? '',
            $this->request->getPost('telefono')         ?? '',
            $this->request->getPost('calle')            ?? '',
            $this->request->getPost('altura')           ?? '',
            $this->request->getPost('email')            ?? '',
            $this->request->getPost('estado_cliente')   ?? '',
        );

        if (!$resultado['ok']) {
            return redirect()->to('/cliente/editar/' . $id)->with('errors', $resultado['errores']);
        }

        return redirect()->to('/cliente/listar')->with('success', 'Cliente actualizado correctamente.');
    }

    public function deshabilitarCliente($id)
    {
        $clienteModel = new ClienteModel();
        $resultado    = $clienteModel->deshabilitar((int) $id);

        if (!$resultado['ok']) {
            return redirect()->to('/cliente/listar')->with('error', $resultado['mensaje']);
        }

        return redirect()->to('/cliente/listar')->with('success', $resultado['mensaje']);
    }

    public function habilitarCliente($id)
    {
        $clienteModel = new ClienteModel();
        $resultado    = $clienteModel->habilitar((int) $id);

        if (!$resultado['ok']) {
            return redirect()->to('/cliente/listar')->with('error', $resultado['mensaje']);
        }

        return redirect()->to('/cliente/listar')->with('success', $resultado['mensaje']);
    }
}
