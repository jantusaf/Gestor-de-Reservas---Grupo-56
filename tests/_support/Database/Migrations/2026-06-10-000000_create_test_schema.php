<?php

namespace Tests\Support\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Esquema mínimo para las pruebas unitarias (SQLite, grupo 'tests').
 *
 * Replica las tablas que tocan los métodos probados:
 *   persona, tipo_recinto, recinto, tipo_usuario, usuario, cliente.
 *
 * Al usarse junto con $refresh = true en los tests, este esquema se
 * destruye y se vuelve a crear ANTES de cada prueba, lo que hace que
 * los scripts de prueba sean ejecutables múltiples veces sin limpieza
 * manual (la base queda siempre en un estado conocido).
 */
class CreateTestSchema extends Migration
{
    protected $DBGroup = 'tests';

    public function up()
    {
        // ---------- persona ----------
        $this->forge->addField([
            'id_persona'       => ['type' => 'integer', 'auto_increment' => true],
            'dni'              => ['type' => 'varchar', 'constraint' => 20],
            'nombre'           => ['type' => 'varchar', 'constraint' => 50],
            'apellido'         => ['type' => 'varchar', 'constraint' => 50],
            'fecha_nacimiento' => ['type' => 'date', 'null' => true],
            'telefono'         => ['type' => 'varchar', 'constraint' => 20, 'null' => true],
            'calle'            => ['type' => 'varchar', 'constraint' => 50],
            'altura'           => ['type' => 'varchar', 'constraint' => 10],
        ]);
        $this->forge->addKey('id_persona', true);
        $this->forge->createTable('persona');

        // ---------- tipo_recinto ----------
        $this->forge->addField([
            'id_tipo_recinto'     => ['type' => 'integer', 'auto_increment' => true],
            'nombre_tipo_recinto' => ['type' => 'varchar', 'constraint' => 50],
        ]);
        $this->forge->addKey('id_tipo_recinto', true);
        $this->forge->createTable('tipo_recinto');

        // ---------- recinto ----------
        $this->forge->addField([
            'id_recinto'      => ['type' => 'integer', 'auto_increment' => true],
            'tarifa'          => ['type' => 'decimal', 'constraint' => '10,2'],
            'estado_recinto'  => ['type' => 'varchar', 'constraint' => 10, 'default' => 'activo'],
            'descripcion'     => ['type' => 'varchar', 'constraint' => 50],
            'id_tipo_recinto' => ['type' => 'integer'],
        ]);
        $this->forge->addKey('id_recinto', true);
        $this->forge->addForeignKey('id_tipo_recinto', 'tipo_recinto', 'id_tipo_recinto', 'CASCADE', 'CASCADE');
        $this->forge->createTable('recinto');

        // ---------- tipo_usuario ----------
        $this->forge->addField([
            'id_tipo_usuario'     => ['type' => 'integer', 'auto_increment' => true],
            'nombre_tipo_usuario' => ['type' => 'varchar', 'constraint' => 50],
        ]);
        $this->forge->addKey('id_tipo_usuario', true);
        $this->forge->createTable('tipo_usuario');

        // ---------- usuario ----------
        $this->forge->addField([
            'id_usuario'      => ['type' => 'integer', 'auto_increment' => true],
            'nombre_usuario'  => ['type' => 'varchar', 'constraint' => 50],
            'contrasena'      => ['type' => 'varchar', 'constraint' => 255],
            'estado_usuario'  => ['type' => 'varchar', 'constraint' => 10, 'default' => 'activo'],
            'id_persona'      => ['type' => 'integer'],
            'id_tipo_usuario' => ['type' => 'integer'],
        ]);
        $this->forge->addKey('id_usuario', true);
        $this->forge->addForeignKey('id_persona', 'persona', 'id_persona', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_tipo_usuario', 'tipo_usuario', 'id_tipo_usuario', 'CASCADE', 'CASCADE');
        $this->forge->createTable('usuario');

        // ---------- cliente ----------
        $this->forge->addField([
            'id_cliente'     => ['type' => 'integer', 'auto_increment' => true],
            'email'          => ['type' => 'varchar', 'constraint' => 100],
            'fecha_alta'     => ['type' => 'date', 'null' => true],
            'estado_cliente' => ['type' => 'varchar', 'constraint' => 10, 'default' => 'activo'],
            'id_persona'     => ['type' => 'integer'],
        ]);
        $this->forge->addKey('id_cliente', true);
        $this->forge->addForeignKey('id_persona', 'persona', 'id_persona', 'CASCADE', 'CASCADE');
        $this->forge->createTable('cliente');
    }

    public function down()
    {
        // Orden inverso por las claves foráneas.
        $this->forge->dropTable('cliente', true);
        $this->forge->dropTable('usuario', true);
        $this->forge->dropTable('recinto', true);
        $this->forge->dropTable('tipo_usuario', true);
        $this->forge->dropTable('tipo_recinto', true);
        $this->forge->dropTable('persona', true);
    }
}
