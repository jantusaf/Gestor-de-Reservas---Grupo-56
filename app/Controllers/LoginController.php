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

   public function iniciar_sesion()
{
    $usuarioModel = new UsuarioModel();

    $dni       = $this->request->getPost('usuario');
    $password  = $this->request->getPost('password');

    // llama al metodo que valida
    $resultado = $this->validar_datos($usuarioModel, $dni, $password);

   
    if ($resultado['estado'] === false) {
       return redirect()->back()->with('error', $resultado['mensaje']);
    }

    //si todo ok trae sesion
    $data = $resultado['data'];

    session()->set([
        'id_usuario'     => $data['id_usuario'],
        'nombre_usuario' => $data['Nombre_Usuario'],
        'apellido'       => $data['Apellido_Usuario'],
        'dni_usuario'    => $data['DNI_Usuario'],
        'id_tipo'        => $data['Id_Tipo'],
        'logged_in'      => true
    ]);
dd(session()->get());
   return redirect()->to('/contenido/principal');
}


private function validar_datos($usuarioModel, $dni, $password)
{
    // busca usuario por DNI
    $data = $usuarioModel->where('DNI_Usuario', $dni)->first();

    if (!$data) {
        return [
            'estado' => false,
            'mensaje' => 'Debes ingresar tu DNI para iniciar sesión'
        ];
    }

    // verif estado
    if ($data['estado_usuario'] === 'inactivo') {
        return [
            'estado' => false,
            'mensaje' => 'Usuario dado de baja'
        ];
    }

    // verif contraseña
    if (!password_verify($password, $data['pass'])) {
        return [
            'estado' => false,
            'mensaje' => 'Contraseña incorrecta'
        ];
    }

   
    return [
        'estado' => true,
        'data' => $data
    ];
}



    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
