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
        $dniRule = $idPersona ? "required|numeric|min_length[7]|max_length[20]|is_unique[persona.dni,id_persona,{$idPersona}]"
                                : 'required|numeric|min_length[7]|max_length[20]|is_unique[persona.dni]';
        $emailRule = $idCliente ? "required|valid_email|max_length[100]|is_unique[cliente.email,id_cliente,{$idCliente}]"
                                : 'required|valid_email|max_length[100]|is_unique[cliente.email]';

        return $this->validate([
            'dni' => $dniRule,
            'nombre' => 'required|regex_match[/^[\p{L}\s]+$/u]|min_length[3]|max_length[50]',
            'apellido' => 'required|regex_match[/^[\p{L}\s]+$/u]|min_length[3]|max_length[50]',
            'fecha_nacimiento' => 'required|valid_date[Y-m-d]|check_past_date',
            'telefono' => 'permit_empty|max_length[20]',
            'calle' => 'required|min_length[3]|max_length[50]',
            'altura' => 'required|max_length[10]',
            'email'=> $emailRule,
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
                $personaId = $personaModel->altaPersona(
                    $this->request->getVar('dni'),
                    $this->request->getVar('nombre'),
                    $this->request->getVar('apellido'),
                    $this->request->getVar('fecha_nacimiento'),
                    $this->request->getVar('telefono'),
                    $this->request->getVar('calle'),
                    $this->request->getVar('altura'),
                );

                $clienteModel->altaCliente(
                    $this->request->getVar('email'),
                    $personaId,
                );

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
        $clienteModel = new ClienteModel();
        $dni = trim($this->request->getGet('dni') ?? '');

        $data['clientes'] = $clienteModel->listarClientes($dni);
        $data['dni_busqueda'] = $dni;
        $data['title'] = 'Listado de Clientes';

        return view('plantillas/head', $data)
            . view('contenido/crud_cliente/listar_clientes', $data)
            . view('plantillas/footer');
    }

    public function listarClientesActivos()
    {
        $clienteModel = new ClienteModel();

        $data['clientes'] = $clienteModel->listarClientesActivos();
        $data['title'] = 'Clientes Activos';

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
                    'cliente' => $cliente,
                    'persona' => $personaModel->find($idPersona),
                    'validation' => $this->validator,
                ])
                . view('plantillas/footer');
        }

        $personaModel = new PersonaModel();
        $personaModel->actualizarPersona(
            $idPersona,
            $this->request->getVar('dni'),
            $this->request->getVar('nombre'),
            $this->request->getVar('apellido'),
            $this->request->getVar('fecha_nacimiento'),
            $this->request->getVar('telefono'),
            $this->request->getVar('calle'),
            $this->request->getVar('altura'),
        );

        $clienteModel->actualizarCliente(
            $id,
            $this->request->getVar('email'),
            $this->request->getVar('estado_cliente'),
        );

        return redirect()->to('/cliente/listar')->with('success', 'Cliente actualizado correctamente.');
    }

    public function deshabilitarCliente($id)
    {
        $clienteModel = new ClienteModel();
        if (!$clienteModel->find($id)) {
            return redirect()->to('/cliente/listar')->with('error', 'Cliente no encontrado.');
        }
        $clienteModel->deshabilitar($id);
        return redirect()->to('/cliente/listar')->with('success', 'Cliente deshabilitado.');
    }

    public function habilitarCliente($id)
    {
        $clienteModel = new ClienteModel();
        if (!$clienteModel->find($id)) {
            return redirect()->to('/cliente/listar')->with('error', 'Cliente no encontrado.');
        }
        $clienteModel->habilitar($id);
        return redirect()->to('/cliente/listar')->with('success', 'Cliente habilitado.');
    }
}
