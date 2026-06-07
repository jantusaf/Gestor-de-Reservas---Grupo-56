<?php

namespace App\Controllers;

use App\Models\UsuarioModel;

class LoginController extends BaseController
{
    public function index()
    {
        return view('plantillas/head', ['title' => 'Login'])
            . view('contenido/login')
            . view('plantillas/footer');
    }

    public function iniciarSesion()
    {
        $usuarioModel = new UsuarioModel();

        $resultado = $usuarioModel->iniciarSesion(
            $this->request->getPost('dni')      ?? '',
            $this->request->getPost('contrasena') ?? '',
        );

        if (!$resultado['ok']) {
            return redirect()->back()->with('error', $resultado['mensaje']);
        }

        session()->set($resultado['sesion']);
        return redirect()->to('/contenido/principal');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
