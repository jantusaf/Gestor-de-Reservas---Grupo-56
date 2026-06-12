<?php

namespace App\Controllers;

use App\Models\UsuarioModel;
use App\Models\PersonaModel;
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
            'dni'=> $this->request->getPost('dni')?? '',
            'nombre'=> $this->request->getPost('nombre')?? '',
            'apellido'=> $this->request->getPost('apellido')?? '',
            'fecha_nacimiento' => $this->request->getPost('fecha_nacimiento') ?? '',
            'telefono'=> $this->request->getPost('telefono')?? '',
            'calle'=> $this->request->getPost('calle')?? '',
            'altura'=> $this->request->getPost('altura')?? '',
        ]);

        $resultado = $usuarioModel->registrarUsuario(
            $persona,
            $this->request->getPost('nombre_usuario') ?? '',
            $this->request->getPost('contrasena')?? '',
        );

        if (!$resultado['ok']) {
            return redirect()->back()->withInput()->with('errors', $resultado['errores']);
        }

        return redirect()->to('/login')->with('success', 'Usuario registrado correctamente.');
    }

    public function perfil()
    {
        $usuarioModel = new UsuarioModel();
        $usuario = $usuarioModel->find((int) session()->get('id_usuario'));

        if (!$usuario) {
            return redirect()->to('/login')->with('error', 'Usuario no encontrado.');
        }

        return view('plantillas/head', ['title' => 'Mi Perfil'])
            . view('contenido/perfil_usuario', [
                'usuario' => $usuario,
                'persona' => (new PersonaModel())->find($usuario['id_persona']),
            ])
            . view('plantillas/footer');
    }

    public function formularioEditarPerfil()
    {
        $usuarioModel = new UsuarioModel();
        $usuario = $usuarioModel->find((int) session()->get('id_usuario'));

        if (!$usuario) {
            return redirect()->to('/login')->with('error', 'Usuario no encontrado.');
        }

        return view('plantillas/head', ['title' => 'Editar Mi Perfil'])
            . view('contenido/editar_perfil', [
                'usuario' => $usuario,
                'persona' => (new PersonaModel())->find($usuario['id_persona']),
            ])
            . view('plantillas/footer');
    }

    public function actualizar()
    {
        $usuarioModel = new UsuarioModel();

        $persona = new Persona([
            'nombre'=> $this->request->getPost('nombre')?? '',
            'apellido'=> $this->request->getPost('apellido')?? '',
            'fecha_nacimiento' => $this->request->getPost('fecha_nacimiento')?? '',
            'telefono'=> $this->request->getPost('telefono')?? '',
            'calle'=> $this->request->getPost('calle')?? '',
            'altura'=> $this->request->getPost('altura')?? '',
        ]);

        $resultado = $usuarioModel->modificarPerfil(
            (int) session()->get('id_usuario'),
            $persona,
            $this->request->getPost('nombre_usuario') ?? '',
        );

        if (!$resultado['ok']) {
            return redirect()->to('/usuario/perfil/editar')->withInput()->with('errors', $resultado['errores']);
        }

        // reflejar el nuevo nombre de usuario en la sesión.
        session()->set('nombre_usuario', $this->request->getPost('nombre_usuario'));

        return redirect()->to('/usuario/perfil')->with('success', 'Datos actualizados correctamente.');
    }

    public function baja()
    {
        $usuarioModel = new UsuarioModel();

        if (!$usuarioModel->darDeBaja((int) session()->get('id_usuario'))) {
            return redirect()->back()->with('error', 'No se pudo dar de baja la cuenta.');
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
            'dni'=> $this->request->getPost('dni') ?? '',
            'nombre'=> $this->request->getPost('nombre') ?? '',
            'apellido'=> $this->request->getPost('apellido') ?? '',
            'fecha_nacimiento'=> $this->request->getPost('fecha_nacimiento') ?? '',
            'telefono'=> $this->request->getPost('telefono') ?? '',
            'calle'=> $this->request->getPost('calle')?? '',
            'altura'=> $this->request->getPost('altura')?? '',
        ]);

        $resultado = $usuarioModel->registrarUsuario(
            $persona,
            $this->request->getPost('nombre_usuario') ?? '',
            $this->request->getPost('contrasena')?? '',
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
        $usuario = $usuarioModel->find((int) $id);

        if (!$usuario) {
            return redirect()->to('/usuario/listar')->with('error', 'Usuario no encontrado.');
        }

        return view('plantillas/head', ['title' => 'Editar Usuario'])
            . view('contenido/crud_usuario/editar_usuario', [
                'usuario' => $usuario,
                'persona' => (new PersonaModel())->find($usuario['id_persona']),
            ])
            . view('plantillas/footer');
    }

    public function editarUsuario($id)
    {
        $usuarioModel = new UsuarioModel();

        $persona = new Persona([
            'dni'=> $this->request->getPost('dni')?? '',
            'nombre'=> $this->request->getPost('nombre')?? '',
            'apellido'=> $this->request->getPost('apellido')?? '',
            'fecha_nacimiento'=> $this->request->getPost('fecha_nacimiento') ?? '',
            'telefono'=> $this->request->getPost('telefono')?? '',
            'calle'=> $this->request->getPost('calle')?? '',
            'altura'=> $this->request->getPost('altura')?? '',
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

        if (!$usuarioModel->deshabilitar((int) $id)) {
            return redirect()->to('/usuario/listar')->with('error', 'Usuario no encontrado.');
        }

        return redirect()->to('/usuario/listar');
    }

    public function habilitarUsuario($id)
    {
        $usuarioModel = new UsuarioModel();

        if (!$usuarioModel->habilitar((int) $id)) {
            return redirect()->to('/usuario/listar')->with('error', 'Usuario no encontrado.');
        }

        return redirect()->to('/usuario/listar');
    }
}
