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

    // CREATE: Muestra la vista de registro de usuario
    public function muestra_vista_registrarse()
    {
        $data['title'] = 'Registrarse';
        return view('plantillas/head', $data)
            . view('contenido/registrarse', $data)
            . view('plantillas/footer');
    }

    // CREATE: Procesa el formulario y guarda persona + usuario
    public function guardar()
    {
        $usuarioModel = new UsuarioModel();
        $personaModel = new PersonaModel();

        $validation = $this->validate([
            'dni'             => 'required|numeric|min_length[7]|max_length[20]|is_unique[persona.dni]',
            'nombre'          => 'required|alpha_space|min_length[3]|max_length[50]',
            'apellido'        => 'required|alpha_space|min_length[3]|max_length[50]',
            'fecha_nacimiento'=> 'required|valid_date[Y-m-d]',
            'telefono'        => 'required|numeric|max_length[20]',
            'calle'           => 'required|min_length[3]|max_length[50]',
            'altura'          => 'required|max_length[10]',
            'usuario'         => 'required|min_length[3]|max_length[50]|is_unique[usuario.nombre_usuario]',
            'password'        => 'required|min_length[6]|max_length[100]'
        ]);

        if (!$validation) {
            // Mostrar errores de validación
            return view('plantillas/head')
                . view('contenido/registrarse', ['validation' => $this->validator])
                . view('plantillas/footer');
        }

        try {
            // 1. Insertar persona
            $personaId = $personaModel->insert([
                'dni'             => $this->request->getVar('dni'),
                'nombre'          => $this->request->getVar('nombre'),
                'apellido'        => $this->request->getVar('apellido'),
                'fecha_nacimiento'=> $this->request->getVar('fecha_nacimiento'),
                'telefono'        => $this->request->getVar('telefono'),
                'calle'           => $this->request->getVar('calle'),
                'altura'          => $this->request->getVar('altura')
            ]);

            // 2. Insertar usuario vinculado a persona
            $usuarioModel->insert([
                'nombre_usuario'  => $this->request->getVar('usuario'),
                'contrasena' => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
                'estado_usuario'  => 'activo',
                'id_persona'      => $personaId,
                'id_tipo_usuario' => 2 // por defecto cliente normal
            ]);

            session()->setFlashdata('success', 'Usuario registrado correctamente');
            return redirect()->to('/login');

        } catch (\Exception $e) {
            // Si falla el insert, mostrar error
            session()->setFlashdata('error', 'Error al registrar: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    // READ: Mostrar perfil del usuario logueado
    public function perfil()
    {
        $usuarioModel = new UsuarioModel();
        $id = session()->get('id_usuario'); // tomado de la sesión

        $data['usuario'] = $usuarioModel->find($id);
        $data['title']   = 'Mi Perfil';

        return view('plantillas/head', $data)
            . view('contenido/perfil_usuario', $data)
            . view('plantillas/footer');
    }

    // UPDATE: Actualizar datos del usuario
    public function actualizar()
    {
        $usuarioModel = new UsuarioModel();
        $id = session()->get('id_usuario');

        $data = [
            'nombre_usuario' => $this->request->getVar('usuario'),
            'estado_usuario' => $this->request->getVar('estado') ?? 'activo'
        ];

        $usuarioModel->update($id, $data);

        return redirect()->back()->with('success', 'Datos actualizados correctamente');
    }

    // DELETE: Dar de baja usuario (soft delete)
    public function dar_de_baja()
    {
        $usuarioModel = new UsuarioModel();
        $id = session()->get('id_usuario');

        $usuarioModel->update($id, ['estado_usuario' => 'inactivo']);

        session()->destroy();
        return redirect()->to('/login')->with('success', 'Cuenta dada de baja correctamente');
    }
}
