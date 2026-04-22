<?php

namespace App\Controllers;

use App\Models\UsuarioModel;

class UsuarioController extends BaseController
{
    public function __construct()
    {
        helper(['form', 'url']);
    }

    // CREATE: Muestra la vista de registro de usuario
    public function registrarse()
    {
        $data['title'] = 'Registrarse';
        return view('plantillas/head', $data)
            . view('contenido/registrarse', $data)
            . view('plantillas/footer');
    }

// CREATE: Procesa el formulario y guarda el usuario
public function save()
{
    $usuarioModel = new UsuarioModel();

    $validation = $this->validate([
        'dni'             => 'required|numeric|min_length[7]|max_length[20]|is_unique[usuario.DNI_Usuario]',
        'nombre'          => 'required|alpha_space|min_length[3]|max_length[50]',
        'apellido'        => 'required|alpha_space|min_length[3]|max_length[50]',
        'fecha_nacimiento'=> 'required|valid_date[Y-m-d]',
        'telefono'        => 'required|numeric|max_length[20]', // ahora obligatorio y numérico
        'sexo'            => 'required|in_list[Masculino,Femenino,Otro]',
        'password'        => 'required|min_length[6]|max_length[100]'
    ]);

    if (!$validation) {
        // Mostrar errores de validación
        return view('plantillas/head')
            . view('contenido/registrarse', [
                'validation' => $this->validator
            ])
            . view('plantillas/footer');
    }

    // Intentar guardar
    try {
        $usuarioModel->insert([
            'DNI_Usuario'      => $this->request->getVar('dni'),
            'Nombre_Usuario'   => $this->request->getVar('nombre'),
            'Apellido_Usuario' => $this->request->getVar('apellido'),
            'Fecha_Ingreso'    => date('Y-m-d'),
            'Fecha_Nacimiento' => $this->request->getVar('fecha_nacimiento'),
            'telefono'         => $this->request->getVar('telefono'),
            'Sexo_Usuario'     => $this->request->getVar('sexo'),
            'pass'             => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
            'Id_Tipo'          => 2, // por defecto cliente normal
            'estado_usuario'   => 'activo'
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
        $id = session()->get('id_Usuario'); // tomado de la sesión

        $data['usuario'] = $usuarioModel->find($id);
        $data['title']   = 'Mi Perfil';

        return view('plantillas/head', $data)
            . view('contenido/perfil_usuario', $data)
            . view('plantillas/footer');
    }

    // UPDATE: Actualizar datos del usuario
    public function update()
    {
        $usuarioModel = new UsuarioModel();
        $id = session()->get('id_Usuario');

        $data = [
            'Nombre_Usuario'   => $this->request->getVar('nombre'),
            'Apellido_Usuario' => $this->request->getVar('apellido'),
            'telefono'         => $this->request->getVar('telefono'),
            'Sexo_Usuario'     => $this->request->getVar('sexo')
        ];

        $usuarioModel->update($id, $data);

        return redirect()->back()->with('success', 'Datos actualizados correctamente');
    }

    // DELETE: Dar de baja usuario (soft delete)
    public function deleteAccount()
    {
        $usuarioModel = new UsuarioModel();
        $id = session()->get('id_Usuario');

        $usuarioModel->update($id, ['estado_usuario' => 'inactivo']);

        session()->destroy();
        return redirect()->to('/login')->with('success', 'Cuenta dada de baja correctamente');
    }
}
