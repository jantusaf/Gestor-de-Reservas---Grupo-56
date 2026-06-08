<?php

use App\Models\RecintoModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class RecintoModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = false;
    protected $refresh = false;

    private RecintoModel $model;
    private $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetServices(false);

        $this->conn = \Config\Database::connect('tests');
        $forge      = \Config\Database::forge('tests');

        $forge->dropTable('recinto',      true);
        $forge->dropTable('tipo_recinto', true);

        $forge->addField([
            'id_tipo_recinto'     => ['type' => 'INT', 'auto_increment' => true],
            'nombre_tipo_recinto' => ['type' => 'VARCHAR', 'constraint' => 100],
        ]);
        $forge->addPrimaryKey('id_tipo_recinto');
        $forge->createTable('tipo_recinto');

        $forge->addField([
            'id_recinto'      => ['type' => 'INT', 'auto_increment' => true],
            'tarifa'          => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'descripcion'     => ['type' => 'VARCHAR', 'constraint' => 50],
            'estado_recinto'  => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'activo'],
            'id_tipo_recinto' => ['type' => 'INT'],
        ]);
        $forge->addPrimaryKey('id_recinto');
        $forge->createTable('recinto');

        $this->conn->table('tipo_recinto')->insertBatch([
            ['nombre_tipo_recinto' => 'Deportivo'],
            ['nombre_tipo_recinto' => 'Social'],
        ]);

        $this->model = new RecintoModel();
    }

    protected function tearDown(): void
    {
        $forge = \Config\Database::forge('tests');
        $forge->dropTable('recinto',      true);
        $forge->dropTable('tipo_recinto', true);
        parent::tearDown();
    }

    private function insertarRecintoDirecto(string $tarifa, string $descripcion, int $tipo = 1, string $estado = 'activo'): int
    {
        $this->conn->table('recinto')->insert([
            'tarifa'          => $tarifa,
            'descripcion'     => $descripcion,
            'estado_recinto'  => $estado,
            'id_tipo_recinto' => $tipo,
        ]);
        return $this->conn->insertID();
    }

    // ---------------------------------------------------------------
    // Alta múltiple: verifica que cada recinto se guarda correctamente
    // ---------------------------------------------------------------
    public function testAltaMultipleRecintos(): void
    {
        $this->model->altaRecinto('1500.00', 'Cancha de futbol', 1);
        $this->model->altaRecinto('2000.50', 'Salon de eventos', 1);
        $this->model->altaRecinto('800.00',  'Sala de reuniones', 2);

        $recintos = $this->model->findAll();

        $this->assertCount(3, $recintos);
        $this->assertEquals('1500.00', $recintos[0]['tarifa']);
        $this->assertEquals('2000.50', $recintos[1]['tarifa']);
        $this->assertEquals('800.00',  $recintos[2]['tarifa']);
    }

    // ---------------------------------------------------------------
    // Actualización múltiple: verifica que solo cambia el registro
    // correcto y no afecta a los demás
    // ---------------------------------------------------------------
    public function testActualizacionMultipleNoAfectaOtros(): void
    {
        $idA = $this->insertarRecintoDirecto('1000.00', 'Recinto A');
        $idB = $this->insertarRecintoDirecto('2000.00', 'Recinto B');

        $this->model->actualizarRecinto($idA, '1100.00', 'Recinto A v2',    1, 'activo');
        $this->model->actualizarRecinto($idA, '1200.00', 'Recinto A v3',    1, 'activo');
        $this->model->actualizarRecinto($idA, '1300.00', 'Recinto A final', 1, 'inactivo');

        $recintoA = $this->model->find($idA);
        $recintoB = $this->model->find($idB);

        $this->assertEquals('1300.00',         $recintoA['tarifa']);
        $this->assertEquals('Recinto A final', $recintoA['descripcion']);
        $this->assertEquals('inactivo',        $recintoA['estado_recinto']);

        $this->assertEquals('2000.00',  $recintoB['tarifa']);
        $this->assertEquals('Recinto B', $recintoB['descripcion']);
        $this->assertEquals('activo',    $recintoB['estado_recinto']);
    }

    // ---------------------------------------------------------------
    // Validación: campo tarifa vacío
    // ---------------------------------------------------------------
    public function testTarifaVaciaDevuelveError(): void
    {
        $resultado = $this->model->altaRecinto('', 'Cancha', 1);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('tarifa', $resultado['errores']);
        $this->assertEquals(
            'El campo Tarifa por hora es obligatorio.',
            $resultado['errores']['tarifa']
        );
    }

    // ---------------------------------------------------------------
    // Validación: tarifa con texto no numérico
    // ---------------------------------------------------------------
    public function testTarifaNoNumericaDevuelveError(): void
    {
        $resultado = $this->model->altaRecinto('abc', 'Cancha', 1);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('tarifa', $resultado['errores']);
        $this->assertEquals(
            'La tarifa debe ser un número.',
            $resultado['errores']['tarifa']
        );
    }

    // ---------------------------------------------------------------
    // Deshabilitar recinto cambia el estado a inactivo
    // ---------------------------------------------------------------
    public function testDeshabilitarRecinto(): void
    {
        $id = $this->insertarRecintoDirecto('500.00', 'Cancha Baja');

        $resultado = $this->model->deshabilitar($id);

        $this->assertTrue($resultado['ok']);
        $this->assertEquals('inactivo', $this->model->find($id)['estado_recinto']);
    }

    // ---------------------------------------------------------------
    // Habilitar recinto cambia el estado a activo
    // ---------------------------------------------------------------
    public function testHabilitarRecinto(): void
    {
        $id = $this->insertarRecintoDirecto('500.00', 'Cancha Sube', 1, 'inactivo');

        $resultado = $this->model->habilitar($id);

        $this->assertTrue($resultado['ok']);
        $this->assertEquals('activo', $this->model->find($id)['estado_recinto']);
    }

    // ---------------------------------------------------------------
    // Deshabilitar recinto inexistente devuelve error
    // ---------------------------------------------------------------
    public function testDeshabilitarRecintoInexistenteDevuelveError(): void
    {
        $resultado = $this->model->deshabilitar(9999);

        $this->assertFalse($resultado['ok']);
    }
}
