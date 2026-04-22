<?php

namespace App\Controllers;

use App\Models\UsuarioModel;

class LoginController extends BaseController
{
    public function index()
    {
        $data['title'] = 'Login';
        return view('plantillas/head', $data)
            . view('contenido/login', $data)
            . view('plantillas/footer');
    }

    public function auth()
    {
        $usuarioModel = new UsuarioModel();

        // El formulario envía "usuario" (DNI) y "password"
        $usuario  = $this->request->getPost('usuario');   // campo del formulario (DNI)
        $password = $this->request->getPost('password');  // campo del formulario

        // Buscar solo por DNI_Usuario
        $data = $usuarioModel->where('DNI_Usuario', $usuario)->first();

        if ($data) {
            // Verificar estado
            if ($data['estado_usuario'] === 'inactivo') {
                return redirect()->back()->with('error', 'Usuario dado de baja');
            }

            // Verificar contraseña
            if (password_verify($password, $data['pass'])) {
                // Guardar datos en sesión con claves consistentes (minúsculas)
                session()->set([
                    'id_usuario'     => $data['id_Usuario'],       // corregido
                    'nombre_usuario' => $data['Nombre_Usuario'],
                    'apellido'       => $data['Apellido_Usuario'],
                    'dni_usuario'    => $data['DNI_Usuario'],
                    'id_tipo'        => $data['Id_Tipo'],          // clave para discriminar roles
                    'logged_in'      => true
                ]);

                // Redirige a la página principal
                return redirect()->to(base_url('/'));
            } else {
                return redirect()->back()->with('error', 'Contraseña incorrecta');
            }
        } else {
            // Mensaje más claro cuando no se encuentra el DNI
            return redirect()->back()->with('error', 'Debes ingresar tu DNI para iniciar sesión');
        }
    }






    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
