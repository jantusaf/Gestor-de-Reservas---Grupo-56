<?php

use App\Models\ClienteModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class ClienteModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = false;
    protected $refresh = false;

    private ClienteModel $model;
    private $conn;

    protected function setUp(): void
    {
        parent::setUp();

        $this->conn = \Config\Database::connect('tests');
        $forge      = \Config\Database::forge('tests');

        $forge->dropTable('cliente', true);
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

        $forge->addField([
            'id_cliente'     => ['type' => 'INT', 'auto_increment' => true],
            'email'          => ['type' => 'VARCHAR', 'constraint' => 100],
            'fecha_alta'     => ['type' => 'DATE'],
            'estado_cliente' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'activo'],
            'id_persona'     => ['type' => 'INT'],
        ]);
        $forge->addPrimaryKey('id_cliente');
        $forge->createTable('cliente');

        $this->conn->table('persona')->insert([
            'dni' => '10000001', 'nombre' => 'Test', 'apellido' => 'Cliente',
            'fecha_nacimiento' => '1990-01-01', 'calle' => 'Falsa', 'altura' => '123',
        ]);

        $this->model = new ClienteModel();
    }

    protected function tearDown(): void
    {
        $forge = \Config\Database::forge('tests');
        $forge->dropTable('cliente', true);
        $forge->dropTable('persona', true);
        parent::tearDown();
    }

    private function idPersona(): int
    {
        return (int) $this->conn->table('persona')
            ->select('id_persona')->get()->getRowArray()['id_persona'];
    }

    private function insertarClienteDirecto(string $email, int $idPersona = 1): int
    {
        $this->conn->table('cliente')->insert([
            'email'          => $email,
            'fecha_alta'     => date('Y-m-d'),
            'estado_cliente' => 'activo',
            'id_persona'     => $idPersona,
        ]);
        return $this->conn->insertID();
    }

    // ---------------------------------------------------------------
    // Alta exitosa guarda el cliente en la BD
    // ---------------------------------------------------------------
    public function testAltaClienteExitosa(): void
    {
        $resultado = $this->model->altaCliente('cliente@test.com', $this->idPersona());

        $this->assertTrue($resultado['ok']);

        $cliente = $this->model->find($resultado['id']);
        $this->assertEquals('cliente@test.com', $cliente['email']);
        $this->assertEquals('activo', $cliente['estado_cliente']);
    }

    // ---------------------------------------------------------------
    // Email inválido devuelve error
    // ---------------------------------------------------------------
    public function testEmailInvalidoDevuelveError(): void
    {
        $resultado = $this->model->altaCliente('no-es-un-email', $this->idPersona());

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('email', $resultado['errores']);
    }

    // ---------------------------------------------------------------
    // Email duplicado devuelve error
    // ---------------------------------------------------------------
    public function testEmailDuplicadoDevuelveError(): void
    {
        $this->model->altaCliente('repetido@test.com', $this->idPersona());

        $resultado = $this->model->altaCliente('repetido@test.com', $this->idPersona());

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('email', $resultado['errores']);
    }

    // ---------------------------------------------------------------
    // Alta múltiple — cada cliente queda guardado
    // ---------------------------------------------------------------
    public function testAltaMultiplesClientes(): void
    {
        $this->insertarClienteDirecto('uno@test.com',  1);
        $this->insertarClienteDirecto('dos@test.com',  1);
        $this->insertarClienteDirecto('tres@test.com', 1);

        $this->assertCount(3, $this->model->findAll());
    }

    // ---------------------------------------------------------------
    // Deshabilitar cambia el estado a inactivo
    // ---------------------------------------------------------------
    public function testDeshabilitarCliente(): void
    {
        $id = $this->insertarClienteDirecto('baja@test.com');

        $resultado = $this->model->deshabilitar($id);

        $this->assertTrue($resultado['ok']);
        $this->assertEquals('inactivo', $this->model->find($id)['estado_cliente']);
    }

    // ---------------------------------------------------------------
    // Habilitar cambia el estado a activo
    // ---------------------------------------------------------------
    public function testHabilitarCliente(): void
    {
        $id = $this->insertarClienteDirecto('alta@test.com');
        $this->model->deshabilitar($id);

        $resultado = $this->model->habilitar($id);

        $this->assertTrue($resultado['ok']);
        $this->assertEquals('activo', $this->model->find($id)['estado_cliente']);
    }

    // ---------------------------------------------------------------
    // Deshabilitar cliente inexistente devuelve error
    // ---------------------------------------------------------------
    public function testDeshabilitarClienteInexistenteDevuelveError(): void
    {
        $resultado = $this->model->deshabilitar(9999);

        $this->assertFalse($resultado['ok']);
    }
}
