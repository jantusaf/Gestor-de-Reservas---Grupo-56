<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use App\Models\PersonaModel;

class UsuarioController extends BaseController
{
    public function __construct()
    {
        helper(['form', 'url']);
    }

    public function formularioRegistro()
    {
        $data['title'] = 'Registrarse';
        return view('plantillas/head', $data)
            . view('contenido/registrarse', $data)
            . view('plantillas/footer');
    }

    public function guardar()
    {
        $usuarioModel = new UsuarioModel();
        $personaModel = new PersonaModel();

        $validation = $this->validate([
            'dni'             => 'required|numeric|min_length[7]|max_length[20]|is_unique[persona.dni]',
            'nombre'          => 'required|regex_match[/^[\p{L}\s]+$/u]|min_length[3]|max_length[50]',
            'apellido'        => 'required|regex_match[/^[\p{L}\s]+$/u]|min_length[3]|max_length[50]',
            'fecha_nacimiento'=> 'required|valid_date[Y-m-d]|check_past_date',
            'telefono'        => 'required|numeric|max_length[20]',
            'calle'           => 'required|min_length[3]|max_length[50]',
            'altura'          => 'required|max_length[10]',
            'usuario'         => 'required|min_length[3]|max_length[50]|is_unique[usuario.nombre_usuario]',
            'password'        => 'required|min_length[6]|max_length[100]'
        ]);

        if (!$validation) {
            return view('plantillas/head')
                . view('contenido/registrarse', ['validation' => $this->validator])
                . view('plantillas/footer');
        }

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

            $usuarioModel->altaUsuario(
                $this->request->getVar('usuario'),
                $this->request->getVar('password'),
                $personaId,
            );

            session()->setFlashdata('success', 'Usuario registrado correctamente');
            return redirect()->to('/login');

        } catch (\Exception $e) {
            session()->setFlashdata('error', 'Error al registrar: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    public function perfil()
    {
        $usuarioModel = new UsuarioModel();
        $id = session()->get('id_usuario');

        $data['usuario'] = $usuarioModel->find($id);
        $data['title']   = 'Mi Perfil';

        return view('plantillas/head', $data)
            . view('contenido/perfil_usuario', $data)
            . view('plantillas/footer');
    }

    public function actualizar()
    {
        $usuarioModel = new UsuarioModel();
        $id = session()->get('id_usuario');

        $usuarioModel->actualizarUsuario(
            $id,
            $this->request->getVar('usuario'),
            $this->request->getVar('estado') ?? 'activo',
        );

        return redirect()->back()->with('success', 'Datos actualizados correctamente');
    }

    public function baja()
    {
        $usuarioModel = new UsuarioModel();
        $id = session()->get('id_usuario');

        $usuarioModel->darDeBaja($id);

        session()->destroy();
        return redirect()->to('/login')->with('success', 'Cuenta dada de baja correctamente');
    }
}
