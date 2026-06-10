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
        if (empty($dni)) {
            return ['ok' => false, 'errores' => ['dni' => 'El campo DNI es obligatorio.']];
        }

        if (empty($contrasena)) {
            return ['ok' => false, 'errores' => ['contrasena' => 'El campo Contraseña es obligatorio.']];
        }

        $persona = PersonaModel::getInstance()->where('dni', $dni)->first();
        if (!$persona) {
            return ['ok' => false, 'errores' => ['dni' => 'DNI no encontrado.']];
        }

        $usuario = $this->where('id_persona', $persona['id_persona'])->first();
        if (!$usuario) {
            return ['ok' => false, 'errores' => ['dni' => 'Usuario no encontrado.']];
        }

        if (!password_verify($contrasena, $usuario['contrasena'])) {
            return ['ok' => false, 'errores' => ['contrasena' => 'Contraseña incorrecta.']];
        }

        if ($usuario['estado_usuario'] === 'inactivo') {
            return ['ok' => false, 'mensaje' => 'Usuario dado de baja.'];
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

    public function registrarUsuario(Persona $persona, string $nombreUsuario, string $contrasena): array
    {
        // Se validan las credenciales primero para poder mostrar TODOS los errores
        // (persona + usuario) juntos. Los errores se capturan ACÁ, antes de que
        // altaPersona reutilice el servicio de validación compartido.
        $validation = \Config\Services::validation();
        $credsOk = $validation->setRules([
            'nombre_usuario' => ['label' => 'Nombre de usuario', 'rules' => 'required|min_length[3]|max_length[50]|is_unique[usuario.nombre_usuario]'],
            'contrasena'     => ['label' => 'Contraseña',        'rules' => 'required|min_length[6]|max_length[100]'],
        ])->run(['nombre_usuario' => $nombreUsuario, 'contrasena' => $contrasena]);
        $credErrores = $credsOk ? [] : $validation->getErrors();

        $db = \Config\Database::connect();
        $db->transBegin();

        $personaResult = PersonaModel::getInstance()->altaPersona($persona);

        // Si falla la persona o las credenciales, se juntan todos los errores
        // y se aborta (no se inserta nada).
        if (!$personaResult['ok'] || !$credsOk) {
            $db->transRollback();
            return [
                'ok'      => false,
                'errores' => array_merge($personaResult['errores'] ?? [], $credErrores),
            ];
        }

        $usuarioResult = $this->altaUsuario($nombreUsuario, $contrasena, $personaResult['id']);
        if (!$usuarioResult['ok']) {
            // Si falla el alta del usuario, se revierte la persona ya insertada
            // para no dejar registros huérfanos en la base de datos.
            $db->transRollback();
            return $usuarioResult;
        }

        $db->transCommit();
        return $usuarioResult;
    }

    // Edición del propio perfil: actualiza los datos de la persona (excepto el DNI)
    // y el nombre de usuario. No modifica el estado del usuario.
    public function modificarPerfil(int $id, Persona $persona, string $nombreUsuario): array
    {
        $usuario = $this->find($id);
        if (!$usuario) {
            return ['ok' => false, 'errores' => ['id' => 'Usuario no encontrado.']];
        }

        // Se valida el nombre de usuario ANTES de escribir nada en la base de datos.
        $validation = \Config\Services::validation();
        if (!$validation->setRules([
            'nombre_usuario' => ['label' => 'Nombre de usuario', 'rules' => "required|min_length[3]|max_length[50]|is_unique[usuario.nombre_usuario,id_usuario,{$id}]"],
        ])->run(['nombre_usuario' => $nombreUsuario])) {
            return ['ok' => false, 'errores' => $validation->getErrors()];
        }

        // El DNI no se puede modificar desde el perfil: se conserva el actual.
        $personaActual = PersonaModel::getInstance()->find($usuario['id_persona']);
        $persona->dni  = $personaActual['dni'];

        $db = \Config\Database::connect();
        $db->transBegin();

        $personaResult = PersonaModel::getInstance()->actualizarPersona($usuario['id_persona'], $persona);
        if (!$personaResult['ok']) {
            $db->transRollback();
            return $personaResult;
        }

        $this->update($id, ['nombre_usuario' => $nombreUsuario]);
        $db->transCommit();
        return ['ok' => true];
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

    public function darDeBaja(int $id): bool
    {
        if (!$this->find($id)) {
            return false;
        }

        $this->update($id, ['estado_usuario' => 'inactivo']);
        return true;
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

    public function modificarUsuario(int $id, Persona $persona, string $nombreUsuario, string $estadoUsuario): array
    {
        $usuario = $this->find($id);
        if (!$usuario) {
            return ['ok' => false, 'errores' => ['id' => 'Usuario no encontrado.']];
        }

        // Se valida ANTES de escribir nada en la base de datos.
        $validation = \Config\Services::validation();
        if (!$validation->setRules([
            'nombre_usuario' => ['label' => 'Nombre de usuario', 'rules' => "required|min_length[3]|max_length[50]|is_unique[usuario.nombre_usuario,id_usuario,{$id}]"],
            'estado_usuario' => ['label' => 'Estado',  'rules' => 'required|in_list[activo,inactivo]'],
        ])->run(['nombre_usuario' => $nombreUsuario, 'estado_usuario' => $estadoUsuario])) {
            return ['ok' => false, 'errores' => $validation->getErrors()];
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        $personaResult = PersonaModel::getInstance()->actualizarPersona($usuario['id_persona'], $persona);
        if (!$personaResult['ok']) {
            $db->transRollback();
            return $personaResult;
        }

        $this->update($id, ['nombre_usuario' => $nombreUsuario, 'estado_usuario' => $estadoUsuario]);
        $db->transCommit();
        return ['ok' => true];
    }

    public function deshabilitar(int $id): bool
    {
        if (!$this->find($id)) {
            return false;
        }

        $this->update($id, ['estado_usuario' => 'inactivo']);
        return true;
    }

    public function habilitar(int $id): bool
    {
        if (!$this->find($id)) {
            return false;
        }

        $this->update($id, ['estado_usuario' => 'activo']);
        return true;
    }
}
