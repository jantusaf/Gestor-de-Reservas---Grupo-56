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

    private function validarCliente(int $idPersona = 0, int $idCliente = 0): bool
    {
        $dniRule   = $idPersona ? "required|numeric|min_length[7]|max_length[20]|is_unique[persona.dni,id_persona,{$idPersona}]"
                                : 'required|numeric|min_length[7]|max_length[20]|is_unique[persona.dni]';
        $emailRule = $idCliente ? "required|valid_email|max_length[100]|is_unique[cliente.email,id_cliente,{$idCliente}]"
                                : 'required|valid_email|max_length[100]|is_unique[cliente.email]';

        return $this->validate([
            'dni'              => $dniRule,
            'nombre'           => 'required|regex_match[/^[\p{L}\s]+$/u]|min_length[3]|max_length[50]',
            'apellido'         => 'required|regex_match[/^[\p{L}\s]+$/u]|min_length[3]|max_length[50]',
            'fecha_nacimiento' => 'required|valid_date[Y-m-d]|check_past_date',
            'telefono'         => 'permit_empty|max_length[20]',
            'calle'            => 'required|min_length[3]|max_length[50]',
            'altura'           => 'required|max_length[10]',
            'email'            => $emailRule,
        ]);
    }

    public function altaCliente()
    {
        if ($this->request->getMethod() === 'post') {
            if (!$this->validarCliente()) {
                return view('plantillas/head', ['title' => 'Alta de Cliente'])
                    . view('contenido/crud_cliente/alta_cliente', ['validation' => $this->validator])
                    . view('plantillas/footer');
            }

            $personaModel = new PersonaModel();
            $clienteModel = new ClienteModel();

            try {
                $personaId = $personaModel->insert([
                    'dni'              => $this->request->getVar('dni'),
                    'nombre'           => $this->request->getVar('nombre'),
                    'apellido'         => $this->request->getVar('apellido'),
                    'fecha_nacimiento' => $this->request->getVar('fecha_nacimiento'),
                    'telefono'         => $this->request->getVar('telefono'),
                    'calle'            => $this->request->getVar('calle'),
                    'altura'           => $this->request->getVar('altura'),
                ]);

                $clienteModel->insert([
                    'email'          => $this->request->getVar('email'),
                    'fecha_alta'     => date('Y-m-d'),
                    'estado_cliente' => 'activo',
                    'id_persona'     => $personaId,
                ]);

                return redirect()->to('/cliente/listar')->with('success', 'Cliente registrado correctamente.');

            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Error al registrar: ' . $e->getMessage())->withInput();
            }
        }

        return view('plantillas/head', ['title' => 'Alta de Cliente'])
            . view('contenido/crud_cliente/alta_cliente')
            . view('plantillas/footer');
    }

    public function listarClientes()
    {
        $db  = \Config\Database::connect();
        $dni = trim($this->request->getGet('dni') ?? '');

        $builder = $db->table('cliente')
            ->select('cliente.id_cliente, cliente.email, cliente.fecha_alta, cliente.estado_cliente, cliente.id_persona,
                      persona.nombre, persona.apellido, persona.dni, persona.telefono, persona.calle, persona.altura, persona.fecha_nacimiento')
            ->join('persona', 'persona.id_persona = cliente.id_persona')
            ->orderBy('cliente.estado_cliente', 'ASC')
            ->orderBy('persona.apellido', 'ASC');

        if ($dni !== '') {
            $builder->like('persona.dni', $dni, 'after');
        }

        $data['clientes']      = $builder->get()->getResultArray();
        $data['dni_busqueda']  = $dni;
        $data['title']         = 'Listado de Clientes';

        return view('plantillas/head', $data)
            . view('contenido/crud_cliente/listar_clientes', $data)
            . view('plantillas/footer');
    }

    public function editarCliente($id)
    {
        $clienteModel = new ClienteModel();
        $personaModel = new PersonaModel();

        $cliente = $clienteModel->find($id);
        if (!$cliente) {
            return redirect()->to('/cliente/listar')->with('error', 'Cliente no encontrado.');
        }

        return view('plantillas/head', ['title' => 'Editar Cliente'])
            . view('contenido/crud_cliente/editar_cliente', [
                'cliente' => $cliente,
                'persona' => $personaModel->find($cliente['id_persona']),
            ])
            . view('plantillas/footer');
    }

    public function actualizarCliente($id)
    {
        $clienteModel = new ClienteModel();
        $cliente = $clienteModel->find($id);

        if (!$cliente) {
            return redirect()->to('/cliente/listar')->with('error', 'Cliente no encontrado.');
        }

        $idPersona = $cliente['id_persona'];

        if (!$this->validarCliente($idPersona, $id)) {
            $personaModel = new PersonaModel();
            return view('plantillas/head', ['title' => 'Editar Cliente'])
                . view('contenido/crud_cliente/editar_cliente', [
                    'cliente'    => $cliente,
                    'persona'    => $personaModel->find($idPersona),
                    'validation' => $this->validator,
                ])
                . view('plantillas/footer');
        }

        $personaModel = new PersonaModel();
        $personaModel->update($idPersona, [
            'dni'              => $this->request->getVar('dni'),
            'nombre'           => $this->request->getVar('nombre'),
            'apellido'         => $this->request->getVar('apellido'),
            'fecha_nacimiento' => $this->request->getVar('fecha_nacimiento'),
            'telefono'         => $this->request->getVar('telefono'),
            'calle'            => $this->request->getVar('calle'),
            'altura'           => $this->request->getVar('altura'),
        ]);

        $clienteModel->update($id, [
            'email'          => $this->request->getVar('email'),
            'estado_cliente' => $this->request->getVar('estado_cliente'),
        ]);

        return redirect()->to('/cliente/listar')->with('success', 'Cliente actualizado correctamente.');
    }

    public function deshabilitarCliente($id)
    {
        $clienteModel = new ClienteModel();
        if (!$clienteModel->find($id)) {
            return redirect()->to('/cliente/listar')->with('error', 'Cliente no encontrado.');
        }
        $clienteModel->update($id, ['estado_cliente' => 'inactivo']);
        return redirect()->to('/cliente/listar')->with('success', 'Cliente deshabilitado.');
    }

    public function habilitarCliente($id)
    {
        $clienteModel = new ClienteModel();
        if (!$clienteModel->find($id)) {
            return redirect()->to('/cliente/listar')->with('error', 'Cliente no encontrado.');
        }
        $clienteModel->update($id, ['estado_cliente' => 'activo']);
        return redirect()->to('/cliente/listar')->with('success', 'Cliente habilitado.');
    }
}
