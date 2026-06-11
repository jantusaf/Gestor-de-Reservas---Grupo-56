<?php

namespace Tests\Support\Models;

use App\Models\PersonaModel;
use App\Entities\Persona;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Pruebas Unitarias - Alta de Persona
 * Método: PersonaModel::altaPersona(Persona $persona)
 *
 * Pruebas FANTASMAS: no se conectan a la base de datos.
 * Se mockean where(), first(), insert() y getInsertID() para simular
 * el comportamiento de guardado sin tocar la BD.
 */
class AltaPersonaTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \Config\Services::validation()->reset();
    }

    /**
     * Crea un mock parcial de PersonaModel con comportamiento configurable.
     *
     * @param array|null $firstReturn  Fila que devuelve first() (null = no hay duplicado).
     */
    private function makeModel(?array $firstReturn = null): PersonaModel
    {
        $model = $this->getMockBuilder(PersonaModel::class)
            ->onlyMethods(['first', 'insert', 'getInsertID'])
            ->addMethods(['where'])    // where() llega via __call() en CI4 — no es método real
            ->getMock();

        $model->method('where')->willReturnSelf();
        $model->method('first')->willReturn($firstReturn);
        $model->method('insert')->willReturn(true);
        $model->method('getInsertID')->willReturn(1);

        return $model;
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
        $model = $this->makeModel();
        $model->expects($this->once())->method('insert');

        $resultado = $model->altaPersona($this->personaValida());

        $this->assertTrue($resultado['ok']);
        $this->assertArrayHasKey('id', $resultado);
        $this->assertEquals(1, $resultado['id']);
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
        $model = $this->makeModel();
        $model->expects($this->never())->method('insert');

        $resultado = $model->altaPersona($this->persona(['dni' => '']));

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('dni', $resultado['errores']);
    }

    /**
     * @test
     * @testdox DNI con letras - Retorna error
     */
    public function altaPersona_DniConLetras_RetornaError()
    {
        $model = $this->makeModel();
        $model->expects($this->never())->method('insert');

        $resultado = $model->altaPersona($this->persona(['dni' => 'abc123']));

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('dni', $resultado['errores']);
    }

    /**
     * @test
     * @testdox DNI menor a 7 dígitos - Retorna error
     */
    public function altaPersona_DniMenorA7Digitos_RetornaError()
    {
        $model = $this->makeModel();
        $model->expects($this->never())->method('insert');

        $resultado = $model->altaPersona($this->persona(['dni' => '123']));

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('dni', $resultado['errores']);
    }

    /**
     * @test
     * @testdox DNI ya registrado - Retorna error
     */
    public function altaPersona_DniDuplicado_RetornaError()
    {
        // first() devuelve una fila: simula DNI ya existente en BD.
        $model = $this->makeModel(['id_persona' => 5, 'dni' => '99999999']);
        $model->expects($this->never())->method('insert');

        $resultado = $model->altaPersona($this->persona(['dni' => '99999999']));

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
        $model = $this->makeModel();
        $model->expects($this->never())->method('insert');

        $resultado = $model->altaPersona($this->persona(['nombre' => '']));

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('nombre', $resultado['errores']);
    }

    /**
     * @test
     * @testdox Nombre menor a 3 caracteres - Retorna error
     */
    public function altaPersona_NombreMenorA3Caracteres_RetornaError()
    {
        $model = $this->makeModel();
        $model->expects($this->never())->method('insert');

        $resultado = $model->altaPersona($this->persona(['nombre' => 'Ab']));

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('nombre', $resultado['errores']);
    }

    /**
     * @test
     * @testdox Nombre con números - Retorna error
     */
    public function altaPersona_NombreConNumeros_RetornaError()
    {
        $model = $this->makeModel();
        $model->expects($this->never())->method('insert');

        $resultado = $model->altaPersona($this->persona(['nombre' => 'Juan123']));

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
        $model = $this->makeModel();
        $model->expects($this->never())->method('insert');

        $resultado = $model->altaPersona($this->persona(['apellido' => '']));

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('apellido', $resultado['errores']);
    }

    /**
     * @test
     * @testdox Apellido menor a 3 caracteres - Retorna error
     */
    public function altaPersona_ApellidoMenorA3Caracteres_RetornaError()
    {
        $model = $this->makeModel();
        $model->expects($this->never())->method('insert');

        $resultado = $model->altaPersona($this->persona(['apellido' => 'Pe']));

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
        $model = $this->makeModel();
        $model->expects($this->never())->method('insert');

        $resultado = $model->altaPersona($this->persona(['fecha_nacimiento' => '']));

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('fecha_nacimiento', $resultado['errores']);
    }

    /**
     * @test
     * @testdox Fecha de nacimiento futura - Retorna error
     */
    public function altaPersona_FechaNacimientoFutura_RetornaError()
    {
        $model = $this->makeModel();
        $model->expects($this->never())->method('insert');

        $resultado = $model->altaPersona($this->persona(['fecha_nacimiento' => '2030-01-01']));

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
        $model = $this->makeModel();
        $model->expects($this->never())->method('insert');

        $resultado = $model->altaPersona($this->persona(['telefono' => '379412345a']));

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('telefono', $resultado['errores']);
    }

    /**
     * @test
     * @testdox Teléfono menor a 6 dígitos - Retorna error
     */
    public function altaPersona_TelefonoMenorA6Digitos_RetornaError()
    {
        $model = $this->makeModel();
        $model->expects($this->never())->method('insert');

        $resultado = $model->altaPersona($this->persona(['telefono' => '12']));

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
        $model = $this->makeModel();
        $model->expects($this->never())->method('insert');

        $resultado = $model->altaPersona($this->persona(['calle' => '']));

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('calle', $resultado['errores']);
    }

    /**
     * @test
     * @testdox Altura vacía - Retorna error
     */
    public function altaPersona_AlturaVacia_RetornaError()
    {
        $model = $this->makeModel();
        $model->expects($this->never())->method('insert');

        $resultado = $model->altaPersona($this->persona(['altura' => '']));

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
