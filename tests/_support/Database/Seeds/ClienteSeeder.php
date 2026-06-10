<?php

namespace Tests\Support\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ClienteSeeder extends Seeder
{
    public function run()
    {
        // Insertar una persona base para pruebas de duplicados
        $this->db->table('persona')->insert([
            'dni'              => '99999999',
            'nombre'           => 'Test',
            'apellido'         => 'Duplicado',
            'fecha_nacimiento' => '1990-01-01',
            'telefono'         => '3794000000',
            'calle'            => 'Calle Test',
            'altura'           => '100',
        ]);

        $idPersona = $this->db->insertID();

        // Insertar un cliente base para pruebas de email/dni duplicado
        $this->db->table('cliente')->insert([
            'email'          => 'duplicado@gmail.com',
            'fecha_alta'     => date('Y-m-d'),
            'estado_cliente' => 'activo',
            'id_persona'     => $idPersona,
        ]);
    }
}