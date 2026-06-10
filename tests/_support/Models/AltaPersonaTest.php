<?php

namespace Tests\Support\Models;

use App\Models\PersonaModel;
use App\Entities\Persona;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\Database\Seeds\ClienteSeeder;

/**
 * Pruebas Unitarias - Alta de Persona
 * Método: PersonaModel::altaPersona(Persona $persona)
 *
 * El seeder ya insertó una persona con dni 99999999 (para probar el DNI duplicado).
 */
class AltaPersonaTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $seed    = ClienteSeeder::class;
    protected $refresh = true;

    private PersonaModel $personaModel;

    protected function setUp(): void
    {
        parent::setUp();
        // El servicio de validación es compartido durante todo el proceso de PHPUnit
        // y acumula errores entre tests. Lo reseteamos para que cada prueba parta limpia
        // y el script sea ejecutable múltiples veces sin contaminación de estado.
        \Config\Services::validation()->reset();
        $this->personaModel = PersonaModel::getInstance();
    }

    // ================================================================
    // CAMINO FELIZ
    // ================================================================

    /**
     * @test
     * @testdox Todos los datos válidos - Persona registrada correctamente
     */
    public function altaPersona_TodosLosDatosValidos()
    {
        $resultado = $this->personaModel->altaPersona($this->personaValida());

        $this->assertTrue($resultado['ok']);
        $this->assertArrayHasKey('id', $resultado);
    }

    // ================================================================
    // VALIDACIONES DE DNI
    // ================================================================

    /**
     * @test
     * @testdox DNI vacío - Retorna error
     */
    public function altaPersona_DniVacio_RetornaError()
    {
        $resultado = $this->personaModel->altaPersona($this->persona(['dni' => '']));

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('dni', $resultado['errores']);
    }

    /**
     * @test
     * @testdox DNI con letras - Retorna error
     */
    public function altaPersona_DniConLetras_RetornaError()
    {
        $resultado = $this->personaModel->altaPersona($this->persona(['dni' => 'abc123']));

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('dni', $resultado['errores']);
    }

    /**
     * @test
     * @testdox DNI menor a 7 dígitos - Retorna error
     */
    public function altaPersona_DniMenorA7Digitos_RetornaError()
    {
        $resultado = $this->personaModel->altaPersona($this->persona(['dni' => '123']));

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('dni', $resultado['errores']);
    }

    /**
     * @test
     * @testdox DNI ya registrado - Retorna error
     */
    public function altaPersona_DniDuplicado_RetornaError()
    {
        // El seeder ya insertó una persona con dni 99999999.
        $resultado = $this->personaModel->altaPersona($this->persona(['dni' => '99999999']));

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('dni', $resultado['errores']);
    }

    // ================================================================
    // VALIDACIONES DE NOMBRE
    // ================================================================

    /**
     * @test
     * @testdox Nombre vacío - Retorna error
     */
    public function altaPersona_NombreVacio_RetornaError()
    {
        $resultado = $this->personaModel->altaPersona($this->persona(['nombre' => '']));

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('nombre', $resultado['errores']);
    }

    /**
     * @test
     * @testdox Nombre menor a 3 caracteres - Retorna error
     */
    public function altaPersona_NombreMenorA3Caracteres_RetornaError()
    {
        $resultado = $this->personaModel->altaPersona($this->persona(['nombre' => 'Ab']));

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('nombre', $resultado['errores']);
    }

    /**
     * @test
     * @testdox Nombre con números - Retorna error
     */
    public function altaPersona_NombreConNumeros_RetornaError()
    {
        $resultado = $this->personaModel->altaPersona($this->persona(['nombre' => 'Juan123']));

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('nombre', $resultado['errores']);
    }

    // ================================================================
    // VALIDACIONES DE APELLIDO
    // ================================================================

    /**
     * @test
     * @testdox Apellido vacío - Retorna error
     */
    public function altaPersona_ApellidoVacio_RetornaError()
    {
        $resultado = $this->personaModel->altaPersona($this->persona(['apellido' => '']));

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('apellido', $resultado['errores']);
    }

    /**
     * @test
     * @testdox Apellido menor a 3 caracteres - Retorna error
     */
    public function altaPersona_ApellidoMenorA3Caracteres_RetornaError()
    {
        $resultado = $this->personaModel->altaPersona($this->persona(['apellido' => 'Pe']));

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('apellido', $resultado['errores']);
    }

    // ================================================================
    // VALIDACIONES DE FECHA DE NACIMIENTO
    // ================================================================

    /**
     * @test
     * @testdox Fecha de nacimiento vacía - Retorna error
     */
    public function altaPersona_FechaNacimientoVacia_RetornaError()
    {
        $resultado = $this->personaModel->altaPersona($this->persona(['fecha_nacimiento' => '']));

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('fecha_nacimiento', $resultado['errores']);
    }

    /**
     * @test
     * @testdox Fecha de nacimiento futura - Retorna error
     */
    public function altaPersona_FechaNacimientoFutura_RetornaError()
    {
        $resultado = $this->personaModel->altaPersona($this->persona(['fecha_nacimiento' => '2030-01-01']));

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('fecha_nacimiento', $resultado['errores']);
    }

    // ================================================================
    // VALIDACIONES DE TELÉFONO
    // ================================================================

    /**
     * @test
     * @testdox Teléfono con letras - Retorna error
     */
    public function altaPersona_TelefonoConLetras_RetornaError()
    {
        $resultado = $this->personaModel->altaPersona($this->persona(['telefono' => '379412345a']));

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('telefono', $resultado['errores']);
    }

    /**
     * @test
     * @testdox Teléfono menor a 6 dígitos - Retorna error
     */
    public function altaPersona_TelefonoMenorA6Digitos_RetornaError()
    {
        $resultado = $this->personaModel->altaPersona($this->persona(['telefono' => '12']));

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('telefono', $resultado['errores']);
    }

    // ================================================================
    // VALIDACIONES DE CALLE Y ALTURA
    // ================================================================

    /**
     * @test
     * @testdox Calle vacía - Retorna error
     */
    public function altaPersona_CalleVacia_RetornaError()
    {
        $resultado = $this->personaModel->altaPersona($this->persona(['calle' => '']));

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('calle', $resultado['errores']);
    }

    /**
     * @test
     * @testdox Altura vacía - Retorna error
     */
    public function altaPersona_AlturaVacia_RetornaError()
    {
        $resultado = $this->personaModel->altaPersona($this->persona(['altura' => '']));

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('altura', $resultado['errores']);
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

    private function persona(array $overrides): Persona
    {
        return new Persona(array_merge($this->datosPersonaValida(), $overrides));
    }
}
