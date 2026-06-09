<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use App\Entities\Persona;

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

        $persona = new Persona([
            'dni'              => $this->request->getPost('dni')              ?? '',
            'nombre'           => $this->request->getPost('nombre')           ?? '',
            'apellido'         => $this->request->getPost('apellido')         ?? '',
            'fecha_nacimiento' => $this->request->getPost('fecha_nacimiento') ?? '',
            'telefono'         => $this->request->getPost('telefono')         ?? '',
            'calle'            => $this->request->getPost('calle')            ?? '',
            'altura'           => $this->request->getPost('altura')           ?? '',
        ]);

        $resultado = $usuarioModel->registrarUsuario(
            $persona,
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

    public function formularioAlta()
    {
        return view('plantillas/head', ['title' => 'Alta de Usuario'])
            . view('contenido/crud_usuario/alta_usuario')
            . view('plantillas/footer');
    }

    public function guardarUsuario()
    {
        $usuarioModel = new UsuarioModel();

        $persona = new Persona([
            'dni'              => $this->request->getPost('dni')              ?? '',
            'nombre'           => $this->request->getPost('nombre')           ?? '',
            'apellido'         => $this->request->getPost('apellido')         ?? '',
            'fecha_nacimiento' => $this->request->getPost('fecha_nacimiento') ?? '',
            'telefono'         => $this->request->getPost('telefono')         ?? '',
            'calle'            => $this->request->getPost('calle')            ?? '',
            'altura'           => $this->request->getPost('altura')           ?? '',
        ]);

        $resultado = $usuarioModel->registrarUsuario(
            $persona,
            $this->request->getPost('nombre_usuario') ?? '',
            $this->request->getPost('contrasena')     ?? '',
        );

        if (!$resultado['ok']) {
            return redirect()->back()->withInput()->with('errors', $resultado['errores']);
        }

        return redirect()->to('/usuario/listar')->with('success', 'Usuario registrado correctamente.');
    }

    public function listarUsuarios()
    {
        $usuarioModel = new UsuarioModel();

        return view('plantillas/head', ['title' => 'Listado de Usuarios'])
            . view('contenido/crud_usuario/listar_usuarios', [
                'usuarios' => $usuarioModel->listarUsuarios(),
            ])
            . view('plantillas/footer');
    }

    public function formularioEditar($id)
    {
        $usuarioModel = new UsuarioModel();
        $data         = $usuarioModel->datosFormularioEditar((int) $id);

        if (!$data) {
            return redirect()->to('/usuario/listar')->with('error', 'Usuario no encontrado.');
        }

        return view('plantillas/head', ['title' => 'Editar Usuario'])
            . view('contenido/crud_usuario/editar_usuario', $data)
            . view('plantillas/footer');
    }

    public function editarUsuario($id)
    {
        $usuarioModel = new UsuarioModel();

        $persona = new Persona([
            'dni'              => $this->request->getPost('dni')              ?? '',
            'nombre'           => $this->request->getPost('nombre')           ?? '',
            'apellido'         => $this->request->getPost('apellido')         ?? '',
            'fecha_nacimiento' => $this->request->getPost('fecha_nacimiento') ?? '',
            'telefono'         => $this->request->getPost('telefono')         ?? '',
            'calle'            => $this->request->getPost('calle')            ?? '',
            'altura'           => $this->request->getPost('altura')           ?? '',
        ]);

        $resultado = $usuarioModel->modificarUsuario(
            (int) $id,
            $persona,
            $this->request->getPost('nombre_usuario') ?? '',
            $this->request->getPost('estado_usuario') ?? '',
        );

        if (!$resultado['ok']) {
            return redirect()->to('/usuario/editar/' . $id)->with('errors', $resultado['errores']);
        }

        return redirect()->to('/usuario/listar')->with('success', 'Usuario actualizado correctamente.');
    }

    public function deshabilitarUsuario($id)
    {
        $usuarioModel = new UsuarioModel();
        $resultado    = $usuarioModel->deshabilitar((int) $id);

        if (!$resultado['ok']) {
            return redirect()->to('/usuario/listar')->with('error', $resultado['mensaje']);
        }

        return redirect()->to('/usuario/listar')->with('success', $resultado['mensaje']);
    }

    public function habilitarUsuario($id)
    {
        $usuarioModel = new UsuarioModel();
        $resultado    = $usuarioModel->habilitar((int) $id);

        if (!$resultado['ok']) {
            return redirect()->to('/usuario/listar')->with('error', $resultado['mensaje']);
        }

        return redirect()->to('/usuario/listar')->with('success', $resultado['mensaje']);
    }
}
