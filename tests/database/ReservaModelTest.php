<?php

use App\Models\ReservaModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class ReservaModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = false;
    protected $refresh = false;

    private ReservaModel $model;
    private $conn;

    protected function setUp(): void
    {
        parent::setUp();

        $this->conn  = \Config\Database::connect('tests');
        $forge       = \Config\Database::forge('tests');

        $forge->dropTable('reserva', true);
        $forge->dropTable('horario', true);

        $forge->addField([
            'id_horario' => ['type' => 'INT', 'auto_increment' => true],
            'horario'    => ['type' => 'VARCHAR', 'constraint' => 20],
        ]);
        $forge->addPrimaryKey('id_horario');
        $forge->createTable('horario');

        $forge->addField([
            'id_reserva'     => ['type' => 'INT', 'auto_increment' => true],
            'fecha_reserva'  => ['type' => 'DATE'],
            'monto'          => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => '0.00'],
            'estado_reserva' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pendiente'],
            'estado_pago'    => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pendiente'],
            'id_horario'     => ['type' => 'INT'],
            'id_cliente'     => ['type' => 'INT', 'default' => 1],
            'id_recinto'     => ['type' => 'INT'],
            'id_usuario'     => ['type' => 'INT', 'default' => 1],
        ]);
        $forge->addPrimaryKey('id_reserva');
        $forge->createTable('reserva');

        // 3 horarios disponibles para usar en los tests
        $this->conn->table('horario')->insertBatch([
            ['horario' => '08:00'],
            ['horario' => '10:00'],
            ['horario' => '12:00'],
        ]);

        $this->model = new ReservaModel();
    }

    protected function tearDown(): void
    {
        $forge = \Config\Database::forge('tests');
        $forge->dropTable('reserva', true);
        $forge->dropTable('horario', true);

        parent::tearDown();
    }

    // Helper para insertar una reserva directamente en la BD
    private function insertarReserva(string $fecha, int $idRecinto, int $idHorario, string $estado = 'pendiente'): int
    {
        $this->conn->table('reserva')->insert([
            'fecha_reserva'  => $fecha,
            'monto'          => '1000.00',
            'estado_reserva' => $estado,
            'estado_pago'    => 'pendiente',
            'id_horario'     => $idHorario,
            'id_cliente'     => 1,
            'id_recinto'     => $idRecinto,
            'id_usuario'     => 1,
        ]);
        return $this->conn->insertID();
    }

    // ---------------------------------------------------------------
    // Sin reservas: el horario no está ocupado
    // ---------------------------------------------------------------
    public function testHorarioLibreSinReservas(): void
    {
        $ocupado = $this->model->estaOcupado('2027-08-01', 1, 1);

        $this->assertFalse($ocupado);
    }

    // ---------------------------------------------------------------
    // Al reservar un horario, queda ocupado
    // ---------------------------------------------------------------
    public function testHorarioQuedaOcupadoAlReservar(): void
    {
        $this->insertarReserva('2027-08-01', 1, 1);

        $this->assertTrue($this->model->estaOcupado('2027-08-01', 1, 1));
    }

    // ---------------------------------------------------------------
    // Las horas disponibles disminuyen con cada reserva
    // (múltiples operaciones — validación de consistencia)
    // ---------------------------------------------------------------
    public function testHorasDisponiblesDisminuyenConCadaReserva(): void
    {
        $fecha    = '2027-08-01';
        $recinto  = 1;

        // Sin reservas: los 3 horarios están disponibles
        $disponibles = $this->model->horasDisponibles($fecha, $recinto);
        $this->assertCount(3, $disponibles);

        // Primera reserva
        $this->insertarReserva($fecha, $recinto, 1);
        $disponibles = $this->model->horasDisponibles($fecha, $recinto);
        $this->assertCount(2, $disponibles);

        // Segunda reserva
        $this->insertarReserva($fecha, $recinto, 2);
        $disponibles = $this->model->horasDisponibles($fecha, $recinto);
        $this->assertCount(1, $disponibles);

        // Tercera reserva
        $this->insertarReserva($fecha, $recinto, 3);
        $disponibles = $this->model->horasDisponibles($fecha, $recinto);
        $this->assertCount(0, $disponibles);
    }

    // ---------------------------------------------------------------
    // Cancelar una reserva libera el horario
    // ---------------------------------------------------------------
    public function testCancelarReservaLiberaElHorario(): void
    {
        $fecha   = '2027-08-01';
        $recinto = 1;

        $id = $this->insertarReserva($fecha, $recinto, 1);

        // Antes de cancelar: ocupado
        $this->assertTrue($this->model->estaOcupado($fecha, $recinto, 1));

        $resultado = $this->model->cancelarReserva($id);

        $this->assertTrue($resultado['ok']);
        // Después de cancelar: libre
        $this->assertFalse($this->model->estaOcupado($fecha, $recinto, 1));
    }

    // ---------------------------------------------------------------
    // No se puede cancelar una reserva que ya está cancelada
    // ---------------------------------------------------------------
    public function testNoPuedeCancelarReservaYaCancelada(): void
    {
        $id = $this->insertarReserva('2027-08-01', 1, 1, 'cancelada');

        $resultado = $this->model->cancelarReserva($id);

        $this->assertFalse($resultado['ok']);
        $this->assertEquals('La reserva ya está cancelada.', $resultado['mensaje']);
    }

    // ---------------------------------------------------------------
    // Reservas de distintos recintos no se bloquean entre sí
    // ---------------------------------------------------------------
    public function testReservasDeDistintosRecintosNoColisionan(): void
    {
        $fecha = '2027-08-01';

        $this->insertarReserva($fecha, 1, 1);
        $this->insertarReserva($fecha, 2, 1);

        // Mismo horario, distintos recintos → ambos ocupados por separado
        $this->assertTrue($this->model->estaOcupado($fecha, 1, 1));
        $this->assertTrue($this->model->estaOcupado($fecha, 2, 1));

        // Pero el horario 2 del recinto 1 sigue libre
        $this->assertFalse($this->model->estaOcupado($fecha, 1, 2));
    }
}
