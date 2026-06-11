<?php

namespace Tests\Support\Models;

use App\Models\ClienteModel;
use App\Models\PersonaModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Pruebas Unitarias - Alta de Cliente
 * Método: ClienteModel::altaCliente(string $email, int $idPersona, ?PersonaModel $personaModel)
 *
 * Pruebas FANTASMAS: no se conectan a la base de datos.
 * Se mockean where(), first(), insert(), getInsertID() en ClienteModel
 * y find() en PersonaModel (inyectado como dependencia).
 */
class AltaClienteTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        \Config\Services::validation()->reset();
    }

    /**
     * Crea un mock parcial de ClienteModel con comportamiento configurable.
     *
     * @param array|null $firstReturn   Fila que devuelve first() (null = no hay email duplicado).
     */
    private function makeModel(?array $firstReturn = null): ClienteModel
    {
        $model = $this->getMockBuilder(ClienteModel::class)
            ->onlyMethods(['first', 'insert', 'getInsertID'])
            ->addMethods(['where'])    // where() llega via __call() en CI4 — no es método real
            ->getMock();

        $model->method('where')->willReturnSelf();
        $model->method('first')->willReturn($firstReturn);
        $model->method('insert')->willReturn(true);
        $model->method('getInsertID')->willReturn(1);

        return $model;
    }

    /**
     * Crea un mock de PersonaModel con comportamiento configurable.
     *
     * @param array|null $findReturn  Fila que devuelve find() (null = persona no existe).
     */
    private function makePersonaModel(?array $findReturn = ['id_persona' => 1, 'dni' => '30123456']): PersonaModel
    {
        $mock = $this->getMockBuilder(PersonaModel::class)
            ->onlyMethods(['find'])
            ->getMock();

        $mock->method('find')->willReturn($findReturn);

        return $mock;
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
        $model = $this->makeModel();
        $model->expects($this->once())->method('insert');

        $resultado = $model->altaCliente('juan@gmail.com', 1, $this->makePersonaModel());

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
        $model = $this->makeModel();
        $model->expects($this->never())->method('insert');

        $resultado = $model->altaCliente('', 1, $this->makePersonaModel());

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('email', $resultado['errores']);
    }

    /**
     * @test
     * @testdox Email con formato inválido - Retorna error
     */
    public function altaCliente_EmailFormatoInvalido_RetornaError()
    {
        $model = $this->makeModel();
        $model->expects($this->never())->method('insert');

        $resultado = $model->altaCliente('emailinvalido', 1, $this->makePersonaModel());

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('email', $resultado['errores']);
    }

    /**
     * @test
     * @testdox Email ya registrado - Retorna error
     */
    public function altaCliente_EmailDuplicado_RetornaError()
    {
        // first() devuelve una fila: simula email ya existente en BD.
        $model = $this->makeModel(['id_cliente' => 3, 'email' => 'duplicado@gmail.com']);
        $model->expects($this->never())->method('insert');

        $resultado = $model->altaCliente('duplicado@gmail.com', 1, $this->makePersonaModel());

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('email', $resultado['errores']);
    }

    // ================================================================
    // VALIDACIÓN DE LA PERSONA
    // ================================================================

    /**
     * @test
     * @testdox Persona no registrada - Retorna error
     */
    public function altaCliente_PersonaInexistente_RetornaError()
    {
        $model = $this->makeModel();
        $model->expects($this->never())->method('insert');

        // PersonaModel::find() devuelve null: la persona no existe.
        $resultado = $model->altaCliente('juan@gmail.com', 99, $this->makePersonaModel(null));

        $this->assertFalse($resultado['ok']);
        $this->assertArrayHasKey('id_persona', $resultado['errores']);
    }
}
