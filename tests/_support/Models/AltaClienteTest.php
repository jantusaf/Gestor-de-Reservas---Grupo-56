<?php

namespace Tests\Support\Models;

use App\Models\ClienteModel;
use App\Entities\Persona;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\Database\Seeds\ClienteSeeder;

/**
 * Pruebas Unitarias - Alta de Cliente
 * Método: registrarCliente(Persona $persona, string $email)
 */
class AltaClienteTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $seed    = ClienteSeeder::class;
    protected $refresh = true;

    private ClienteModel $clienteModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clienteModel = new ClienteModel();
    }

    // ================================================================
    // CAMINO FELIZ
    // ================================================================

    /** @test */
    public function altaCliente_TodosLosDatosValidos()
    {
        // Arrange
        $persona = $this->personaValida();
        $email   = 'nuevo@gmail.com';

        // Act
        $resultado = $this->clienteModel->registrarCliente($persona, $email);

        // Assert
        $this->assertTrue($resultado['ok']);
        $this->assertArrayHasKey('id', $resultado);
    }

    // ================================================================
    // VALIDACIONES DE DNI
    // ================================================================

    /** @test */
    public function altaCliente_DniVacio_RetornaError()
    {
        // Arrange
        $persona = new Persona([...$this->datosPersonaValida(), 'dni' => '']);

        // Act
        $resultado = $this->clienteModel->registrarCliente($persona, 'nuevo@gmail.com');

        // Assert
        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('dni', $resultado['errores']);
    }

    /** @test */
    public function altaCliente_DniConLetras_RetornaError()
    {
        // Arrange
        $persona = new Persona([...$this->datosPersonaValida(), 'dni' => 'abc123']);

        // Act
        $resultado = $this->clienteModel->registrarCliente($persona, 'nuevo@gmail.com');

        // Assert
        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('dni', $resultado['errores']);
    }

    /** @test */
    public function altaCliente_DniMenorA7Digitos_RetornaError()
    {
        // Arrange
        $persona = new Persona([...$this->datosPersonaValida(), 'dni' => '123']);

        // Act
        $resultado = $this->clienteModel->registrarCliente($persona, 'nuevo@gmail.com');

        // Assert
        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('dni', $resultado['errores']);
    }

    /** @test */
    public function altaCliente_DniDuplicado_RetornaError()
    {
        // Arrange - el seeder ya insertó una persona con dni 99999999
        $persona = new Persona([...$this->datosPersonaValida(), 'dni' => '99999999']);

        // Act
        $resultado = $this->clienteModel->registrarCliente($persona, 'nuevo@gmail.com');

        // Assert
        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('dni', $resultado['errores']);
    }

    // ================================================================
    // VALIDACIONES DE NOMBRE
    // ================================================================

    /** @test */
    public function altaCliente_NombreVacio_RetornaError()
    {
        // Arrange
        $persona = new Persona([...$this->datosPersonaValida(), 'nombre' => '']);

        // Act
        $resultado = $this->clienteModel->registrarCliente($persona, 'nuevo@gmail.com');

        // Assert
        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('nombre', $resultado['errores']);
    }

    /** @test */
    public function altaCliente_NombreMenorA3Caracteres_RetornaError()
    {
        // Arrange
        $persona = new Persona([...$this->datosPersonaValida(), 'nombre' => 'Ab']);

        // Act
        $resultado = $this->clienteModel->registrarCliente($persona, 'nuevo@gmail.com');

        // Assert
        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('nombre', $resultado['errores']);
    }

    /** @test */
    public function altaCliente_NombreConNumeros_RetornaError()
    {
        // Arrange
        $persona = new Persona([...$this->datosPersonaValida(), 'nombre' => 'Juan123']);

        // Act
        $resultado = $this->clienteModel->registrarCliente($persona, 'nuevo@gmail.com');

        // Assert
        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('nombre', $resultado['errores']);
    }

    // ================================================================
    // VALIDACIONES DE APELLIDO
    // ================================================================

    /** @test */
    public function altaCliente_ApellidoVacio_RetornaError()
    {
        // Arrange
        $persona = new Persona([...$this->datosPersonaValida(), 'apellido' => '']);

        // Act
        $resultado = $this->clienteModel->registrarCliente($persona, 'nuevo@gmail.com');

        // Assert
        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('apellido', $resultado['errores']);
    }

    /** @test */
    public function altaCliente_ApellidoMenorA3Caracteres_RetornaError()
    {
        // Arrange
        $persona = new Persona([...$this->datosPersonaValida(), 'apellido' => 'Pe']);

        // Act
        $resultado = $this->clienteModel->registrarCliente($persona, 'nuevo@gmail.com');

        // Assert
        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('apellido', $resultado['errores']);
    }

    // ================================================================
    // VALIDACIONES DE FECHA DE NACIMIENTO
    // ================================================================

    /** @test */
    public function altaCliente_FechaNacimientoVacia_RetornaError()
    {
        // Arrange
        $persona = new Persona([...$this->datosPersonaValida(), 'fecha_nacimiento' => '']);

        // Act
        $resultado = $this->clienteModel->registrarCliente($persona, 'nuevo@gmail.com');

        // Assert
        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('fecha_nacimiento', $resultado['errores']);
    }

    /** @test */
    public function altaCliente_FechaNacimientoFutura_RetornaError()
    {
        // Arrange
        $persona = new Persona([...$this->datosPersonaValida(), 'fecha_nacimiento' => '2030-01-01']);

        // Act
        $resultado = $this->clienteModel->registrarCliente($persona, 'nuevo@gmail.com');

        // Assert
        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('fecha_nacimiento', $resultado['errores']);
    }

    // ================================================================
    // VALIDACIONES DE TELÉFONO
    // ================================================================

    /** @test */
    public function altaCliente_TelefonoMenorA7Digitos_RetornaError()
    {
        // Arrange
        $persona = new Persona([...$this->datosPersonaValida(), 'telefono' => '12']);

        // Act
        $resultado = $this->clienteModel->registrarCliente($persona, 'nuevo@gmail.com');

        // Assert
        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('telefono', $resultado['errores']);
    }

    // ================================================================
    // VALIDACIONES DE CALLE Y ALTURA
    // ================================================================

    /** @test */
    public function altaCliente_CalleVacia_RetornaError()
    {
        // Arrange
        $persona = new Persona([...$this->datosPersonaValida(), 'calle' => '']);

        // Act
        $resultado = $this->clienteModel->registrarCliente($persona, 'nuevo@gmail.com');

        // Assert
        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('calle', $resultado['errores']);
    }

    /** @test */
    public function altaCliente_AlturaVacia_RetornaError()
    {
        // Arrange
        $persona = new Persona([...$this->datosPersonaValida(), 'altura' => '']);

        // Act
        $resultado = $this->clienteModel->registrarCliente($persona, 'nuevo@gmail.com');

        // Assert
        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('altura', $resultado['errores']);
    }

    // ================================================================
    // VALIDACIONES DE EMAIL
    // ================================================================

    /** @test */
    public function altaCliente_EmailVacio_RetornaError()
    {
        // Arrange
        $persona = $this->personaValida();

        // Act
        $resultado = $this->clienteModel->registrarCliente($persona, '');

        // Assert
        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('email', $resultado['errores']);
    }

    /** @test */
    public function altaCliente_EmailFormatoInvalido_RetornaError()
    {
        // Arrange
        $persona = $this->personaValida();

        // Act
        $resultado = $this->clienteModel->registrarCliente($persona, 'emailinvalido');

        // Assert
        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('email', $resultado['errores']);
    }

    /** @test */
    public function altaCliente_EmailDuplicado_RetornaError()
    {
        // Arrange - el seeder ya insertó un cliente con duplicado@gmail.com
        $persona = $this->personaValida();

        // Act
        $resultado = $this->clienteModel->registrarCliente($persona, 'duplicado@gmail.com');

        // Assert
        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('email', $resultado['errores']);
    }

    // ================================================================
    // ROLLBACK
    // ================================================================

    /** @test */
    public function altaCliente_EmailDuplicado_PersonaNoSeGuarda()
    {
        // Arrange
        $persona = new Persona([
            'dni'              => '40999999',
            'nombre'           => 'Carlos',
            'apellido'         => 'López',
            'fecha_nacimiento' => '1988-07-20',
            'telefono'         => '3794999999',
            'calle'            => 'Corrientes',
            'altura'           => '789',
        ]);

        // Act - email duplicado del seeder
        $resultado = $this->clienteModel->registrarCliente($persona, 'duplicado@gmail.com');

        // Assert
        $this->assertFalse($resultado['ok']);
        $personaGuardada = (new \App\Models\PersonaModel)->where('dni', '40999999')->first();
        $this->assertNull($personaGuardada);
    }

    // ================================================================
    // HELPERS
    // ================================================================

    private function datosPersonaValida(): array
    {
        return [
            'dni'              => '30123456',
            'nombre'           => 'Juan',
            'apellido'         => 'Pérez',
            'fecha_nacimiento' => '1990-05-15',
            'telefono'         => '3794123456',
            'calle'            => 'San Martín',
            'altura'           => '123',
        ];
    }

    private function personaValida(): Persona
    {
        return new Persona($this->datosPersonaValida());
    }
}
