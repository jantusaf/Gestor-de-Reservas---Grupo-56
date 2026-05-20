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
            . view('plantillas/footer', $data);
    }

   public function iniciar_sesion()
{
    $usuarioModel = new UsuarioModel();

    $dni = $this->request->getPost('dni');
    $password  = $this->request->getPost('password');

    // llama al metodo que valida
    $resultado = $this->verificar_datos($usuarioModel, $dni, $password);

   
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


private function verificar_datos($usuarioModel, $dni, $password)
{
    // 1. Buscar persona por DNI
    $personaModel = new \App\Models\PersonaModel();
    $persona = $personaModel->where('dni', $dni)->first();

    if (!$persona) {
        return [
            'estado' => false,
            'mensaje' => 'DNI no encontrado'
        ];
    }

    // 2. Buscar usuario asociado a esa persona
    $usuario = $usuarioModel->where('id_persona', $persona['id_persona'])->first();

    if (!$usuario) {
        return [
            'estado' => false,
            'mensaje' => 'Usuario no encontrado'
        ];
    }

    // 3. Validar estado
    if ($usuario['estado_usuario'] === 'inactivo') {
        return [
            'estado' => false,
            'mensaje' => 'Usuario dado de baja'
        ];
    }

    // 4. Validar contraseña
    if (!password_verify($password, $usuario['contrasena'])) {
        return [
            'estado' => false,
            'mensaje' => 'Contraseña incorrecta'
        ];
    }

    // 5. Devolver datos reales para la sesión
    return [
        'estado' => true,
        'data' => [
            'id_usuario'     => $usuario['id_usuario'],
            'nombre_usuario' => $usuario['nombre_usuario'],
            'apellido'       => $persona['apellido'],
            'dni_usuario'    => $persona['dni'],
            'id_tipo'        => $usuario['id_tipo_usuario']
        ]
    ];
}




    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}
