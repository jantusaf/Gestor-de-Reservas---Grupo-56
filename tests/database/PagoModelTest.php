<?php

use App\Models\PagoModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class PagoModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = false;
    protected $refresh = false;

    private PagoModel $model;
    private $conn;

    protected function setUp(): void
    {
        parent::setUp();

        $this->conn = \Config\Database::connect('tests');
        $forge      = \Config\Database::forge('tests');

        $forge->dropTable('pago',       true);
        $forge->dropTable('reserva',    true);
        $forge->dropTable('medio_pago', true);

        $forge->addField([
            'id_medio_pago'     => ['type' => 'INT', 'auto_increment' => true],
            'nombre_medio_pago' => ['type' => 'VARCHAR', 'constraint' => 50],
        ]);
        $forge->addPrimaryKey('id_medio_pago');
        $forge->createTable('medio_pago');

        $forge->addField([
            'id_reserva'     => ['type' => 'INT', 'auto_increment' => true],
            'fecha_reserva'  => ['type' => 'DATE'],
            'monto'          => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => '0.00'],
            'estado_reserva' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pendiente'],
            'estado_pago'    => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pendiente'],
            'id_horario'     => ['type' => 'INT', 'default' => 1],
            'id_cliente'     => ['type' => 'INT', 'default' => 1],
            'id_recinto'     => ['type' => 'INT', 'default' => 1],
            'id_usuario'     => ['type' => 'INT', 'default' => 1],
        ]);
        $forge->addPrimaryKey('id_reserva');
        $forge->createTable('reserva');

        $forge->addField([
            'id_pago'       => ['type' => 'INT', 'auto_increment' => true],
            'fecha_pago'    => ['type' => 'DATE'],
            'monto_total'   => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'id_reserva'    => ['type' => 'INT'],
            'id_medio_pago' => ['type' => 'INT'],
            'id_usuario'    => ['type' => 'INT'],
        ]);
        $forge->addPrimaryKey('id_pago');
        $forge->createTable('pago');

        // Medio de pago y reserva base
        $this->conn->table('medio_pago')->insert(['nombre_medio_pago' => 'Efectivo']);
        $this->conn->table('reserva')->insert([
            'fecha_reserva'  => '2027-08-01',
            'monto'          => '1500.00',
            'estado_reserva' => 'pendiente',
            'estado_pago'    => 'pendiente',
        ]);

        $this->model = new PagoModel();
    }

    protected function tearDown(): void
    {
        $forge = \Config\Database::forge('tests');
        $forge->dropTable('pago',       true);
        $forge->dropTable('reserva',    true);
        $forge->dropTable('medio_pago', true);
        parent::tearDown();
    }

    private function idReserva(): int
    {
        return (int) $this->conn->table('reserva')->select('id_reserva')->get()->getRowArray()['id_reserva'];
    }

    // ---------------------------------------------------------------
    // Pago exitoso cambia el estado de la reserva a confirmada/pagada
    // ---------------------------------------------------------------
    public function testAltaPagoExitosaCambiaEstadoReserva(): void
    {
        $idReserva = $this->idReserva();

        $resultado = $this->model->altaPago($idReserva, 1500.00, 1, 1);

        $this->assertTrue($resultado['ok']);

        $reserva = \Config\Database::connect('tests')
            ->table('reserva')
            ->where('id_reserva', $idReserva)
            ->get()->getRowArray();

        $this->assertEquals('confirmada', $reserva['estado_reserva']);
        $this->assertEquals('pagada',     $reserva['estado_pago']);
    }

    // ---------------------------------------------------------------
    // Monto incorrecto devuelve error
    // ---------------------------------------------------------------
    public function testPagoConMontoIncorrectoDevuelveError(): void
    {
        $resultado = $this->model->altaPago($this->idReserva(), 999.00, 1, 1);

        $this->assertFalse($resultado['ok']);
        $this->assertEquals('El monto debe ser exactamente igual al de la reserva.', $resultado['mensaje']);
    }

    // ---------------------------------------------------------------
    // Reserva inexistente devuelve error
    // ---------------------------------------------------------------
    public function testPagoReservaInexistenteDevuelveError(): void
    {
        $resultado = $this->model->altaPago(9999, 1500.00, 1, 1);

        $this->assertFalse($resultado['ok']);
        $this->assertEquals('Reserva no encontrada.', $resultado['mensaje']);
    }

    // ---------------------------------------------------------------
    // No se puede pagar una reserva ya pagada
    // ---------------------------------------------------------------
    public function testNoPuedePagarReservaYaPagada(): void
    {
        $idReserva = $this->idReserva();

        $this->model->altaPago($idReserva, 1500.00, 1, 1);

        // Intentar pagar de nuevo
        $resultado = $this->model->altaPago($idReserva, 1500.00, 1, 1);

        $this->assertFalse($resultado['ok']);
        $this->assertEquals('Esta reserva ya fue pagada.', $resultado['mensaje']);
    }

    // ---------------------------------------------------------------
    // Múltiples pagos de distintas reservas son independientes
    // ---------------------------------------------------------------
    public function testMultiplesPagosDeDistintasReservas(): void
    {
        // Insertar una segunda reserva
        $this->conn->table('reserva')->insert([
            'fecha_reserva'  => '2027-08-02',
            'monto'          => '2000.00',
            'estado_reserva' => 'pendiente',
            'estado_pago'    => 'pendiente',
        ]);

        $reservas  = $this->conn->table('reserva')->orderBy('id_reserva', 'ASC')->get()->getResultArray();
        $idReserva1 = (int) $reservas[0]['id_reserva'];
        $idReserva2 = (int) $reservas[1]['id_reserva'];

        $this->model->altaPago($idReserva1, 1500.00, 1, 1);
        $this->model->altaPago($idReserva2, 2000.00, 1, 1);

        $pagos = $this->conn->table('pago')->get()->getResultArray();
        $this->assertCount(2, $pagos);
        $this->assertEquals('1500.00', $pagos[0]['monto_total']);
        $this->assertEquals('2000.00', $pagos[1]['monto_total']);
    }
}
