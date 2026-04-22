<?php

namespace App\Controllers;

use App\Models\ClienteModel;

class ClienteController extends BaseController
{
    public function __construct()
    {
        helper(['form', 'url']);
    }

    // Muestra el formulario de alta de cliente
    public function alta()
    {
        $data['title'] = 'Alta de Cliente';
        return view('plantillas/head', $data)
            . view('contenido/crud_cliente/alta_cliente', $data) // ruta corregida
            . view('plantillas/footer');
    }

// Procesa el formulario y guarda el cliente
public function guardar()
{
    $clienteModel = new ClienteModel();

    // Validación SIN el campo estado
    $validation = $this->validate([
        'dni'      => 'required|min_length[7]|max_length[20]|is_unique[Cliente.dni_cliente]',
        'nombre'   => 'required|min_length[3]|max_length[50]',
        'apellido' => 'required|min_length[3]|max_length[50]',
        'telefono' => 'permit_empty|max_length[20]'
    ]);

    if (!$validation) {
        return view('plantillas/head')
            . view('contenido/crud_cliente/alta_cliente', [
                'validation' => $this->validator
            ]) // ruta corregida
            . view('plantillas/footer');
    }

    try {
        // Insertar cliente con estado fijo en 'activo'
        $clienteModel->insert([
            'dni_cliente'      => $this->request->getVar('dni'),
            'nombre_cliente'   => $this->request->getVar('nombre'),
            'apellido_cliente' => $this->request->getVar('apellido'),
            'telefono_cliente' => $this->request->getVar('telefono'),
            'estado_cliente'   => 'activo' // se asigna automáticamente
        ]);

        // Mensaje de éxito y redirección al formulario de alta
        session()->setFlashdata('success', 'Cliente registrado correctamente');
        return redirect()->to('/cliente/alta');

    } catch (\Exception $e) {
        session()->setFlashdata('error', 'Error al registrar: ' . $e->getMessage());
        return redirect()->back()->withInput();
    }
}



    // Listar clientes
    public function listar()
    {
        $clienteModel = new ClienteModel();
        $data['clientes'] = $clienteModel->findAll();
        $data['title'] = 'Listado de Clientes';

        return view('plantillas/head', $data)
            . view('contenido/crud_cliente/listar_clientes', $data) // ruta corregida
            . view('plantillas/footer');
    }
}
