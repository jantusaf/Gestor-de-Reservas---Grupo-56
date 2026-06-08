<?php
namespace App\Models;

use CodeIgniter\Model;
use App\Entities\Persona;

class UsuarioModel extends Model
{
    protected $table      = 'usuario';
    protected $primaryKey = 'id_usuario';

    protected $allowedFields = [
        'nombre_usuario',
        'contrasena',
        'estado_usuario',
        'id_persona',
        'id_tipo_usuario'
    ];

    public function iniciarSesion(string $dni, string $contrasena): array
    {
        $persona = (new PersonaModel())->where('dni', $dni)->first();
        if (!$persona) {
            return ['ok' => false, 'mensaje' => 'DNI no encontrado.'];
        }

        $usuario = $this->where('id_persona', $persona['id_persona'])->first();
        if (!$usuario) {
            return ['ok' => false, 'mensaje' => 'Usuario no encontrado.'];
        }

        if ($usuario['estado_usuario'] === 'inactivo') {
            return ['ok' => false, 'mensaje' => 'Usuario dado de baja.'];
        }

        if (!password_verify($contrasena, $usuario['contrasena'])) {
            return ['ok' => false, 'mensaje' => 'Contraseña incorrecta.'];
        }

        return [
            'ok'    => true,
            'sesion' => [
                'id_usuario'     => $usuario['id_usuario'],
                'nombre_usuario' => $usuario['nombre_usuario'],
                'apellido'       => $persona['apellido'],
                'dni_usuario'    => $persona['dni'],
                'id_tipo'        => $usuario['id_tipo_usuario'],
                'logged_in'      => true,
            ],
        ];
    }

    public function registrarUsuario(string $dni, string $nombre, string $apellido, string $fechaNacimiento, string $telefono, string $calle, string $altura, string $nombreUsuario, string $contrasena): array
    {
        $persona = new Persona([
            'dni'              => $dni,
            'nombre'           => $nombre,
            'apellido'         => $apellido,
            'fecha_nacimiento' => $fechaNacimiento,
            'telefono'         => $telefono,
            'calle'            => $calle,
            'altura'           => $altura,
        ]);

        $personaResult = (new PersonaModel())->altaPersona($persona);
        if (!$personaResult['ok']) {
            return $personaResult;
        }

        return $this->altaUsuario($nombreUsuario, $contrasena, $personaResult['id']);
    }

    public function datosPerfil(int $id): ?array
    {
        $usuario = $this->find($id);
        if (!$usuario) {
            return null;
        }

        return ['usuario' => $usuario];
    }

    public function altaUsuario(string $nombreUsuario, string $contrasena, int $idPersona): array
    {
        $validation = \Config\Services::validation();

        if (!$validation->setRules([
            'nombre_usuario' => ['label' => 'Nombre de usuario', 'rules' => 'required|min_length[3]|max_length[50]|is_unique[usuario.nombre_usuario]'],
            'contrasena'     => ['label' => 'Contraseña',        'rules' => 'required|min_length[6]|max_length[100]'],
        ])->run([
            'nombre_usuario' => $nombreUsuario,
            'contrasena'     => $contrasena,
        ])) {
            return ['ok' => false, 'errores' => $validation->getErrors()];
        }

        $this->insert([
            'nombre_usuario'  => $nombreUsuario,
            'contrasena'      => password_hash($contrasena, PASSWORD_DEFAULT),
            'estado_usuario'  => 'activo',
            'id_persona'      => $idPersona,
            'id_tipo_usuario' => 2,
        ]);

        return ['ok' => true, 'id' => $this->getInsertID()];
    }

    public function actualizarUsuario(int $id, string $nombreUsuario): array
    {
        $validation = \Config\Services::validation();

        if (!$validation->setRules([
            'nombre_usuario' => ['label' => 'Nombre de usuario', 'rules' => "required|min_length[3]|max_length[50]|is_unique[usuario.nombre_usuario,id_usuario,{$id}]"],
        ])->run(['nombre_usuario' => $nombreUsuario])) {
            return ['ok' => false, 'errores' => $validation->getErrors()];
        }

        $this->update($id, ['nombre_usuario' => $nombreUsuario]);
        return ['ok' => true];
    }

    public function darDeBaja(int $id): array
    {
        if (!$this->find($id)) {
            return ['ok' => false, 'mensaje' => 'Usuario no encontrado.'];
        }

        $this->update($id, ['estado_usuario' => 'inactivo']);
        return ['ok' => true];
    }

    public function listarUsuarios(): array
    {
        return \Config\Database::connect()
            ->table('usuario')
            ->select('usuario.id_usuario, usuario.nombre_usuario, usuario.estado_usuario, usuario.id_tipo_usuario, persona.nombre, persona.apellido, persona.dni, persona.telefono')
            ->join('persona', 'persona.id_persona = usuario.id_persona')
            ->orderBy('usuario.estado_usuario', 'ASC')
            ->orderBy('persona.apellido', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function datosFormularioEditar(int $id): ?array
    {
        $usuario = $this->find($id);
        if (!$usuario) {
            return null;
        }

        return [
            'usuario' => $usuario,
            'persona' => (new PersonaModel())->find($usuario['id_persona']),
        ];
    }

    public function modificarUsuario(int $id, string $dni, string $nombre, string $apellido, string $fechaNacimiento, string $telefono, string $calle, string $altura, string $nombreUsuario, string $estadoUsuario): array
    {
        $usuario = $this->find($id);
        if (!$usuario) {
            return ['ok' => false, 'errores' => ['id' => 'Usuario no encontrado.']];
        }

        $persona = new Persona([
            'dni'              => $dni,
            'nombre'           => $nombre,
            'apellido'         => $apellido,
            'fecha_nacimiento' => $fechaNacimiento,
            'telefono'         => $telefono,
            'calle'            => $calle,
            'altura'           => $altura,
        ]);

        $personaResult = (new PersonaModel())->actualizarPersona($usuario['id_persona'], $persona);
        if (!$personaResult['ok']) {
            return $personaResult;
        }

        $validation = \Config\Services::validation();
        if (!$validation->setRules([
            'nombre_usuario' => ['label' => 'Nombre de usuario', 'rules' => "required|min_length[3]|max_length[50]|is_unique[usuario.nombre_usuario,id_usuario,{$id}]"],
            'estado_usuario' => ['label' => 'Estado',            'rules' => 'required|in_list[activo,inactivo]'],
        ])->run(['nombre_usuario' => $nombreUsuario, 'estado_usuario' => $estadoUsuario])) {
            return ['ok' => false, 'errores' => $validation->getErrors()];
        }

        $this->update($id, ['nombre_usuario' => $nombreUsuario, 'estado_usuario' => $estadoUsuario]);
        return ['ok' => true];
    }

    public function deshabilitar(int $id): array
    {
        if (!$this->find($id)) {
            return ['ok' => false, 'mensaje' => 'Usuario no encontrado.'];
        }

        $this->update($id, ['estado_usuario' => 'inactivo']);
        return ['ok' => true, 'mensaje' => 'Usuario deshabilitado.'];
    }

    public function habilitar(int $id): array
    {
        if (!$this->find($id)) {
            return ['ok' => false, 'mensaje' => 'Usuario no encontrado.'];
        }

        $this->update($id, ['estado_usuario' => 'activo']);
        return ['ok' => true, 'mensaje' => 'Usuario habilitado.'];
    }
}
