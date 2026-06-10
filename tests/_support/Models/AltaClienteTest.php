<?php

namespace Tests\Support\Models;

use App\Models\ClienteModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\Database\Seeds\ClienteSeeder;

/**
 * Pruebas Unitarias - Alta de Cliente
 * Método: ClienteModel::altaCliente(string $email, int $idPersona)
 *
 * El seeder deja:
 *   - una persona (id_persona = 1)
 *   - un cliente con email 'duplicado@gmail.com' (para probar el email duplicado)
 */
class AltaClienteTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $seed    = ClienteSeeder::class;
    protected $refresh = true;

    private ClienteModel $clienteModel;

    // Persona existente sembrada por el seeder.
    private const ID_PERSONA_EXISTENTE = 1;
    // Id de persona que no existe en la base.
    private const ID_PERSONA_INEXISTENTE = 99;

    protected function setUp(): void
    {
        parent::setUp();
        // El servicio de validación es compartido durante todo el proceso de PHPUnit
        // y acumula errores entre tests. Lo reseteamos para que cada prueba parta limpia
        // y el script sea ejecutable múltiples veces sin contaminación de estado.
        \Config\Services::validation()->reset();
        $this->clienteModel = new ClienteModel();
    }

    // ================================================================
    // CAMINO FELIZ
    // ================================================================

    /**
     * @test
     * @testdox Email válido e idPersona existente - Cliente registrado correctamente
     */
    public function altaCliente_DatosValidos()
    {
        $resultado = $this->clienteModel->altaCliente('juan@gmail.com', self::ID_PERSONA_EXISTENTE);

        $this->assertTrue($resultado['ok']);
        $this->assertArrayHasKey('id', $resultado);
    }

    // ================================================================
    // VALIDACIONES DE EMAIL
    // ================================================================

    /**
     * @test
     * @testdox Email vacío - Retorna error
     */
    public function altaCliente_EmailVacio_RetornaError()
    {
        $resultado = $this->clienteModel->altaCliente('', self::ID_PERSONA_EXISTENTE);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('email', $resultado['errores']);
    }

    /**
     * @test
     * @testdox Email con formato inválido - Retorna error
     */
    public function altaCliente_EmailFormatoInvalido_RetornaError()
    {
        $resultado = $this->clienteModel->altaCliente('emailinvalido', self::ID_PERSONA_EXISTENTE);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('email', $resultado['errores']);
    }

    /**
     * @test
     * @testdox Email ya registrado - Retorna error
     */
    public function altaCliente_EmailDuplicado_RetornaError()
    {
        // 'duplicado@gmail.com' ya fue insertado por el seeder.
        $resultado = $this->clienteModel->altaCliente('duplicado@gmail.com', self::ID_PERSONA_EXISTENTE);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('email', $resultado['errores']);
    }

    // ================================================================
    // VALIDACIÓN DE LA PERSONA
    // ================================================================

    /**
     * @test
     * @testdox Persona no registrada en BD - No se puede asignar como cliente
     */
    public function altaCliente_PersonaInexistente_RetornaError()
    {
        $resultado = $this->clienteModel->altaCliente('juan@gmail.com', self::ID_PERSONA_INEXISTENTE);

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('id_persona', $resultado['errores']);
    }
}
