<?php

namespace Tests\Support\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RecintoSeeder extends Seeder
{
    public function run()
    {
        // Tipos de recinto que usan los casos de prueba (id_tipo_recinto = 1).
        $this->db->table('tipo_recinto')->insertBatch([
            ['id_tipo_recinto' => 1, 'nombre_tipo_recinto' => 'Cancha de fútbol'],
            ['id_tipo_recinto' => 2, 'nombre_tipo_recinto' => 'Cancha de tenis'],
        ]);

        // Recinto base (id_recinto = 1) para las pruebas de "Modificar Recinto".
        $this->db->table('recinto')->insert([
            'id_recinto'      => 1,
            'tarifa'          => '1500.00',
            'estado_recinto'  => 'activo',
            'descripcion'     => 'Cancha de fútbol',
            'id_tipo_recinto' => 1,
        ]);
    }
}
