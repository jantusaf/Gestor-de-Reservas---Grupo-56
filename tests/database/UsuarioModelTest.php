<?php

use App\Models\UsuarioModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class UsuarioModelTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = false;
    protected $refresh = false;

    private UsuarioModel $model;
    private $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetServices(false);

        $this->conn = \Config\Database::connect('tests');
        $forge      = \Config\Database::forge('tests');

        $forge->dropTable('usuario', true);
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
            'id_usuario'      => ['type' => 'INT', 'auto_increment' => true],
            'nombre_usuario'  => ['type' => 'VARCHAR', 'constraint' => 50],
            'contrasena'      => ['type' => 'VARCHAR', 'constraint' => 255],
            'estado_usuario'  => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'activo'],
            'id_persona'      => ['type' => 'INT'],
            'id_tipo_usuario' => ['type' => 'INT', 'default' => 2],
        ]);
        $forge->addPrimaryKey('id_usuario');
        $forge->createTable('usuario');

        $this->conn->table('persona')->insert([
            'dni' => '20000001', 'nombre' => 'Admin', 'apellido' => 'Test',
            'fecha_nacimiento' => '1990-06-15', 'calle' => 'Colon', 'altura' => '500',
        ]);

        $this->model = new UsuarioModel();
    }

    protected function tearDown(): void
    {
        $forge = \Config\Database::forge('tests');
        $forge->dropTable('usuario', true);
        $forge->dropTable('persona', true);
        parent::tearDown();
    }

    private function idPersona(): int
    {
        return (int) $this->conn->table('persona')
            ->select('id_persona')->get()->getRowArray()['id_persona'];
    }

    private function insertarUsuarioDirecto(string $nombreUsuario, string $clave = 'clave123'): int
    {
        $this->conn->table('usuario')->insert([
            'nombre_usuario'  => $nombreUsuario,
            'contrasena'      => password_hash($clave, PASSWORD_DEFAULT),
            'estado_usuario'  => 'activo',
            'id_persona'      => $this->idPersona(),
            'id_tipo_usuario' => 2,
        ]);
        return $this->conn->insertID();
    }

    // ---------------------------------------------------------------
    // Alta exitosa — guarda el usuario con contraseña hasheada
    // ---------------------------------------------------------------
    public function testAltaUsuarioExitosa(): void
    {
        $resultado = $this->model->altaUsuario('juanito', 'clave123', $this->idPersona());

        $this->assertTrue($resultado['ok']);

        $usuario = $this->model->find($resultado['id']);
        $this->assertEquals('juanito', $usuario['nombre_usuario']);
        $this->assertEquals('activo',  $usuario['estado_usuario']);
        $this->assertNotEquals('clave123', $usuario['contrasena']);
        $this->assertTrue(password_verify('clave123', $usuario['contrasena']));
    }

    // ---------------------------------------------------------------
    // Nombre de usuario duplicado devuelve error
    // ---------------------------------------------------------------
    public function testNombreUsuarioDuplicadoDevuelveError(): void
    {
        $this->insertarUsuarioDirecto('repetido');

        $resultado = $this->model->altaUsuario('repetido', 'otra123', $this->idPersona());

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('nombre_usuario', $resultado['errores']);
    }

    // ---------------------------------------------------------------
    // Contraseña menor a 6 caracteres devuelve error
    // ---------------------------------------------------------------
    public function testContrasenaMenorA6CaracteresDevuelveError(): void
    {
        $resultado = $this->model->altaUsuario('usuario1', '123', $this->idPersona());

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('contrasena', $resultado['errores']);
    }

    // ---------------------------------------------------------------
    // Inicio de sesión exitoso
    // ---------------------------------------------------------------
    public function testIniciarSesionExitoso(): void
    {
        $this->insertarUsuarioDirecto('operador', 'miClave99');

        $resultado = $this->model->iniciarSesion('20000001', 'miClave99');

        $this->assertTrue($resultado['ok']);
        $this->assertEquals('operador', $resultado['sesion']['nombre_usuario']);
        $this->assertTrue($resultado['sesion']['logged_in']);
    }

    // ---------------------------------------------------------------
    // DNI no encontrado devuelve error
    // ---------------------------------------------------------------
    public function testIniciarSesionDniIncorrectoDevuelveError(): void
    {
        $resultado = $this->model->iniciarSesion('99999999', 'clave123');

        $this->assertFalse($resultado['ok']);
        $this->assertEquals('DNI no encontrado.', $resultado['mensaje']);
    }

    // ---------------------------------------------------------------
    // Contraseña incorrecta devuelve error
    // ---------------------------------------------------------------
    public function testIniciarSesionContrasenaIncorrectaDevuelveError(): void
    {
        $this->insertarUsuarioDirecto('operador2', 'claveCorrecta');

        $resultado = $this->model->iniciarSesion('20000001', 'claveIncorrecta');

        $this->assertFalse($resultado['ok']);
        $this->assertEquals('Contraseña incorrecta.', $resultado['mensaje']);
    }

    // ---------------------------------------------------------------
    // Usuario inactivo no puede iniciar sesión
    // ---------------------------------------------------------------
    public function testIniciarSesionUsuarioInactivoDevuelveError(): void
    {
        $id = $this->insertarUsuarioDirecto('operador3', 'clave123');
        $this->model->deshabilitar($id);

        $resultado = $this->model->iniciarSesion('20000001', 'clave123');

        $this->assertFalse($resultado['ok']);
        $this->assertEquals('Usuario dado de baja.', $resultado['mensaje']);
    }

    // ---------------------------------------------------------------
    // Deshabilitar cambia el estado a inactivo
    // ---------------------------------------------------------------
    public function testDeshabilitarUsuario(): void
    {
        $id = $this->insertarUsuarioDirecto('operador4');

        $resultado = $this->model->deshabilitar($id);

        $this->assertTrue($resultado['ok']);
        $this->assertEquals('inactivo', $this->model->find($id)['estado_usuario']);
    }

    // ---------------------------------------------------------------
    // Habilitar cambia el estado a activo
    // ---------------------------------------------------------------
    public function testHabilitarUsuario(): void
    {
        $id = $this->insertarUsuarioDirecto('operador5');
        $this->model->deshabilitar($id);

        $resultado = $this->model->habilitar($id);

        $this->assertTrue($resultado['ok']);
        $this->assertEquals('activo', $this->model->find($id)['estado_usuario']);
    }

    // ---------------------------------------------------------------
    // Deshabilitar usuario inexistente devuelve error
    // ---------------------------------------------------------------
    public function testDeshabilitarUsuarioInexistenteDevuelveError(): void
    {
        $resultado = $this->model->deshabilitar(9999);

        $this->assertFalse($resultado['ok']);
    }
}
