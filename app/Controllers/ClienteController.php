<?php

namespace App\Controllers;

use App\Models\PersonaModel;
use App\Models\ClienteModel;

class ClienteController extends BaseController
{
    public function __construct()
    {
        helper(['form', 'url']);
    }

    // 1. Mostrar la vista de alta de cliente
    public function muestra_vista_alta_cliente()
    {
        $data['title'] = 'Alta de Cliente';
        return view('plantillas/head', $data)
            . view('contenido/crud_cliente/alta_cliente', $data)
            . view('plantillas/footer');
    }

    // 2. Verificar datos del formulario
    private function verificar_datos()
    {
        return $this->validate([
            // persona
            'dni'             => 'required|numeric|min_length[7]|max_length[20]|is_unique[persona.dni]',
            'nombre'          => 'required|regex_match[/^[\p{L}\s]+$/u]|min_length[3]|max_length[50]',
            'apellido'        => 'required|regex_match[/^[\p{L}\s]+$/u]|min_length[3]|max_length[50]',
            'fecha_nacimiento'=> 'required|valid_date[Y-m-d]',
            'telefono'        => 'permit_empty|max_length[20]',
            'calle'           => 'required|min_length[3]|max_length[50]',
            'altura'          => 'required|max_length[10]',
            // cliente
            'email'           => 'required|valid_email|max_length[100]|is_unique[cliente.email]',
            'estado_cliente'  => 'required|in_list[activo,inactivo]'
        ]);
    }

    // 3. Alta de cliente (inserta persona y cliente)
    public function alta_cliente()
    {
        if (!$this->verificar_datos()) {
            return view('plantillas/head')
                . view('contenido/crud_cliente/alta_cliente', [
                    'validation' => $this->validator
                ])
                . view('plantillas/footer');
        }

        $personaModel = new PersonaModel();
        $clienteModel = new ClienteModel();

        try {
            // Insertar persona
            $personaId = $personaModel->insert([
                'dni'             => $this->request->getVar('dni'),
                'nombre'          => $this->request->getVar('nombre'),
                'apellido'        => $this->request->getVar('apellido'),
                'fecha_nacimiento'=> $this->request->getVar('fecha_nacimiento'),
                'telefono'        => $this->request->getVar('telefono'),
                'calle'           => $this->request->getVar('calle'),
                'altura'          => $this->request->getVar('altura')
            ]);

            // Insertar cliente vinculado a persona
            $clienteModel->insert([
                'email'          => $this->request->getVar('email'),
                'fecha_alta'     => date('Y-m-d'),
                'estado_cliente' => $this->request->getVar('estado_cliente'),
                'id_persona'     => $personaId
            ]);

            session()->setFlashdata('success', 'Cliente registrado correctamente');
            return redirect()->to('/cliente/alta');

        } catch (\Exception $e) {
            session()->setFlashdata('error', 'Error al registrar: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    // 4. Listar clientes
    public function listar_clientes()
    {
        $clienteModel = new ClienteModel();
        $data['clientes'] = $clienteModel->findAll();
        $data['title'] = 'Listado de Clientes';

        return view('plantillas/head', $data)
            . view('contenido/crud_cliente/listar_clientes', $data)
            . view('plantillas/footer');
    }
}
