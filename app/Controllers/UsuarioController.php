<?php

namespace App\Controllers;

use App\Models\UsuarioModel;

class UsuarioController extends BaseController
{
    public function __construct()
    {
        helper(['form', 'url']);
    }

    public function formularioRegistro()
    {
        return view('plantillas/head', ['title' => 'Registrarse'])
            . view('contenido/registrarse')
            . view('plantillas/footer');
    }

    public function guardar()
    {
        $usuarioModel = new UsuarioModel();

        $resultado = $usuarioModel->registrarUsuario(
            $this->request->getPost('dni')            ?? '',
            $this->request->getPost('nombre')         ?? '',
            $this->request->getPost('apellido')       ?? '',
            $this->request->getPost('fecha_nacimiento') ?? '',
            $this->request->getPost('telefono')       ?? '',
            $this->request->getPost('calle')          ?? '',
            $this->request->getPost('altura')         ?? '',
            $this->request->getPost('nombre_usuario') ?? '',
            $this->request->getPost('contrasena')     ?? '',
        );

        if (!$resultado['ok']) {
            return redirect()->back()->withInput()->with('errors', $resultado['errores']);
        }

        return redirect()->to('/login')->with('success', 'Usuario registrado correctamente.');
    }

    public function perfil()
    {
        $usuarioModel = new UsuarioModel();
        $data         = $usuarioModel->datosPerfil((int) session()->get('id_usuario'));

        if (!$data) {
            return redirect()->to('/login')->with('error', 'Usuario no encontrado.');
        }

        return view('plantillas/head', ['title' => 'Mi Perfil'])
            . view('contenido/perfil_usuario', $data)
            . view('plantillas/footer');
    }

    public function actualizar()
    {
        $usuarioModel = new UsuarioModel();

        $resultado = $usuarioModel->actualizarUsuario(
            (int) session()->get('id_usuario'),
            $this->request->getPost('nombre_usuario') ?? '',
        );

        if (!$resultado['ok']) {
            return redirect()->back()->withInput()->with('errors', $resultado['errores']);
        }

        return redirect()->back()->with('success', 'Datos actualizados correctamente.');
    }

    public function baja()
    {
        $usuarioModel = new UsuarioModel();

        $resultado = $usuarioModel->darDeBaja((int) session()->get('id_usuario'));

        if (!$resultado['ok']) {
            return redirect()->back()->with('error', $resultado['mensaje']);
        }

        session()->destroy();
        return redirect()->to('/login')->with('success', 'Cuenta dada de baja correctamente.');
    }
}
