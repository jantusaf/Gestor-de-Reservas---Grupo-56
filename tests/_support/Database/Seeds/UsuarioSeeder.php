<?php

namespace Tests\Support\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UsuarioSeeder extends Seeder
{
    public function run()
    {
        // Tipo de usuario base.
        $this->db->table('tipo_usuario')->insert([
            'id_tipo_usuario'     => 2,
            'nombre_tipo_usuario' => 'Empleado',
        ]);

        // Persona + usuario que se va a MODIFICAR (id_usuario = 1).
        $this->db->table('persona')->insert([
            'id_persona'       => 1,
            'dni'              => '30123456',
            'nombre'           => 'Juan',
            'apellido'         => 'Pérez',
            'fecha_nacimiento' => '1990-05-15',
            'telefono'         => '3794123456',
            'calle'            => 'San Martín',
            'altura'           => '123',
        ]);
        $this->db->table('usuario')->insert([
            'id_usuario'      => 1,
            'nombre_usuario'  => 'jperez',
            'contrasena'      => password_hash('secreta123', PASSWORD_DEFAULT),
            'estado_usuario'  => 'activo',
            'id_persona'      => 1,
            'id_tipo_usuario' => 2,
        ]);

        // Segunda persona + usuario, para probar nombre de usuario / DNI DUPLICADO.
        $this->db->table('persona')->insert([
            'id_persona'       => 2,
            'dni'              => '99999999',
            'nombre'           => 'Ana',
            'apellido'         => 'Gómez',
            'fecha_nacimiento' => '1985-03-10',
            'telefono'         => '3794777777',
            'calle'            => 'Belgrano',
            'altura'           => '456',
        ]);
        $this->db->table('usuario')->insert([
            'id_usuario'      => 2,
            'nombre_usuario'  => 'agomez',
            'contrasena'      => password_hash('secreta123', PASSWORD_DEFAULT),
            'estado_usuario'  => 'activo',
            'id_persona'      => 2,
            'id_tipo_usuario' => 2,
        ]);
    }
}
