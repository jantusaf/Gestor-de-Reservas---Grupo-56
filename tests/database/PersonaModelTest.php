<?php

use App\Models\PersonaModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class PersonaModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = false;
    protected $refresh = false;

    private PersonaModel $model;
    private $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetServices(false);

        $this->conn = \Config\Database::connect('tests');
        $forge      = \Config\Database::forge('tests');

        $forge->dropTable('persona', true);
        $forge->addField([
            'id_persona'       => ['type' => 'INT', 'auto_increment' => true],
            'dni'              => ['type' => 'VARCHAR', 'constraint' => 20],
            'nombre'           => ['type' => 'VARCHAR', 'constraint' => 50],
            'apellido'         => ['type' => 'VARCHAR', 'constraint' => 50],
            'fecha_nacimiento' => ['type' => 'DATE'],
            'telefono'         => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'calle'            => ['type' => 'VARCHAR', 'constraint' => 50],
            'altura'           => ['type' => 'VARCHAR', 'constraint' => 10],
        ]);
        $forge->addPrimaryKey('id_persona');
        $forge->createTable('persona');

        $this->model = new PersonaModel();
    }

    protected function tearDown(): void
    {
        \Config\Database::forge('tests')->dropTable('persona', true);
        parent::tearDown();
    }

    private function insertarPersonaDirecto(string $dni = '12300001'): int
    {
        $this->conn->table('persona')->insert([
            'dni'              => $dni,
            'nombre'           => 'Test',
            'apellido'         => 'Persona',
            'fecha_nacimiento' => '1990-01-01',
            'telefono'         => '',
            'calle'            => 'Falsa',
            'altura'           => '123',
        ]);
        return $this->conn->insertID();
    }

    // ---------------------------------------------------------------
    // Alta exitosa guarda el registro en la BD
    // ---------------------------------------------------------------
    public function testAltaPersonaExitosa(): void
    {
        $resultado = $this->model->altaPersona(
            '12345678', 'Juan', 'Perez', '2000-05-10', '3516000000', 'San Martin', '123'
        );

        $this->assertTrue($resultado['ok']);
        $this->assertIsInt($resultado['id']);

        $persona = $this->model->find($resultado['id']);
        $this->assertEquals('12345678', $persona['dni']);
        $this->assertEquals('Juan',     $persona['nombre']);
    }

    // ---------------------------------------------------------------
    // Alta múltiple — consistencia de datos
    // ---------------------------------------------------------------
    public function testAltaMultiplesPersonas(): void
    {
        $this->model->altaPersona('11111111', 'Ana',   'Lopez',  '1995-03-20', '', 'Belgrano', '10');
        $this->model->altaPersona('22222222', 'Pedro', 'Garcia', '1988-07-15', '', 'Rivadavia', '200');
        $this->model->altaPersona('33333333', 'Maria', 'Torres', '2001-11-01', '', 'Mitre', '55');

        $personas = $this->model->findAll();

        $this->assertCount(3, $personas);
        $this->assertEquals('11111111', $personas[0]['dni']);
        $this->assertEquals('22222222', $personas[1]['dni']);
        $this->assertEquals('33333333', $personas[2]['dni']);
    }

    // ---------------------------------------------------------------
    // DNI duplicado devuelve error
    // ---------------------------------------------------------------
    public function testDniDuplicadoDevuelveError(): void
    {
        $this->model->altaPersona('99999999', 'Luis', 'Diaz', '1990-01-01', '', 'Colon', '5');

        $resultado = $this->model->altaPersona('99999999', 'Carlos', 'Ruiz', '1985-06-20', '', 'Paz', '8');

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('dni', $resultado['errores']);
    }

    // ---------------------------------------------------------------
    // DNI no numérico devuelve error
    // ---------------------------------------------------------------
    public function testDniNoNumericoDevuelveError(): void
    {
        $resultado = $this->model->altaPersona(
            'ABCDEFGH', 'Luis', 'Diaz', '1990-01-01', '', 'Colon', '5'
        );

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('dni', $resultado['errores']);
    }

    // ---------------------------------------------------------------
    // Nombre con números devuelve error
    // ---------------------------------------------------------------
    public function testNombreConNumerosDevuelveError(): void
    {
        $resultado = $this->model->altaPersona(
            '44444444', 'Juan123', 'Perez', '2000-01-01', '', 'Lavalle', '7'
        );

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('nombre', $resultado['errores']);
    }

    // ---------------------------------------------------------------
    // Fecha de nacimiento futura devuelve error
    // ---------------------------------------------------------------
    public function testFechaNacimientoFuturaDevuelveError(): void
    {
        $resultado = $this->model->altaPersona(
            '55555555', 'Juan', 'Perez', '2099-01-01', '', 'Lavalle', '7'
        );

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('fecha_nacimiento', $resultado['errores']);
    }

    // ---------------------------------------------------------------
    // Actualización exitosa modifica los datos
    // ---------------------------------------------------------------
    public function testActualizarPersonaExitoso(): void
    {
        $id  = $this->insertarPersonaDirecto('66666666');
        $row = $this->conn->table('persona')->where('id_persona', $id)->get()->getRowArray();
        $this->assertNotNull($row, "El registro insertado no existe (id=$id)");

        $resultado = $this->model->actualizarPersona(
            $id, '66666666', 'Juan', 'Gomez', '2000-01-01', '', 'Nueva', '99'
        );

        $this->assertTrue($resultado['ok'], json_encode($resultado['errores'] ?? []));

        $persona = $this->model->find($id);
        $this->assertEquals('Gomez', $persona['apellido']);
        $this->assertEquals('Nueva', $persona['calle']);
    }

    // ---------------------------------------------------------------
    // Actualizar con el mismo DNI no falla (is_unique con exclusión)
    // ---------------------------------------------------------------
    public function testActualizarConMismoDniNoFalla(): void
    {
        $id = $this->insertarPersonaDirecto('77777777');

        $resultado = $this->model->actualizarPersona(
            $id, '77777777', 'Ana', 'Lopez', '1995-01-01', '', 'Mitre', '3'
        );

        $this->assertTrue($resultado['ok']);
    }
}
