<?php
namespace App\Models;

use CodeIgniter\Model;

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

    public function altaUsuario(string $nombreUsuario, string $password, int $idPersona): int
    {
        $this->insert([
            'nombre_usuario'  => $nombreUsuario,
            'contrasena'      => password_hash($password, PASSWORD_DEFAULT),
            'estado_usuario'  => 'activo',
            'id_persona'      => $idPersona,
            'id_tipo_usuario' => 2,
        ]);
        return $this->getInsertID();
    }

    public function actualizarUsuario(int $id, string $nombreUsuario, string $estadoUsuario): void
    {
        $this->update($id, [
            'nombre_usuario' => $nombreUsuario,
            'estado_usuario' => $estadoUsuario,
        ]);
    }

    public function darDeBaja(int $id): void
    {
        $this->update($id, ['estado_usuario' => 'inactivo']);
    }
}
